<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\Kecamatan;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Services\PengamatanHistoryService;
use Barryvdh\DomPDF\Facade\Pdf;
// Pustaka Export
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    private function getFilters(Request $request): array
    {
        $validated = $request->validate([
            'jenis' => ['nullable', Rule::in(['bulanan', 'triwulan'])],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'triwulan' => ['nullable', 'integer', 'between:1,4'],
            'uppt_id' => ['nullable', 'exists:uppts,id'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $uppts = Uppt::orderBy('nama_uppt', 'asc')->get();
        $uppt_id = $validated['uppt_id'] ?? $request->input('uppt_id', '');

        $kecamatans = [];
        if ($uppt_id) {
            $kecamatans = Kecamatan::where('uppt_id', $uppt_id)->orderBy('nama_kecamatan', 'asc')->get();
        }

        return [
            'jenis' => $validated['jenis'] ?? 'bulanan',
            'tahun' => $validated['tahun'] ?? date('Y'),
            'bulan' => $validated['bulan'] ?? date('m'),
            'triwulan' => $validated['triwulan'] ?? '1',
            'uppts' => $uppts,
            'uppt_id' => $uppt_id,
            'kecamatans' => $kecamatans,
            'kecamatan_id' => $validated['kecamatan_id'] ?? '',
        ];
    }

    private function getData(array $filters, Request $request, PengamatanHistoryService $history)
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Akses ditolak.');

        $query = Pengamatan::with(['komoditas', 'opt', 'jenisOpt', 'uppt.kabupaten', 'kecamatan', 'user']);

        $endMonth = (int) $filters['bulan'];
        if ($filters['jenis'] == 'bulanan') {
            $selectedMonth = (int) $filters['bulan'];
        } else {
            $endMonth = (int) $filters['triwulan'] * 3;
            $selectedMonth = $endMonth;
        }

        $periodEnd = Carbon::create((int) $filters['tahun'], $endMonth, 1)->endOfMonth();
        $periodStart = Carbon::create((int) $filters['tahun'], $selectedMonth, 1)->startOfMonth();
        $query->whereYear('tanggal_pengamatan', $filters['tahun'])
            ->whereDate('tanggal_pengamatan', '<=', $periodEnd->toDateString());

        // Filter UPPT
        if (! empty($filters['uppt_id'])) {
            $query->where('uppt_id', $filters['uppt_id']);
        }

        // Filter Kecamatan
        if (! empty($filters['kecamatan_id'])) {
            $query->where('kecamatan_id', $filters['kecamatan_id']);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('komoditas', function ($q2) use ($search) {
                    $q2->where('nama_komoditas', 'like', "%{$search}%");
                })->orWhereHas('opt', function ($q2) use ($search) {
                    $q2->where('nama_opt', 'like', "%{$search}%");
                })->orWhereHas('jenisOpt', function ($q2) use ($search) {
                    $q2->where('nama_jenis_opt', 'like', "%{$search}%");
                });
            });
        }

        $historyRows = $query->orderBy('tanggal_pengamatan')->orderBy('id')->get();
        $history->attachRunningTotals($historyRows);

        return $historyRows
            ->filter(fn (Pengamatan $pengamatan): bool => $pengamatan->tanggal_pengamatan >= $periodStart && $pengamatan->tanggal_pengamatan <= $periodEnd)
            ->groupBy(fn (Pengamatan $pengamatan): string => implode(':', [
                $pengamatan->uppt_id,
                $pengamatan->kecamatan_id ?? 'null',
                $pengamatan->komoditas_id,
                $pengamatan->opt_id,
                $pengamatan->jenis_opt_id ?? 'null',
            ]))
            ->map(fn ($observations): Pengamatan => $observations->last())
            ->sortBy(fn (Pengamatan $pengamatan): string => $pengamatan->tanggal_pengamatan->toDateString().'-'.str_pad((string) $pengamatan->id, 12, '0', STR_PAD_LEFT))
            ->values();
    }

    public function index(Request $request)
    {
        $filters = $this->getFilters($request);
        $data = $this->getData($filters, $request, app(PengamatanHistoryService::class));

        return view('laporan.index', array_merge(['data' => $data], $filters));
    }

    public function historyTahunan(Request $request, PengamatanHistoryService $history)
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Akses ditolak.');

        $uppts = Uppt::orderBy('nama_uppt')->get();
        $upptId = $request->input('uppt_id', '');
        $kecamatanRule = Rule::exists('kecamatans', 'id');

        if ($upptId) {
            $kecamatanRule->where('uppt_id', $upptId);
        }

        $filters = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'uppt_id' => ['nullable', 'exists:uppts,id'],
            'kecamatan_id' => ['nullable', $kecamatanRule],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $upptId = $filters['uppt_id'] ?? $upptId;
        $kecamatanId = $filters['kecamatan_id'] ?? '';
        $kecamatans = $upptId
            ? Kecamatan::where('uppt_id', $upptId)->orderBy('nama_kecamatan')->get()
            : collect();

        $query = Pengamatan::with(['komoditas', 'opt', 'jenisOpt', 'uppt', 'kecamatan', 'user'])
            ->whereYear('tanggal_pengamatan', $tahun);

        if ($upptId) {
            $query->where('uppt_id', $upptId);
        }

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        $data = $query->orderBy('tanggal_pengamatan')->orderBy('id')->get();
        $history->attachRunningTotals($data);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $search = mb_strtolower($search);
            $data = $data->filter(function (Pengamatan $pengamatan) use ($search): bool {
                $searchable = implode(' ', [
                    $pengamatan->uppt?->nama_uppt,
                    $pengamatan->kecamatan?->nama_kecamatan,
                    $pengamatan->komoditas?->nama_komoditas,
                    $pengamatan->opt?->nama_opt,
                    $pengamatan->jenisOpt?->nama_jenis_opt,
                    $pengamatan->user?->name,
                ]);

                return str_contains(mb_strtolower($searchable), $search);
            })->values();
        }

        return view('laporan.tahunan', [
            'data' => $data,
            'tahun' => $tahun,
            'uppts' => $uppts,
            'upptId' => (string) ($upptId ?? ''),
            'kecamatans' => $kecamatans,
            'kecamatanId' => (string) $kecamatanId,
            'search' => $filters['search'] ?? '',
        ]);
    }

    private function getFilename(array $filters, $ext)
    {
        $periode = $filters['tahun'];
        if ($filters['jenis'] == 'bulanan') {
            $namaBulan = Carbon::create()->month((int) $filters['bulan'])->translatedFormat('F');
            $periode = "{$namaBulan}_{$filters['tahun']}";
        } elseif ($filters['jenis'] == 'triwulan') {
            $periode = "Triwulan_{$filters['triwulan']}_{$filters['tahun']}";
        }

        return "Laporan_{$periode}.{$ext}";
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->getFilters($request);
        $data = $this->getData($filters, $request, app(PengamatanHistoryService::class));

        $filename = $this->getFilename($filters, 'xlsx');

        return Excel::download(new LaporanExport(
            $data,
            $filters['jenis'],
            $filters['tahun'],
            $filters['bulan'],
            $filters['triwulan'],
            $filters['uppt_id']
        ), $filename);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->getFilters($request);
        $data = $this->getData($filters, $request, app(PengamatanHistoryService::class));

        $pdf = Pdf::loadView('laporan.pdf', array_merge(['data' => $data], $filters))
            ->setPaper('a4', 'landscape');

        $filename = $this->getFilename($filters, 'pdf');

        return $pdf->stream($filename);
    }
}
