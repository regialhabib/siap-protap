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
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as LaravelValidator;
use Illuminate\View\View;

class RiwayatPengamatanController extends Controller
{
    private const FILTERS = ['tahun', 'kabupaten_id', 'uppt_id', 'kecamatan_id', 'komoditas_id', 'opt_id', 'jenis_opt_id'];

    public function index(Request $request, PengamatanHistoryService $history): View
    {
        // POPT hanya membaca rantai UPPT-nya sendiri, jadi Kabupaten/Kota dan UPPT dikunci.
        $ownUppt = $request->user()->isPopt() ? $request->user()->uppt()->with('kabupaten')->first() : null;
        $lockedFilters = $ownUppt ? ['kabupaten_id' => (string) $ownUppt->kabupaten_id, 'uppt_id' => (string) $ownUppt->id] : [];

        $hasFilters = collect(self::FILTERS)
            ->diff(array_keys($lockedFilters))
            ->contains(fn (string $filter): bool => $request->filled($filter));
        $request->query->add($lockedFilters);
        $filters = $hasFilters ? $this->validatedFilters($request) : [...$this->emptyFilters(), ...$lockedFilters];
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

        $chain = $hasFilters
            ? $this->chainHistories($history->monthlyChains($query->get(), $periode), $periode, $history)->first()
            : null;

        return view('pengamatan.riwayat', [
            'chain' => $chain,
            'tahun' => $periode,
            'filters' => $filters,
            'hasFilters' => $hasFilters,
            'ownUppt' => $ownUppt,
            'kabupatens' => $ownUppt ? collect() : Kabupaten::orderBy('nama_kabupaten')->get(),
            'uppts' => Uppt::with('kabupaten:id,nama_kabupaten')
                ->when($ownUppt, fn ($upptQuery) => $upptQuery->whereKey($ownUppt->id))
                ->orderBy('nama_uppt')
                ->get(['id', 'nama_uppt', 'kabupaten_id']),
            'kecamatans' => $filters['uppt_id'] === ''
                ? collect()
                : Kecamatan::where('uppt_id', $filters['uppt_id'])->orderBy('nama_kecamatan')->get(),
            'komoditasList' => Komoditas::orderBy('nama_komoditas')->get(),
            'opts' => Opt::with(['jenisOpts' => fn ($jenisQuery) => $jenisQuery->orderBy('nama_jenis_opt')])->orderBy('nama_opt')->get(),
        ]);
    }

    /**
     * Semua filter wajib diisi agar hasilnya tepat satu rantai pengamatan.
     *
     * @return array{tahun: int, kabupaten_id: string, uppt_id: string, kecamatan_id: string, komoditas_id: string, opt_id: string, jenis_opt_id: string} */
    private function validatedFilters(Request $request): array
    {
        $validator = Validator::make($request->query(), [
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'kabupaten_id' => ['required', 'exists:kabupatens,id'],
            'uppt_id' => ['required', 'exists:uppts,id'],
            'kecamatan_id' => [
                Rule::requiredIf(fn (): bool => $request->filled('uppt_id') && Kecamatan::where('uppt_id', $request->query('uppt_id'))->exists()),
                'nullable',
                'exists:kecamatans,id',
            ],
            'komoditas_id' => ['required', 'exists:komoditas,id'],
            'opt_id' => ['required', 'exists:opts,id'],
            'jenis_opt_id' => [
                Rule::requiredIf(fn (): bool => $request->filled('opt_id') && JenisOpt::where('opt_id', $request->query('opt_id'))->exists()),
                'nullable',
                'exists:jenis_opts,id',
            ],
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
            'tahun' => (int) $filters['tahun'],
            'kabupaten_id' => (string) ($filters['kabupaten_id'] ?? ''),
            'uppt_id' => (string) ($filters['uppt_id'] ?? ''),
            'kecamatan_id' => (string) ($filters['kecamatan_id'] ?? ''),
            'komoditas_id' => (string) ($filters['komoditas_id'] ?? ''),
            'opt_id' => (string) ($filters['opt_id'] ?? ''),
            'jenis_opt_id' => (string) ($filters['jenis_opt_id'] ?? ''),
        ];
    }

    /** @return array{tahun: int, kabupaten_id: string, uppt_id: string, kecamatan_id: string, komoditas_id: string, opt_id: string, jenis_opt_id: string} */
    private function emptyFilters(): array
    {
        return ['tahun' => PeriodePengamatan::current(), ...array_fill_keys(array_slice(self::FILTERS, 1), '')];
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
                    'opt' => $chain->opt?->nama_opt ?? '-',
                    'jenis_opt' => $chain->jenisOpt?->nama_jenis_opt,
                    'luas_komoditi_ha' => $total['luas_komoditi_ha'],
                    'months' => $months,
                    'total' => $total,
                ];
            })
            ->sortBy(fn (object $chain): string => mb_strtolower(implode('|', [
                $chain->kabupaten, $chain->uppt, $chain->kecamatan, $chain->komoditas, $chain->opt, $chain->jenis_opt,
            ])))
            ->values();
    }
}
