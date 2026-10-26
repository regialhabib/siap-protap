<?php

namespace App\Http\Controllers;

use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Models\User;
use App\Services\PengamatanHistoryService;
use App\Services\PeriodePengamatan;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, PengamatanHistoryService $history): View|JsonResponse
    {
        $user = Auth::user();
        $isPopt = $user->isPopt();

        // POPT selalu melihat bulan berjalan; filter bulan/tahun hanya untuk admin.
        $filters = $isPopt ? [] : $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);
        $currentMonth = (int) ($filters['bulan'] ?? now()->month);
        $currentYear = (int) ($filters['tahun'] ?? now()->year);

        $monthStart = Carbon::create($currentYear, $currentMonth, 1);
        $statsQuery = Pengamatan::observedBetween($monthStart, $monthStart->copy()->endOfMonth())
            ->when($isPopt, fn ($query) => $query->where('uppt_id', $user->uppt_id));

        $activeChains = $this->activeChains($user, $history);

        $stats = [
            'total_pengamatan' => $statsQuery->count(),
            'total_luas_serangan' => $statsQuery->sum('serangan_jumlah'),
            'kondisi_serangan' => round($activeChains->sum('kondisi_serangan_ha'), 2),
            'komoditas_terdampak' => $statsQuery->distinct('komoditas_id')->count('komoditas_id'),
        ];

        $pengamatans = $isPopt ? $this->recentObservations($user, $history) : null;
        $chartData = $isPopt ? null : $this->complianceData($currentMonth, $currentYear);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'stats' => $stats,
                'chartData' => $chartData,
                'table_html' => $pengamatans ? view('partials.dashboard-table', compact('pengamatans'))->render() : null,
            ]);
        }

        return view('dashboard', compact('stats', 'chartData', 'pengamatans', 'activeChains'));
    }

    /**
     * Riwayat pengamatan terbaru milik UPPT petugas beserta angka berjalan rantainya.
     *
     * @return LengthAwarePaginator<int, Pengamatan>
     */
    private function recentObservations(User $user, PengamatanHistoryService $history): LengthAwarePaginator
    {
        $historyRows = Pengamatan::where('uppt_id', $user->uppt_id)
            ->orderBy('tanggal_pengamatan')
            ->orderBy('id')
            ->get();
        $history->attachRunningTotals($historyRows);
        $historyById = $historyRows->keyBy('id');

        $pengamatans = Pengamatan::with(['komoditas', 'opt', 'jenisOpt', 'uppt', 'kecamatan'])
            ->where('uppt_id', $user->uppt_id)
            ->latest('tanggal_pengamatan')
            ->paginate(5)
            ->withQueryString();

        $pengamatans->getCollection()->each(function (Pengamatan $pengamatan) use ($historyById): void {
            $historyRecord = $historyById->get($pengamatan->id);
            foreach (['serangan_ringan_berjalan', 'serangan_berat_berjalan', 'serangan_jumlah_berjalan', 'kendali_jumlah', 'kondisi_serangan_ha'] as $attribute) {
                $pengamatan->setAttribute($attribute, $historyRecord?->getAttribute($attribute) ?? 0);
            }
        });

        return $pengamatans;
    }

    /**
     * Kepatuhan UPPT: sudah/belum ada input pengamatan pada bulan terpilih.
     *
     * @return array<string, mixed>
     */
    private function complianceData(int $month, int $year): array
    {
        $allUppt = Uppt::orderBy('nama_uppt', 'asc')->get();
        $allUpptCount = $allUppt->count();

        $monthStart = Carbon::create($year, $month, 1);
        $upptIdsWithPengamatan = Pengamatan::observedBetween($monthStart, $monthStart->copy()->endOfMonth())
            ->distinct('uppt_id')
            ->pluck('uppt_id')
            ->toArray();

        $upptSudah = $allUppt->whereIn('id', $upptIdsWithPengamatan)->values();
        $upptBelum = $allUppt->whereNotIn('id', $upptIdsWithPengamatan)->values();

        $totalSudah = $upptSudah->count();
        $totalBelum = $upptBelum->count();

        return [
            'sudah' => $upptSudah,
            'belum' => $upptBelum,
            'total_sudah' => $totalSudah,
            'total_belum' => $totalBelum,
            'persentase_sudah' => $allUpptCount > 0 ? round(($totalSudah / $allUpptCount) * 100, 1) : 0,
            'persentase_belum' => $allUpptCount > 0 ? round(($totalBelum / $allUpptCount) * 100, 1) : 0,
            'total' => $allUpptCount,
            'bulan_ini' => Carbon::createFromDate($year, $month, 1)->isoFormat('MMMM Y'),
        ];
    }

    /**
     * Rantai aktif periode berjalan milik UPPT petugas beserta status input bulan ini.
     *
     * @return Collection<int, object>
     */
    private function activeChains(User $user, PengamatanHistoryService $history): Collection
    {
        if (! $user->isPopt() || ! $user->uppt_id) {
            return collect();
        }

        $periode = PeriodePengamatan::current();
        $monthKey = PeriodePengamatan::monthKey(now());
        $observations = Pengamatan::with(['kecamatan', 'komoditas', 'opt', 'jenisOpt'])
            ->where('uppt_id', $user->uppt_id)
            ->observedBetween(PeriodePengamatan::start($periode), PeriodePengamatan::end($periode))
            ->get();

        return $history->monthlyChains($observations, $periode)
            ->filter(fn (object $chain): bool => $chain->months[$monthKey] !== null && ! $history->hasNothingToReport($chain->months[$monthKey]))
            ->map(fn (object $chain): object => (object) [
                'kecamatan' => $chain->kecamatan?->nama_kecamatan ?? '-',
                'komoditas' => $chain->komoditas?->nama_komoditas ?? '-',
                'opt' => ($chain->opt?->nama_opt ?? '-').($chain->jenisOpt ? ' — '.$chain->jenisOpt->nama_jenis_opt : ''),
                'kondisi_serangan_ha' => $chain->months[$monthKey]['kondisi_akhir'],
                'sudah_lapor' => $chain->months[$monthKey]['ada_input'],
                'input_url' => route('pengamatan.create', array_filter([
                    'kecamatan_id' => $chain->kecamatan_id,
                    'komoditas_id' => $chain->komoditas_id,
                    'opt_id' => $chain->opt_id,
                    'jenis_opt_id' => $chain->jenis_opt_id,
                ])),
            ])
            ->sortBy(fn (object $chain): string => mb_strtolower($chain->kecamatan.'|'.$chain->komoditas.'|'.$chain->opt))
            ->values();
    }
}
