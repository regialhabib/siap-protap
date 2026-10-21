<?php

namespace App\Http\Controllers;

use App\Models\JenisOpt;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Komoditas;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Services\PengamatanHistoryService;
use App\Services\PeriodePengamatan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as LaravelValidator;
use Illuminate\View\View;

class RiwayatPengamatanController extends Controller
{
    public function index(Request $request, PengamatanHistoryService $history): View
    {
        $filters = $this->validatedFilters($request);
        $periode = $filters['tahun'];

        $query = Pengamatan::query()
            ->with(['uppt.kabupaten', 'kecamatan', 'komoditas', 'opt', 'jenisOpt'])
            ->observedBetween(PeriodePengamatan::start($periode), PeriodePengamatan::end($periode));

        if ($filters['kabupaten_id'] !== '') {
            $query->whereHas('uppt', fn ($upptQuery) => $upptQuery->where('kabupaten_id', $filters['kabupaten_id']));
        }

        if ($filters['uppt_id'] !== '') {
            $query->where('uppt_id', $filters['uppt_id']);
        }

        if ($filters['kecamatan_id'] !== '') {
            $query->where('kecamatan_id', $filters['kecamatan_id']);
        }

        if ($filters['komoditas_id'] !== '') {
            $query->where('komoditas_id', $filters['komoditas_id']);
        }

        if ($filters['jenis_opt_id'] !== '') {
            $query->where('jenis_opt_id', $filters['jenis_opt_id']);
        }

        $hasFilters = collect(['tahun', 'kabupaten_id', 'uppt_id', 'kecamatan_id', 'komoditas_id', 'jenis_opt_id'])
            ->contains(fn (string $filter): bool => $request->filled($filter));
        $rows = $hasFilters
            ? $this->monthlyComparisonRows($history->monthlyChains($query->get(), $periode), $periode, $history)
            : collect();

        return view('pengamatan.riwayat', [
            'data' => $this->paginateRows($rows, $request),
            'tahun' => $periode,
            'filters' => $filters,
            'recordCount' => $rows->filter(fn (object $row): bool => ! $row->is_empty)->count(),
            'kabupatens' => Kabupaten::orderBy('nama_kabupaten')->get(),
            'uppts' => Uppt::with('kabupaten:id,nama_kabupaten')
                ->orderBy('nama_uppt')
                ->get(['id', 'nama_uppt', 'kabupaten_id']),
            'kecamatans' => $filters['uppt_id'] === ''
                ? collect()
                : Kecamatan::where('uppt_id', $filters['uppt_id'])->orderBy('nama_kecamatan')->get(),
            'komoditasList' => Komoditas::orderBy('nama_komoditas')->get(),
            'jenisOpts' => JenisOpt::with('opt')->orderBy('nama_jenis_opt')->get(),
        ]);
    }

    /** @return array{tahun: int, kabupaten_id: string, uppt_id: string, kecamatan_id: string, komoditas_id: string, jenis_opt_id: string} */
    private function validatedFilters(Request $request): array
    {
        $validator = Validator::make($request->query(), [
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'kabupaten_id' => ['nullable', 'exists:kabupatens,id'],
            'uppt_id' => ['nullable', 'exists:uppts,id'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'komoditas_id' => ['nullable', 'exists:komoditas,id'],
            'jenis_opt_id' => ['nullable', 'exists:jenis_opts,id'],
        ]);

        $validator->after(function (LaravelValidator $validator) use ($request): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $kabupatenId = $request->query('kabupaten_id');
            $upptId = $request->query('uppt_id');
            $kecamatanId = $request->query('kecamatan_id');

            if ($kabupatenId && $upptId && ! Uppt::whereKey($upptId)->where('kabupaten_id', $kabupatenId)->exists()) {
                $validator->errors()->add('uppt_id', 'UPPT tidak berada di Kabupaten/Kota yang dipilih.');
            }

            if ($kecamatanId) {
                $kecamatanQuery = Kecamatan::whereKey($kecamatanId);

                if ($upptId) {
                    $kecamatanQuery->where('uppt_id', $upptId);
                } elseif ($kabupatenId) {
                    $kecamatanQuery->whereHas('uppt', fn ($upptQuery) => $upptQuery->where('kabupaten_id', $kabupatenId));
                } else {
                    $validator->errors()->add('kecamatan_id', 'Pilih Kabupaten/Kota atau UPPT terlebih dahulu.');

                    return;
                }

                if (! $kecamatanQuery->exists()) {
                    $validator->errors()->add('kecamatan_id', 'Kecamatan tidak berada di wilayah yang dipilih.');
                }
            }
        });

        $filters = $validator->validate();

        return [
            'tahun' => (int) ($filters['tahun'] ?? PeriodePengamatan::current()),
            'kabupaten_id' => (string) ($filters['kabupaten_id'] ?? ''),
            'uppt_id' => (string) ($filters['uppt_id'] ?? ''),
            'kecamatan_id' => (string) ($filters['kecamatan_id'] ?? ''),
            'komoditas_id' => (string) ($filters['komoditas_id'] ?? ''),
            'jenis_opt_id' => (string) ($filters['jenis_opt_id'] ?? ''),
        ];
    }

