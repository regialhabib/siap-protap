<?php

namespace App\Http\Controllers;

use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Services\PengamatanHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request, PengamatanHistoryService $history)
    {
        $user = Auth::user();

        $currentMonth = $request->input('bulan', now()->month);
        $currentYear = $request->input('tahun', now()->year);

        $statsQuery = Pengamatan::whereMonth('tanggal_pengamatan', $currentMonth)
            ->whereYear('tanggal_pengamatan', $currentYear);

        if ($user->role === 'popt') {
            $statsQuery->where('uppt_id', $user->uppt_id);
        }

        $stats = [
            'total_pengamatan' => $statsQuery->count(),
            'total_luas_serangan' => $statsQuery->sum('serangan_jumlah'),
            'komoditas_terdampak' => $statsQuery->distinct('komoditas_id')->count('komoditas_id'),
        ];

        // Table Query
        $tableQuery = Pengamatan::with(['komoditas', 'opt', 'jenisOpt', 'uppt', 'kecamatan'])
            ->latest('tanggal_pengamatan');

        if ($user->role === 'popt') {
            $tableQuery->where('uppt_id', $user->uppt_id);
        } else {
            $tableQuery->whereMonth('tanggal_pengamatan', $currentMonth)
                ->whereYear('tanggal_pengamatan', $currentYear);
        }

        $historyQuery = Pengamatan::query()->with(['komoditas', 'opt', 'jenisOpt', 'uppt', 'kecamatan']);
        if ($user->role === 'popt') {
            $historyQuery->where('uppt_id', $user->uppt_id);
        } else {
            $historyQuery->whereYear('tanggal_pengamatan', $currentYear)
                ->whereMonth('tanggal_pengamatan', '<=', $currentMonth);
        }

        $historyRows = $historyQuery->orderBy('tanggal_pengamatan')->orderBy('id')->get();
        $history->attachRunningTotals($historyRows);
        $historyById = $historyRows->keyBy('id');

        $pengamatans = $tableQuery->paginate(5)->withQueryString();
        $pengamatans->getCollection()->each(function (Pengamatan $pengamatan) use ($historyById): void {
            $historyRecord = $historyById->get($pengamatan->id);
            foreach (['serangan_ringan_berjalan', 'serangan_berat_berjalan', 'serangan_jumlah_berjalan', 'kendali_jumlah', 'kondisi_serangan_ha'] as $attribute) {
                $pengamatan->setAttribute($attribute, $historyRecord?->getAttribute($attribute) ?? 0);
            }
        });

        // Hitung Kepatuhan UPPT (Bulan Ini)
        $allUppt = Uppt::orderBy('nama_uppt', 'asc')->get();
        $allUpptCount = $allUppt->count();

        $upptIdsWithPengamatan = Pengamatan::whereMonth('tanggal_pengamatan', $currentMonth)
            ->whereYear('tanggal_pengamatan', $currentYear)
            ->distinct('uppt_id')
            ->pluck('uppt_id')
            ->toArray();

        $upptSudah = $allUppt->whereIn('id', $upptIdsWithPengamatan)->values();
        $upptBelum = $allUppt->whereNotIn('id', $upptIdsWithPengamatan)->values();

        $totalSudah = $upptSudah->count();
        $totalBelum = $upptBelum->count();

        $persentaseSudah = $allUpptCount > 0 ? round(($totalSudah / $allUpptCount) * 100, 1) : 0;
        $persentaseBelum = $allUpptCount > 0 ? round(($totalBelum / $allUpptCount) * 100, 1) : 0;

        $chartData = [
            'sudah' => $upptSudah,
            'belum' => $upptBelum,
            'total_sudah' => $totalSudah,
            'total_belum' => $totalBelum,
            'persentase_sudah' => $persentaseSudah,
            'persentase_belum' => $persentaseBelum,
            'total' => $allUpptCount,
            'bulan_ini' => Carbon::createFromDate($currentYear, $currentMonth, 1)->isoFormat('MMMM Y'),
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'stats' => $stats,
                'chartData' => $chartData,
                'table_html' => view('partials.dashboard-table', compact('pengamatans'))->render(),
            ]);
        }

        return view('dashboard', compact('stats', 'chartData', 'pengamatans'));
    }
}
