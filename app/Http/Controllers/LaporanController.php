<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Services\PengamatanHistoryService;
use App\Services\PeriodePengamatan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    private const RELATIONS = ['komoditas', 'opt', 'jenisOpt', 'uppt.kabupaten', 'kecamatan', 'user'];

    public function __construct(private PengamatanHistoryService $history) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->input('jenis') === 'triwulan') {
            return redirect()->route('laporan.triwulan', $this->queryWithoutJenis($request));
        }

        $filters = $this->monthlyFilters($request);

        return view('laporan.index', [
            ...$filters,
            'data' => $this->paginateData($this->monthlyRows($filters), $request),
            'uppts' => Uppt::orderBy('nama_uppt')->get(),
            'kecamatans' => $filters['uppt_id'] === ''
                ? collect()
                : Kecamatan::where('uppt_id', $filters['uppt_id'])->orderBy('nama_kecamatan')->get(),
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        if ($request->input('jenis') === 'triwulan') {
            return redirect()->route('laporan.export.triwulan.excel', $this->queryWithoutJenis($request));
        }

        $filters = $this->monthlyExportFilters($request);

        return Excel::download(
            new LaporanExport($this->monthlyRows($filters), 'bulanan', $filters),
            $this->monthlyFilename($filters, 'xlsx')
        );
    }

    public function exportPdf(Request $request): Response|RedirectResponse
    {
        if ($request->input('jenis') === 'triwulan') {
            return redirect()->route('laporan.export.triwulan.pdf', $this->queryWithoutJenis($request));
        }

        $filters = $this->monthlyExportFilters($request);
        $pdf = Pdf::loadView('laporan.pdf', [...$filters, 'jenis' => 'bulanan', 'data' => $this->monthlyRows($filters)])
            ->setPaper('a4', 'landscape');

        return $pdf->stream($this->monthlyFilename($filters, 'pdf'));
    }

    public function quarterly(Request $request): View
    {
        $filters = $this->quarterlyFilters($request);

        return view('laporan.triwulan', [
            'filters' => $filters,
            'data' => $this->paginateData($this->quarterlyRows($filters), $request),
            'kabupatens' => Kabupaten::orderBy('nama_kabupaten')->get(),
        ]);
    }

    public function exportQuarterlyExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->quarterlyFilters($request);

        return Excel::download(
            new LaporanExport($this->quarterlyRows($filters), 'triwulan', ['filters' => $filters]),
            $this->quarterlyFilename($filters, 'xlsx')
        );
    }

    public function exportQuarterlyPdf(Request $request): Response
    {
        $filters = $this->quarterlyFilters($request);
        $pdf = Pdf::loadView('laporan.pdf-triwulan', ['filters' => $filters, 'data' => $this->quarterlyRows($filters)])
            ->setPaper('a4', 'landscape');

        return $pdf->stream($this->quarterlyFilename($filters, 'pdf'));
    }

    public function historyTahunan(Request $request): View
    {
        $filters = $this->annualFilters($request);

        return view('laporan.tahunan', [
            'data' => $this->paginateData($this->annualRows($filters), $request),
            'tahun' => $filters['tahun'],
            'kabupatens' => Kabupaten::orderBy('nama_kabupaten')->get(),
            'filters' => $filters,
        ]);
    }

    public function exportAnnualExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->annualFilters($request);

        return Excel::download(
            new LaporanExport($this->annualRows($filters), 'tahunan', ['tahun' => $filters['tahun']]),
            "Laporan_Tahunan_{$filters['tahun']}.xlsx"
        );
    }

    public function exportAnnualPdf(Request $request): Response
    {
        $filters = $this->annualFilters($request);
        $pdf = Pdf::loadView('laporan.pdf-tahunan', ['tahun' => $filters['tahun'], 'data' => $this->annualRows($filters)])
            ->setPaper('a3', 'landscape');

        return $pdf->stream("Laporan_Tahunan_{$filters['tahun']}.pdf");
    }

    /** @return array{tahun: int, bulan: int, uppt_id: string, kecamatan_id: string, search: string} */
    private function monthlyFilters(Request $request): array
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'uppt_id' => ['nullable', 'exists:uppts,id'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $bulan = (int) ($validated['bulan'] ?? now()->month);

        if (CarbonImmutable::create($tahun, $bulan, 1)->greaterThan(now()->startOfMonth())) {
            throw ValidationException::withMessages(['bulan' => 'Laporan bulanan hanya tersedia sampai bulan berjalan.']);
        }

        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'uppt_id' => (string) ($validated['uppt_id'] ?? ''),
            'kecamatan_id' => (string) ($validated['kecamatan_id'] ?? ''),
            'search' => trim((string) ($validated['search'] ?? '')),
        ];
    }

    /** @return array{tahun: int, bulan: int, uppt_id: string, kecamatan_id: string, search: string} */
    private function monthlyExportFilters(Request $request): array
    {
        $request->validate([
            'uppt_id' => ['required', 'exists:uppts,id'],
        ]);

        return $this->monthlyFilters($request);
    }

    /**
     * Baris laporan bulanan per rantai: serangan & pengendalian bulan ini saja, kondisi posisi akhir bulan.
     *
     * @param  array{tahun: int, bulan: int, uppt_id: string, kecamatan_id: string, search: string}  $filters
     * @return Collection<int, object>
     */
    private function monthlyRows(array $filters): Collection
    {
        $month = CarbonImmutable::create($filters['tahun'], $filters['bulan'], 1);
        $periode = PeriodePengamatan::forDate($month);
        $observations = $this->periodeQuery($periode, $month->endOfMonth())
            ->when($filters['uppt_id'] !== '', fn (Builder $query) => $query->where('uppt_id', $filters['uppt_id']))
            ->when($filters['kecamatan_id'] !== '', fn (Builder $query) => $query->where('kecamatan_id', $filters['kecamatan_id']))
            ->when($filters['search'] !== '', fn (Builder $query) => $this->applySearch($query, $filters['search']))
            ->get();

        return $this->history->monthlyChains($observations, $periode)
            ->map(function (object $chain) use ($month): ?object {
                $state = $chain->months[PeriodePengamatan::monthKey($month)];
                if ($state === null || $this->history->hasNothingToReport($state)) {
                    return null;
                }

                return (object) [
                    'uppt' => $chain->uppt,
                    'kecamatan' => $chain->kecamatan,
                    'komoditas' => $chain->komoditas,
                    'opt' => $chain->opt,
                    'jenisOpt' => $chain->jenisOpt,
                    'user' => $state['petugas'],
                    'tanggal_pengamatan' => $state['tanggal_terakhir'],
                    'ada_input' => $state['ada_input'],
                    'luas_komoditi_ha' => $state['luas_komoditi_ha'],
                    'serangan_ringan' => $state['tambah_ringan'],
                    'serangan_berat' => $state['tambah_berat'],
                    'serangan_jumlah' => $state['tambah_jumlah'],
                    'kendali_apbd_prov' => $state['kendali_apbd_prov'],
                    'kendali_apbd_kab' => $state['kendali_apbd_kab'],
                    'kendali_masyarakat' => $state['kendali_masyarakat'],
                    'kendali_apbn' => $state['kendali_apbn'],
                    'kendali_jumlah' => $state['kendali_jumlah'],
                    'kondisi_serangan_ha' => $state['kondisi_akhir'],
                    'kondisi_label' => $this->history->kondisiLabel($state['kondisi_awal'], $state['kondisi_akhir']),
                ];
            })
            ->filter()
            ->sortBy(fn (object $row): string => mb_strtolower(implode('|', [
                $row->uppt?->nama_uppt, $row->kecamatan?->nama_kecamatan, $row->komoditas?->nama_komoditas,
                $row->opt?->nama_opt, $row->jenisOpt?->nama_jenis_opt,
            ])))
            ->values();
    }

    /** @return array{tahun: int, triwulan: int, kabupaten_id: string} */
    private function quarterlyFilters(Request $request): array
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'triwulan' => ['nullable', 'integer', 'between:1,4'],
            'kabupaten_id' => ['nullable', 'exists:kabupatens,id'],
        ]);

        return [
            'tahun' => (int) ($validated['tahun'] ?? PeriodePengamatan::current()),
            'triwulan' => (int) ($validated['triwulan'] ?? PeriodePengamatan::quarterForDate(now())),
            'kabupaten_id' => (string) ($validated['kabupaten_id'] ?? ''),
        ];
    }

    /**
     * Baris triwulan per kabupaten: jumlah tambah serangan & pengendalian selama 3 bulan, kondisi akhir triwulan
     * dibandingkan dengan kondisi akhir triwulan sebelumnya.
     *
     * @param  array{tahun: int, triwulan: int, kabupaten_id: string}  $filters
     * @return Collection<int, object>
     */
    private function quarterlyRows(array $filters): Collection
    {
        $months = PeriodePengamatan::quarterMonths($filters['tahun'], $filters['triwulan']);

        return $this->kabupatenRows($filters['tahun'], $months, $filters['kabupaten_id'])
            ->each(function (object $row): void {
                $row->kondisi_label = $this->history->kondisiLabel($row->kondisi_awal, $row->kondisi_serangan_ha);
            });
    }

    /** @return array{tahun: int, kabupaten_id: string, search: string} */
    private function annualFilters(Request $request): array
    {
        $filters = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'kabupaten_id' => ['nullable', 'exists:kabupatens,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return [
            'tahun' => (int) ($filters['tahun'] ?? PeriodePengamatan::current()),
            'kabupaten_id' => (string) ($filters['kabupaten_id'] ?? ''),
            'search' => trim((string) ($filters['search'] ?? '')),
        ];
    }

    /**
     * Baris tahunan per kabupaten: akumulasi serangan & pengendalian satu periode Feb–Jan.
     *
     * @param  array{tahun: int, kabupaten_id: string, search: string}  $filters
     * @return Collection<int, object>
     */
    private function annualRows(array $filters): Collection
    {
        $rows = $this->kabupatenRows($filters['tahun'], PeriodePengamatan::months($filters['tahun']), $filters['kabupaten_id']);

        if ($filters['search'] === '') {
            return $rows;
        }

        $needle = mb_strtolower($filters['search']);

        return $rows->filter(fn (object $row): bool => str_contains(mb_strtolower(implode(' ', [
            $row->kabupaten, $row->komoditas, $row->opt, $row->jenis_opt, $row->cara_pengendalian,
        ])), $needle))->values();
    }

    /**
     * Gabungkan rantai per Kabupaten × Komoditas × OPT × Jenis OPT untuk rentang bulan tertentu.
     *
     * @param  list<CarbonImmutable>  $months
     * @return Collection<int, object>
     */
    private function kabupatenRows(int $periode, array $months, string $kabupatenId): Collection
    {
        $observations = $this->periodeQuery($periode, end($months)->endOfMonth())
            ->when($kabupatenId !== '', fn (Builder $query) => $query->whereHas('uppt', fn (Builder $upptQuery) => $upptQuery->where('kabupaten_id', $kabupatenId)))
            ->get();
        $groups = [];

        foreach ($this->history->monthlyChains($observations, $periode) as $chain) {
            $summary = $this->history->summarizeMonths($chain, $months);
            if ($summary === null || $this->history->hasNothingToReport($summary)) {
                continue;
            }

            $groupKey = implode(':', [$chain->uppt?->kabupaten_id ?? 'null', $chain->komoditas_id, $chain->opt_id, $chain->jenis_opt_id ?? 'null']);
            $group = $groups[$groupKey] ??= (object) [
                'kabupaten' => $chain->uppt?->kabupaten?->nama_kabupaten ?? '-',
                'komoditas' => $chain->komoditas?->nama_komoditas ?? '-',
                'opt' => $chain->opt?->nama_opt ?? '-',
                'jenis_opt' => $chain->jenisOpt?->nama_jenis_opt ?? '',
                'luas_komoditi_ha' => 0.0,
                'serangan_ringan' => 0.0, 'serangan_berat' => 0.0, 'serangan_jumlah' => 0.0,
                'kendali_apbd_prov' => 0.0, 'kendali_apbd_kab' => 0.0, 'kendali_masyarakat' => 0.0, 'kendali_apbn' => 0.0, 'kendali_jumlah' => 0.0,
                'kondisi_awal' => 0.0, 'kondisi_serangan_ha' => 0.0,
                'cara' => [],
            ];

            foreach ([
                'luas_komoditi_ha' => 'luas_komoditi_ha',
                'serangan_ringan' => 'tambah_ringan', 'serangan_berat' => 'tambah_berat', 'serangan_jumlah' => 'tambah_jumlah',
                'kendali_apbd_prov' => 'kendali_apbd_prov', 'kendali_apbd_kab' => 'kendali_apbd_kab',
                'kendali_masyarakat' => 'kendali_masyarakat', 'kendali_apbn' => 'kendali_apbn', 'kendali_jumlah' => 'kendali_jumlah',
                'kondisi_awal' => 'kondisi_awal', 'kondisi_serangan_ha' => 'kondisi_akhir',
            ] as $field => $source) {
                $group->{$field} = round($group->{$field} + $summary[$source], 2);
            }

            if (filled($chain->cara_pengendalian)) {
                $group->cara[] = $chain->cara_pengendalian;
            }
        }

        return collect($groups)
            ->each(function (object $group): void {
                $group->cara_pengendalian = collect($group->cara)->unique()->implode('; ');
                unset($group->cara);
            })
            ->sortBy(fn (object $group): string => mb_strtolower($group->kabupaten.'|'.$group->komoditas.'|'.$group->opt.'|'.$group->jenis_opt))
            ->values();
    }

    private function periodeQuery(int $periode, CarbonImmutable $until): Builder
    {
        return Pengamatan::with(self::RELATIONS)
            ->observedBetween(PeriodePengamatan::start($periode), $until);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query->whereHas('komoditas', fn (Builder $q) => $q->where('nama_komoditas', 'like', "%{$search}%"))
                ->orWhereHas('opt', fn (Builder $q) => $q->where('nama_opt', 'like', "%{$search}%"))
                ->orWhereHas('jenisOpt', fn (Builder $q) => $q->where('nama_jenis_opt', 'like', "%{$search}%"));
        });
    }

    private function queryWithoutJenis(Request $request): array
    {
        $parameters = $request->query();
        unset($parameters['jenis']);

        return $parameters;
    }

    private function paginateData(Collection $data, Request $request): LengthAwarePaginator
    {
        $perPage = 25;
        $currentPage = max(1, $request->integer('page', 1));

        return new LengthAwarePaginator(
            $data->forPage($currentPage, $perPage)->values(),
            $data->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /** @param array{tahun: int, bulan: int} $filters */
    private function monthlyFilename(array $filters, string $ext): string
    {
        $namaBulan = CarbonImmutable::create($filters['tahun'], $filters['bulan'], 1)->translatedFormat('F');

        return "Laporan_{$namaBulan}_{$filters['tahun']}.{$ext}";
    }

    /** @param array{tahun: int, triwulan: int} $filters */
    private function quarterlyFilename(array $filters, string $ext): string
    {
        return 'Laporan_Triwulan_'.PeriodePengamatan::quarterLabel($filters['triwulan'])."_{$filters['tahun']}.{$ext}";
    }
}
