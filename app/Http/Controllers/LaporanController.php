<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Services\PengamatanHistoryService;
use Barryvdh\DomPDF\Facade\Pdf;
// Pustaka Export
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    private function getFilters(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'jenis' => ['nullable', Rule::in(['bulanan', 'triwulan'])],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'bulan_awal' => ['required_if:jenis,triwulan', 'nullable', 'integer', 'between:1,12'],
            'bulan_tengah' => ['required_if:jenis,triwulan', 'nullable', 'integer', 'between:1,12'],
            'bulan_akhir' => ['required_if:jenis,triwulan', 'nullable', 'integer', 'between:1,12'],
            'uppt_id' => ['nullable', 'exists:uppts,id'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $validator->after(function ($validator) use ($request): void {
            if ($request->input('jenis') !== 'triwulan' || $validator->errors()->any()) {
                return;
            }

            $months = [
                (int) $request->input('bulan_awal'),
                (int) $request->input('bulan_tengah'),
                (int) $request->input('bulan_akhir'),
            ];

            if ($months[1] !== $months[0] + 1 || $months[2] !== $months[1] + 1) {
                $validator->errors()->add('bulan_tengah', 'Pilih tiga bulan berurutan dalam tahun yang sama.');
            }
        });
        $validated = $validator->validate();

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
            'bulan_awal' => $validated['bulan_awal'] ?? '1',
            'bulan_tengah' => $validated['bulan_tengah'] ?? '2',
            'bulan_akhir' => $validated['bulan_akhir'] ?? '3',
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

        $selectedMonth = $filters['jenis'] === 'bulanan'
            ? (int) $filters['bulan']
            : (int) $filters['bulan_akhir'];
        $endMonth = $selectedMonth;

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
        if ($request->input('jenis') === 'triwulan') {
            return redirect()->route('laporan.triwulan', $this->reportQueryParameters($request));
        }

        $request->merge(['jenis' => 'bulanan']);

        return $this->showTimeBasedReport($request);
    }

    public function quarterly(Request $request)
    {
        $request->merge(['jenis' => 'triwulan']);
        if (! $request->has('bulan_awal') && ! $request->has('bulan_tengah') && ! $request->has('bulan_akhir')) {
            $request->merge(['bulan_awal' => 1, 'bulan_tengah' => 2, 'bulan_akhir' => 3]);
        }

        return $this->showTimeBasedReport($request);
    }

    private function showTimeBasedReport(Request $request)
    {
        $filters = $this->getFilters($request);
        $reportAvailable = $filters['jenis'] !== 'triwulan' || $this->hasCompleteQuarterlyData($filters);
        $data = $reportAvailable
            ? $this->getData($filters, $request, app(PengamatanHistoryService::class))
            : collect();
        $data = $this->paginateData($data, $request);

        return view('laporan.index', array_merge(['data' => $data, 'reportAvailable' => $reportAvailable], $filters));
    }

    private function reportQueryParameters(Request $request): array
    {
        $parameters = $request->query();
        unset($parameters['jenis']);

        return $parameters;
    }

    private function hasCompleteQuarterlyData(array $filters): bool
    {
        $query = Pengamatan::query()
            ->whereYear('tanggal_pengamatan', $filters['tahun'])
            ->whereBetween('tanggal_pengamatan', [
                Carbon::create((int) $filters['tahun'], (int) $filters['bulan_awal'], 1)->toDateString(),
                Carbon::create((int) $filters['tahun'], (int) $filters['bulan_akhir'], 1)->endOfMonth()->toDateString(),
            ]);

        if (! empty($filters['uppt_id'])) {
            $query->where('uppt_id', $filters['uppt_id']);
        }

        if (! empty($filters['kecamatan_id'])) {
            $query->where('kecamatan_id', $filters['kecamatan_id']);
        }

        foreach (range((int) $filters['bulan_awal'], (int) $filters['bulan_akhir']) as $month) {
            if (! (clone $query)->whereMonth('tanggal_pengamatan', $month)->exists()) {
                return false;
            }
        }

        return true;
    }

    private function paginateData(Collection $data, Request $request): LengthAwarePaginator
    {
        $perPage = 5;
        $currentPage = max(1, $request->integer('page', 1));

        return new LengthAwarePaginator(
            $data->forPage($currentPage, $perPage)->values(),
            $data->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function getAnnualData(int $tahun, array $filters): Collection
    {
        $query = Pengamatan::with(['komoditas', 'opt', 'jenisOpt', 'uppt.kabupaten', 'kecamatan'])
            ->whereYear('tanggal_pengamatan', $tahun);

        if ($filters['kabupaten_id'] !== '') {
            $query->whereHas('uppt', fn ($upptQuery) => $upptQuery->where('kabupaten_id', $filters['kabupaten_id']));
        }

        $observations = $query->orderBy('tanggal_pengamatan')->orderBy('id')->get();
        $monthlyLatest = $observations->groupBy(fn (Pengamatan $row): string => implode(':', [
            $row->tanggal_pengamatan->format('Y-m'), $row->uppt_id, $row->kecamatan_id ?? 'null',
            $row->komoditas_id, $row->opt_id, $row->jenis_opt_id ?? 'null',
        ]))->map(fn ($rows): Pengamatan => $rows->last())->values();

        if ($filters['search'] !== '') {
            $needle = mb_strtolower($filters['search']);
            $monthlyLatest = $monthlyLatest->filter(function (Pengamatan $row) use ($needle): bool {
                $value = implode(' ', [
                    $row->uppt?->kabupaten?->nama_kabupaten, $row->komoditas?->nama_komoditas,
                    $row->opt?->nama_opt, $row->jenisOpt?->nama_jenis_opt, $row->cara_pengendalian,
                ]);

                return str_contains(mb_strtolower($value), $needle);
            })->values();
        }

        $groups = [];
        foreach ($monthlyLatest as $row) {
            $kabupatenId = $row->uppt?->kabupaten_id ?? 'null';
            $groupKey = implode(':', [$kabupatenId, $row->komoditas_id, $row->opt_id, $row->jenis_opt_id ?? 'null']);
            $kecamatanKey = (string) ($row->kecamatan_id ?? $row->uppt_id);
            $group = $groups[$groupKey] ??= (object) [
                'kabupaten' => $row->uppt?->kabupaten?->nama_kabupaten ?? '-',
                'komoditas' => $row->komoditas?->nama_komoditas ?? '-',
                'opt' => $row->opt?->nama_opt ?? '-',
                'jenis_opt' => $row->jenisOpt?->nama_jenis_opt ?? '',
                'serangan_ringan' => 0.0, 'serangan_berat' => 0.0, 'serangan_jumlah' => 0.0,
                'kendali_apbd_prov' => 0.0, 'kendali_apbd_kab' => 0.0,
                'kendali_masyarakat' => 0.0, 'kendali_apbn' => 0.0,
                'luas_per_kecamatan' => [], 'cara_per_kecamatan' => [],
            ];

            foreach (['serangan_ringan', 'serangan_berat', 'serangan_jumlah', 'kendali_apbd_prov', 'kendali_apbd_kab', 'kendali_masyarakat', 'kendali_apbn'] as $field) {
                $group->{$field} += (float) $row->{$field};
            }

            $group->luas_per_kecamatan[$kecamatanKey] = $row;
            if (filled($row->cara_pengendalian)) {
                $group->cara_per_kecamatan[$kecamatanKey] = $row->cara_pengendalian;
            }
        }

        return collect($groups)->map(function (object $group): object {
            $group->luas_komoditi_ha = collect($group->luas_per_kecamatan)->sum(fn (Pengamatan $row): float => (float) $row->luas_komoditi_ha);
            $group->cara_pengendalian = collect($group->cara_per_kecamatan)->unique()->implode('; ');
            $group->kendali_jumlah = $group->kendali_apbd_prov + $group->kendali_apbd_kab + $group->kendali_masyarakat + $group->kendali_apbn;
            $group->kerugian_hasil = '';
            unset($group->luas_per_kecamatan, $group->cara_per_kecamatan);

            return $group;
        })->sortBy(fn (object $group): string => $group->kabupaten.'-'.$group->komoditas.'-'.$group->opt.'-'.$group->jenis_opt)->values();
    }

    public function historyTahunan(Request $request)
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Akses ditolak.');

        $filters = $this->annualFilters($request);
        $data = $this->getAnnualData($filters['tahun'], $filters);
        $data = $this->paginateData($data, $request);

        return view('laporan.tahunan', [
            'data' => $data,
            'tahun' => $filters['tahun'],
            'upptId' => '',
            'kabupatens' => Kabupaten::orderBy('nama_kabupaten')->get(),
            'filters' => $filters,
        ]);
    }

    private function annualFilters(Request $request): array
    {
        $filters = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'kabupaten_id' => ['nullable', 'exists:kabupatens,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return [
            'tahun' => (int) ($filters['tahun'] ?? date('Y')),
            'kabupaten_id' => (string) ($filters['kabupaten_id'] ?? ''),
            'search' => trim((string) ($filters['search'] ?? '')),
        ];
    }

    private function annualExportData(Request $request): array
    {
        $filters = $this->annualFilters($request);
        $filters['jenis'] = 'tahunan';
        $filters['data'] = $this->getAnnualData($filters['tahun'], $filters);

        return $filters;
    }

    public function exportAnnualExcel(Request $request)
    {
        $filters = $this->annualExportData($request);

        return Excel::download(new LaporanExport($filters['data'], 'tahunan', $filters['tahun'], null, null, null, null, null), "Laporan_Tahunan_{$filters['tahun']}.xlsx");
    }

    public function exportAnnualPdf(Request $request)
    {
        $filters = $this->annualExportData($request);
        $pdf = Pdf::loadView('laporan.pdf-tahunan', $filters)->setPaper('a3', 'landscape');

        return $pdf->stream("Laporan_Tahunan_{$filters['tahun']}.pdf");
    }

    private function getFilename(array $filters, $ext)
    {
        $periode = $filters['tahun'];
        if ($filters['jenis'] == 'bulanan') {
            $namaBulan = Carbon::create()->month((int) $filters['bulan'])->translatedFormat('F');
            $periode = "{$namaBulan}_{$filters['tahun']}";
        } elseif ($filters['jenis'] == 'triwulan') {
            $bulanAwal = str_pad((string) $filters['bulan_awal'], 2, '0', STR_PAD_LEFT);
            $bulanAkhir = str_pad((string) $filters['bulan_akhir'], 2, '0', STR_PAD_LEFT);
            $periode = "Triwulan_{$bulanAwal}-{$bulanAkhir}_{$filters['tahun']}";
        }

        return "Laporan_{$periode}.{$ext}";
    }

    private function getExportFilters(Request $request, string $jenis): array
    {
        $request->validate([
            'uppt_id' => ['required', 'exists:uppts,id'],
        ]);

        $request->merge(['jenis' => $jenis]);

        return $this->getFilters($request);
    }

    public function exportExcel(Request $request)
    {
        if ($request->input('jenis') === 'triwulan') {
            return redirect()->route('laporan.export.triwulan.excel', $this->reportQueryParameters($request));
        }

        return $this->exportExcelForType($request, 'bulanan');
    }

    public function exportQuarterlyExcel(Request $request)
    {
        return $this->exportExcelForType($request, 'triwulan');
    }

    private function exportExcelForType(Request $request, string $jenis)
    {
        $filters = $this->getExportFilters($request, $jenis);
        if ($filters['jenis'] === 'triwulan' && ! $this->hasCompleteQuarterlyData($filters)) {
            return redirect()->route($jenis === 'triwulan' ? 'laporan.triwulan' : 'laporan.index', $this->reportQueryParameters($request))
                ->withErrors(['bulan_awal' => 'Laporan belum tersedia karena data tiga bulan belum lengkap.']);
        }
        $data = $this->getData($filters, $request, app(PengamatanHistoryService::class));

        $filename = $this->getFilename($filters, 'xlsx');

        return Excel::download(new LaporanExport(
            $data,
            $filters['jenis'],
            $filters['tahun'],
            $filters['bulan'],
            $filters['bulan_awal'],
            $filters['bulan_tengah'],
            $filters['bulan_akhir'],
            $filters['uppt_id']
        ), $filename);
    }

    public function exportPdf(Request $request)
    {
        if ($request->input('jenis') === 'triwulan') {
            return redirect()->route('laporan.export.triwulan.pdf', $this->reportQueryParameters($request));
        }

        return $this->exportPdfForType($request, 'bulanan');
    }

    public function exportQuarterlyPdf(Request $request)
    {
        return $this->exportPdfForType($request, 'triwulan');
    }

    private function exportPdfForType(Request $request, string $jenis)
    {
        $filters = $this->getExportFilters($request, $jenis);
        if ($filters['jenis'] === 'triwulan' && ! $this->hasCompleteQuarterlyData($filters)) {
            return redirect()->route($jenis === 'triwulan' ? 'laporan.triwulan' : 'laporan.index', $this->reportQueryParameters($request))
                ->withErrors(['bulan_awal' => 'Laporan belum tersedia karena data tiga bulan belum lengkap.']);
        }
        $data = $this->getData($filters, $request, app(PengamatanHistoryService::class));

        $pdf = Pdf::loadView('laporan.pdf', array_merge(['data' => $data], $filters))
            ->setPaper('a4', 'landscape');

        $filename = $this->getFilename($filters, 'pdf');

        return $pdf->stream($filename);
    }
}
