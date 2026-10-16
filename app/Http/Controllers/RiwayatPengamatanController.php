<?php

namespace App\Http\Controllers;

use App\Models\JenisOpt;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Komoditas;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Services\PengamatanHistoryService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

        $query = Pengamatan::query()
            ->with(['uppt.kabupaten', 'kecamatan', 'komoditas', 'opt', 'jenisOpt'])
            ->whereBetween('tanggal_pengamatan', [
                Carbon::create($filters['tahun'], 1, 1)->toDateString(),
                Carbon::create($filters['tahun'], 12, 31)->toDateString(),
            ]);

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

        $observations = $query->orderBy('tanggal_pengamatan')->orderBy('id')->get();
        $history->attachRunningTotals($observations);
        $rows = $this->monthlyComparisonRows($observations);

        return view('pengamatan.riwayat', [
            'data' => $this->paginateRows($rows, $request),
            'tahun' => $filters['tahun'],
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
            'tahun' => (int) ($filters['tahun'] ?? now()->year),
            'kabupaten_id' => (string) ($filters['kabupaten_id'] ?? ''),
            'uppt_id' => (string) ($filters['uppt_id'] ?? ''),
            'kecamatan_id' => (string) ($filters['kecamatan_id'] ?? ''),
            'komoditas_id' => (string) ($filters['komoditas_id'] ?? ''),
            'jenis_opt_id' => (string) ($filters['jenis_opt_id'] ?? ''),
        ];
    }

    /** @param EloquentCollection<int, Pengamatan> $observations
     * @return Collection<int, object>
     */
    private function monthlyComparisonRows(EloquentCollection $observations): Collection
    {
        $monthlySnapshots = [];

        foreach ($observations->groupBy(fn (Pengamatan $row): int => $row->tanggal_pengamatan->month) as $month => $monthRows) {
            $monthlySnapshots[(int) $month] = $monthRows
                ->groupBy(fn (Pengamatan $row): string => $this->observationKey($row))
                ->map(fn (Collection $group): Pengamatan => $group->last());
        }

        $rows = collect();

        foreach (range(1, 12) as $month) {
            $currentSnapshots = $monthlySnapshots[$month] ?? collect();
            $previousSnapshots = $month === 1 ? collect() : ($monthlySnapshots[$month - 1] ?? collect());
            $observationKeys = $previousSnapshots->keys()->merge($currentSnapshots->keys())->unique();
            $monthRows = $observationKeys->map(function (string $key) use ($month, $currentSnapshots, $previousSnapshots): object {
                $current = $currentSnapshots->get($key);
                $previous = $previousSnapshots->get($key);
                $observation = $current ?? $previous;

                return (object) [
                    'bulan' => $month,
                    'is_empty' => false,
                    'kabupaten' => $observation->uppt?->kabupaten?->nama_kabupaten ?? '-',
                    'uppt' => $observation->uppt?->nama_uppt ?? '-',
                    'kecamatan' => $observation->kecamatan?->nama_kecamatan ?? '-',
                    'komoditas' => $observation->komoditas?->nama_komoditas ?? '-',
                    'opt' => $observation->opt?->nama_opt ?? '-',
                    'jenis_opt' => $observation->jenisOpt?->nama_jenis_opt,
                    'previous' => $this->attackSnapshot($previous),
                    'current' => $this->attackSnapshot($current),
                ];
            })->sortBy(fn (object $row): string => mb_strtolower(implode('|', [
                $row->kabupaten, $row->uppt, $row->kecamatan, $row->komoditas, $row->opt, $row->jenis_opt ?? '',
            ])))->values();

            if ($monthRows->isEmpty()) {
                $monthRows->push((object) ['bulan' => $month, 'is_empty' => true]);
            }

            $rows = $rows->concat($monthRows);
        }

        return $rows->values();
    }

    private function observationKey(Pengamatan $observation): string
    {
        return implode(':', [
            $observation->uppt_id,
            $observation->kecamatan_id ?? 'null',
            $observation->komoditas_id,
            $observation->opt_id,
            $observation->jenis_opt_id ?? 'null',
        ]);
    }

    /** @return array{ringan: float, berat: float, jumlah: float, kondisi_serangan: float}|null */
    private function attackSnapshot(?Pengamatan $observation): ?array
    {
        if ($observation === null) {
            return null;
        }

        return [
            'ringan' => (float) $observation->serangan_ringan_berjalan,
            'berat' => (float) $observation->serangan_berat_berjalan,
            'jumlah' => (float) $observation->serangan_jumlah_berjalan,
            'kondisi_serangan' => (float) $observation->kondisi_serangan_ha,
        ];
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