    /**
     * Baris per bulan (Feb–Jan) untuk setiap rantai aktif: posisi bulan lalu dibandingkan dengan bulan ini.
     *
     * @param  Collection<string, object>  $chains
     * @return Collection<int, object>
     */
    private function monthlyComparisonRows(Collection $chains, int $periode, PengamatanHistoryService $history): Collection
    {
        $rows = collect();
        $previousKey = null;

        foreach (PeriodePengamatan::months($periode) as $month) {
            $monthKey = PeriodePengamatan::monthKey($month);
            $monthRows = $chains
                ->filter(fn (object $chain): bool => $chain->months[$monthKey] !== null)
                ->map(function (object $chain) use ($month, $monthKey, $previousKey, $history): object {
                    $current = $chain->months[$monthKey];
                    $previous = $previousKey === null ? null : $chain->months[$previousKey];

                    return (object) [
                        'bulan_label' => $month->translatedFormat('F Y'),
                        'is_empty' => false,
                        'ada_input' => $current['ada_input'],
                        'kabupaten' => $chain->uppt?->kabupaten?->nama_kabupaten ?? '-',
                        'uppt' => $chain->uppt?->nama_uppt ?? '-',
                        'kecamatan' => $chain->kecamatan?->nama_kecamatan ?? '-',
                        'komoditas' => $chain->komoditas?->nama_komoditas ?? '-',
                        'opt' => $chain->opt?->nama_opt ?? '-',
                        'jenis_opt' => $chain->jenisOpt?->nama_jenis_opt,
                        'previous' => $previous === null ? null : [
                            'ringan' => $previous['kumulatif_ringan'],
                            'berat' => $previous['kumulatif_berat'],
                            'jumlah' => $previous['kumulatif_jumlah'],
                            'kondisi_serangan' => $previous['kondisi_akhir'],
                        ],
                        'current' => [
                            'tambah_ringan' => $current['tambah_ringan'],
                            'tambah_berat' => $current['tambah_berat'],
                            'tambah_jumlah' => $current['tambah_jumlah'],
                            'ringan' => $current['kumulatif_ringan'],
                            'berat' => $current['kumulatif_berat'],
                            'jumlah' => $current['kumulatif_jumlah'],
                            'kendali_apbd_prov' => $current['kendali_apbd_prov'],
                            'kendali_apbd_kab' => $current['kendali_apbd_kab'],
                            'kendali_masyarakat' => $current['kendali_masyarakat'],
                            'kendali_apbn' => $current['kendali_apbn'],
                            'pengendalian' => $current['kendali_jumlah'],
                            'kondisi_serangan' => $current['kondisi_akhir'],
                            'kondisi_label' => $history->kondisiLabel($current['kondisi_awal'], $current['kondisi_akhir']),
                        ],
                    ];
                })
                ->sortBy(fn (object $row): string => mb_strtolower(implode('|', [
                    $row->kabupaten, $row->uppt, $row->kecamatan, $row->komoditas, $row->opt, $row->jenis_opt ?? '',
                ])))
                ->values();

            if ($monthRows->isEmpty()) {
                $monthRows->push((object) ['bulan_label' => $month->translatedFormat('F Y'), 'is_empty' => true]);
            }

            $rows = $rows->concat($monthRows);
            $previousKey = $monthKey;
        }

        return $rows->values();
    }

    /** @param Collection<int, object> $rows */
    private function paginateRows(Collection $rows, Request $request): LengthAwarePaginator
    {
        $perPage = 50;
        $currentPage = max(1, $request->integer('page', 1));

        return new LengthAwarePaginator(
            $rows->forPage($currentPage, $perPage)->values(),
            $rows->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
