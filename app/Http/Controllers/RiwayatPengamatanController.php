<?php

namespace App\Http\Controllers;

use App\Models\JenisOpt;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Services\PengamatanHistoryService;
use App\Services\PeriodePengamatan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
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

        if ($filters['opt_id'] !== '') {
            $query->where('opt_id', $filters['opt_id']);
        }

        if ($filters['jenis_opt_id'] !== '') {
            $query->where('jenis_opt_id', $filters['jenis_opt_id']);
        }

        $hasFilters = collect(['tahun', 'kabupaten_id', 'uppt_id', 'kecamatan_id', 'komoditas_id', 'opt_id', 'jenis_opt_id'])
            ->contains(fn (string $filter): bool => $request->filled($filter));
        $chains = $hasFilters
            ? $this->chainHistories($history->monthlyChains($query->get(), $periode), $periode, $history)
            : collect();

        return view('pengamatan.riwayat', [
            'data' => $this->paginateRows($chains, $request),
            'chainLabels' => $chains->map(fn (object $chain): string => "{$chain->uppt} · Kec. {$chain->kecamatan} — {$chain->komoditas} · {$chain->opt}")->all(),
            'tahun' => $periode,
            'filters' => $filters,
            'hasFilters' => $hasFilters,
            'kabupatens' => Kabupaten::orderBy('nama_kabupaten')->get(),
            'uppts' => Uppt::with('kabupaten:id,nama_kabupaten')
                ->orderBy('nama_uppt')
                ->get(['id', 'nama_uppt', 'kabupaten_id']),
            'kecamatans' => $filters['uppt_id'] === ''
                ? collect()
                : Kecamatan::where('uppt_id', $filters['uppt_id'])->orderBy('nama_kecamatan')->get(),
            'komoditasList' => Komoditas::orderBy('nama_komoditas')->get(),
            'opts' => Opt::with(['jenisOpts' => fn ($jenisQuery) => $jenisQuery->orderBy('nama_jenis_opt')])->orderBy('nama_opt')->get(),
        ]);
    }

    /** @return array{tahun: int, kabupaten_id: string, uppt_id: string, kecamatan_id: string, komoditas_id: string, opt_id: string, jenis_opt_id: string} */
    private function validatedFilters(Request $request): array
    {
        $validator = Validator::make($request->query(), [
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'kabupaten_id' => ['nullable', 'exists:kabupatens,id'],
            'uppt_id' => ['nullable', 'exists:uppts,id'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'komoditas_id' => ['nullable', 'exists:komoditas,id'],
            'opt_id' => ['nullable', 'exists:opts,id'],
            'jenis_opt_id' => ['nullable', 'exists:jenis_opts,id'],
        ]);

        $validator->after(function (LaravelValidator $validator) use ($request): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $kabupatenId = $request->query('kabupaten_id');
            $upptId = $request->query('uppt_id');
            $kecamatanId = $request->query('kecamatan_id');
            $optId = $request->query('opt_id');
            $jenisOptId = $request->query('jenis_opt_id');

            if ($optId && $jenisOptId && ! JenisOpt::whereKey($jenisOptId)->where('opt_id', $optId)->exists()) {
                $validator->errors()->add('jenis_opt_id', 'Jenis OPT tidak termasuk OPT yang dipilih.');
            }

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
            'opt_id' => (string) ($filters['opt_id'] ?? ''),
            'jenis_opt_id' => (string) ($filters['jenis_opt_id'] ?? ''),
        ];
    }

    /**
     * Satu blok per rantai: identitas lokasi, 12 bulan (Feb–Jan), dan total periode.
     * Bulan sebelum rantai dimulai dan bulan yang belum berjalan ditandai kosong.
     *
     * @param  Collection<string, object>  $chains
     * @return Collection<int, object>
     */
    private function chainHistories(Collection $chains, int $periode, PengamatanHistoryService $history): Collection
    {
        $currentMonth = now()->startOfMonth();

        return $chains
            ->map(function (object $chain) use ($periode, $history, $currentMonth): object {
                $months = [];
                $visibleMonths = [];

                foreach (PeriodePengamatan::months($periode) as $month) {
                    $state = $chain->months[PeriodePengamatan::monthKey($month)];
                    $isBlank = $state === null || ($month->greaterThan($currentMonth) && ! $state['ada_input']);

                    if (! $isBlank) {
                        $visibleMonths[] = $month;
                    }

                    $months[] = (object) [
                        'bulan_label' => $month->translatedFormat('F Y'),
                        'is_blank' => $isBlank,
                        'ada_input' => ! $isBlank && $state['ada_input'],
                        'values' => $isBlank ? null : Arr::only($state, ['tambah_ringan', 'tambah_berat', 'tambah_jumlah', ...PengamatanHistoryService::CONTROL_FIELDS, 'kendali_jumlah']),
                        'kondisi_awal' => $isBlank ? null : $state['kondisi_awal'],
                        'kondisi_akhir' => $isBlank ? null : $state['kondisi_akhir'],
                        'kondisi_label' => $isBlank ? null : $history->kondisiLabel($state['kondisi_awal'], $state['kondisi_akhir']),
                    ];
                }

                $total = $history->summarizeMonths($chain, $visibleMonths);

                return (object) [
                    'kabupaten' => $chain->uppt?->kabupaten?->nama_kabupaten ?? '-',
                    'uppt' => $chain->uppt?->nama_uppt ?? '-',
                    'kecamatan' => $chain->kecamatan?->nama_kecamatan ?? '-',
                    'komoditas' => $chain->komoditas?->nama_komoditas ?? '-',
                    'opt' => ($chain->opt?->nama_opt ?? '-').($chain->jenisOpt ? ' — '.$chain->jenisOpt->nama_jenis_opt : ''),
                    'luas_komoditi_ha' => $total['luas_komoditi_ha'],
                    'months' => $months,
                    'total' => $total,
                ];
            })
            ->sortBy(fn (object $chain): string => mb_strtolower(implode('|', [
                $chain->kabupaten, $chain->uppt, $chain->kecamatan, $chain->komoditas, $chain->opt,
            ])))
            ->values();
    }

    /** @param Collection<int, object> $rows */
    private function paginateRows(Collection $rows, Request $request): LengthAwarePaginator
    {
        $perPage = 1;
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
