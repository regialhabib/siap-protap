<?php

namespace App\Services;

use App\Models\Pengamatan;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Rantai pengamatan = UPPT + Kecamatan + Komoditas + OPT + Jenis OPT dalam satu periode (Feb–Jan).
 * Setiap entri menambah serangan dan pengendalian; kondisi = kondisi sebelumnya + tambah serangan − pengendalian.
 */
class PengamatanHistoryService
{
    /** @var list<string> */
    public const CONTROL_FIELDS = ['kendali_apbd_prov', 'kendali_apbd_kab', 'kendali_masyarakat', 'kendali_apbn'];

    /** @return array{luas_komoditi_ha: float, serangan_ringan: float, serangan_berat: float, serangan_jumlah: float, kondisi_serangan: float} */
    public function previousSummary(array $attributes, ?int $excludeId = null): array
    {
        $candidate = $this->candidate($attributes, $excludeId);
        $summary = $this->emptySummary();

        foreach ($this->sorted($this->chainObservations($candidate, $excludeId)) as $pengamatan) {
            if ($this->sortKey($pengamatan) < $this->sortKey($candidate)) {
                $summary = $this->applyObservation($summary, $pengamatan);
            }
        }

        return $summary;
    }

    /**
     * Konteks rantai untuk formulir: entri terakhir sebelum kandidat dan entri lain pada bulan yang sama.
     *
     * @param  array<string, mixed>  $attributes
     * @return array{terakhir: ?Pengamatan, bulan_sama: Collection<int, Pengamatan>}
     */
    public function chainContext(array $attributes, ?int $excludeId = null): array
    {
        $candidate = $this->candidate($attributes, $excludeId);
        $observations = $this->sorted($this->chainObservations($candidate, $excludeId));
        $monthKey = PeriodePengamatan::monthKey($candidate->tanggal_pengamatan);

        return [
            'terakhir' => $observations->last(fn (Pengamatan $pengamatan): bool => $this->sortKey($pengamatan) < $this->sortKey($candidate)),
            'bulan_sama' => $observations
                ->filter(fn (Pengamatan $pengamatan): bool => PeriodePengamatan::monthKey($pengamatan->tanggal_pengamatan) === $monthKey)
                ->values(),
        ];
    }

    /**
     * Cari entri pertama yang membuat kondisi serangan rantai menjadi minus jika perubahan diterapkan.
     * $existing = entri yang diubah/dihapus, $attributes = data baru (null untuk hapus).
     *
     * @return array{tanggal: CarbonImmutable, maksimal: float, is_candidate: bool}|null
     */
    public function firstNegativeCondition(?Pengamatan $existing, ?array $attributes): ?array
    {
        $candidate = $attributes === null ? null : $this->candidate($attributes, $existing?->id);
        $references = collect([$existing, $candidate])
            ->filter()
            ->unique(fn (Pengamatan $pengamatan): string => $this->chainKey($pengamatan));

        foreach ($references as $reference) {
            $observations = $this->chainObservations($reference, $existing?->id);
            if ($candidate !== null && $this->chainKey($candidate) === $this->chainKey($reference)) {
                $observations->push($candidate);
            }

            $kondisi = 0.0;
            foreach ($this->sorted($observations) as $observation) {
                $batas = round($kondisi + $this->attackTotal($observation), 2);
                $kondisi = round($batas - $this->controlTotal($observation), 2);

                if ($kondisi < 0) {
                    return [
                        'tanggal' => CarbonImmutable::parse($observation->tanggal_pengamatan),
                        'maksimal' => $batas,
                        'is_candidate' => $observation === $candidate,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @template TCollection of Collection<int, Pengamatan>
     *
     * @param  TCollection  $pengamatans
     * @return TCollection
     */
    public function attachRunningTotals(Collection $pengamatans): Collection
    {
        $states = [];

        foreach ($this->sorted($pengamatans) as $pengamatan) {
            $key = $this->chainKey($pengamatan);
            $state = $this->applyObservation($states[$key] ?? $this->emptySummary(), $pengamatan);

            $pengamatan->setAttribute('serangan_ringan_berjalan', $state['serangan_ringan']);
            $pengamatan->setAttribute('serangan_berat_berjalan', $state['serangan_berat']);
            $pengamatan->setAttribute('serangan_jumlah_berjalan', $state['serangan_jumlah']);
            $pengamatan->setAttribute('kendali_jumlah', $this->controlTotal($pengamatan));
            $pengamatan->setAttribute('kondisi_serangan_ha', $state['kondisi_serangan']);
            $states[$key] = $state;
        }

        return $pengamatans;
    }

    /**
     * Susun rantai bulanan untuk satu periode. Rantai aktif sejak bulan input pertama
     * dan angkanya dibawa ke bulan tanpa input sampai akhir periode.
     *
     * @param  Collection<int, Pengamatan>  $observations
     * @return Collection<string, object>
     */
    public function monthlyChains(Collection $observations, int $periode): Collection
    {
        $months = PeriodePengamatan::months($periode);

        return $this->sorted($observations->filter(
            fn (Pengamatan $pengamatan): bool => PeriodePengamatan::forDate($pengamatan->tanggal_pengamatan) === $periode
        ))
            ->groupBy(fn (Pengamatan $pengamatan): string => $this->chainKey($pengamatan))
            ->map(fn (Collection $chainObservations): object => $this->buildChain($chainObservations, $months));
    }

    /**
     * Ringkasan beberapa bulan berurutan dari satu rantai: jumlah tambah serangan & pengendalian,
     * kondisi sebelum bulan pertama dan kondisi akhir bulan terakhir. Null jika rantai belum aktif.
     *
     * @param  list<CarbonInterface>  $months
     * @return array{tambah_ringan: float, tambah_berat: float, tambah_jumlah: float, kendali_apbd_prov: float, kendali_apbd_kab: float, kendali_masyarakat: float, kendali_apbn: float, kendali_jumlah: float, kondisi_awal: float, kondisi_akhir: float, luas_komoditi_ha: float, ada_input: bool}|null
     */
    public function summarizeMonths(object $chain, array $months): ?array
    {
        $lastState = $chain->months[PeriodePengamatan::monthKey(end($months))] ?? null;
        if ($lastState === null) {
            return null;
        }

        $summary = [
            'tambah_ringan' => 0.0, 'tambah_berat' => 0.0, 'tambah_jumlah' => 0.0,
            'kendali_apbd_prov' => 0.0, 'kendali_apbd_kab' => 0.0, 'kendali_masyarakat' => 0.0, 'kendali_apbn' => 0.0, 'kendali_jumlah' => 0.0,
            'kondisi_awal' => null,
            'kondisi_akhir' => $lastState['kondisi_akhir'],
            'luas_komoditi_ha' => $lastState['luas_komoditi_ha'],
            'ada_input' => false,
        ];

        foreach ($months as $month) {
            $state = $chain->months[PeriodePengamatan::monthKey($month)] ?? null;
            if ($state === null) {
                continue;
            }

            $summary['kondisi_awal'] ??= $state['kondisi_awal'];
            $summary['ada_input'] = $summary['ada_input'] || $state['ada_input'];
            foreach (['tambah_ringan', 'tambah_berat', 'tambah_jumlah', ...self::CONTROL_FIELDS, 'kendali_jumlah'] as $field) {
                $summary[$field] = round($summary[$field] + $state[$field], 2);
            }
        }

        $summary['kondisi_awal'] ??= 0.0;

        return $summary;
    }

    /**
     * Rantai tanpa input dengan kondisi 0 tidak perlu dilaporkan (serangan sudah habis dikendalikan).
     *
     * @param  array{ada_input: bool, kondisi_akhir: float}  $state  state bulan atau ringkasan beberapa bulan
     */
    public function hasNothingToReport(array $state): bool
    {
        return ! $state['ada_input'] && $state['kondisi_akhir'] === 0.0;
    }

    public function kondisiLabel(float $sebelumnya, float $sekarang): string
    {
        $selisih = round($sekarang - $sebelumnya, 2);
        $status = match (true) {
            $selisih > 0 => 'Bertambah '.self::hektare($selisih).' Ha',
            $selisih < 0 => 'Berkurang '.self::hektare(abs($selisih)).' Ha',
            default => 'Tetap',
        };

        return self::hektare($sekarang).' Ha ('.$status.')';
    }

    public static function hektare(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2), '0'), '.');
    }

    public function chainKey(Pengamatan $pengamatan): string
    {
        return implode(':', [
            $pengamatan->uppt_id,
            $pengamatan->kecamatan_id ?? 'null',
            $pengamatan->komoditas_id,
            $pengamatan->opt_id,
            $pengamatan->jenis_opt_id ?? 'null',
            PeriodePengamatan::forDate($pengamatan->tanggal_pengamatan),
        ]);
    }

    /**
     * @param  Collection<int, Pengamatan>  $chainObservations
     * @param  list<CarbonImmutable>  $months
     */
    private function buildChain(Collection $chainObservations, array $months): object
    {
        $first = $chainObservations->first();
        $byMonth = $chainObservations->groupBy(fn (Pengamatan $pengamatan): string => PeriodePengamatan::monthKey($pengamatan->tanggal_pengamatan));
        $monthStates = [];
        $running = null;

        foreach ($months as $month) {
            $key = PeriodePengamatan::monthKey($month);
            $entries = $byMonth->get($key, collect());

            if ($running === null && $entries->isEmpty()) {
                $monthStates[$key] = null;

                continue;
            }

            $running ??= [
                'kumulatif_ringan' => 0.0, 'kumulatif_berat' => 0.0, 'kondisi' => 0.0,
                'luas_komoditi_ha' => 0.0, 'petugas' => null, 'tanggal_terakhir' => null,
            ];
            $state = [
                'bulan' => $month,
                'ada_input' => $entries->isNotEmpty(),
                'tambah_ringan' => round($entries->sum(fn (Pengamatan $p): float => (float) $p->serangan_ringan), 2),
                'tambah_berat' => round($entries->sum(fn (Pengamatan $p): float => (float) $p->serangan_berat), 2),
                'kondisi_awal' => $running['kondisi'],
            ];
            $state['tambah_jumlah'] = round($state['tambah_ringan'] + $state['tambah_berat'], 2);
            foreach (self::CONTROL_FIELDS as $field) {
                $state[$field] = round($entries->sum(fn (Pengamatan $p): float => (float) $p->{$field}), 2);
            }
            $state['kendali_jumlah'] = round(array_sum(Arr::only($state, self::CONTROL_FIELDS)), 2);

            if ($entries->isNotEmpty()) {
                $last = $entries->last();
                $running['luas_komoditi_ha'] = (float) $last->luas_komoditi_ha;
                $running['petugas'] = $last->relationLoaded('user') ? $last->user : null;
                $running['tanggal_terakhir'] = CarbonImmutable::parse($last->tanggal_pengamatan);
            }
            $running['kumulatif_ringan'] = round($running['kumulatif_ringan'] + $state['tambah_ringan'], 2);
            $running['kumulatif_berat'] = round($running['kumulatif_berat'] + $state['tambah_berat'], 2);
            $running['kondisi'] = round($running['kondisi'] + $state['tambah_jumlah'] - $state['kendali_jumlah'], 2);

            $monthStates[$key] = $state + [
                'kumulatif_ringan' => $running['kumulatif_ringan'],
                'kumulatif_berat' => $running['kumulatif_berat'],
                'kumulatif_jumlah' => round($running['kumulatif_ringan'] + $running['kumulatif_berat'], 2),
                'kondisi_akhir' => $running['kondisi'],
                'luas_komoditi_ha' => $running['luas_komoditi_ha'],
                'petugas' => $running['petugas'],
                'tanggal_terakhir' => $running['tanggal_terakhir'],
            ];
        }

        return (object) [
            'key' => $this->chainKey($first),
            'uppt_id' => $first->uppt_id,
            'kecamatan_id' => $first->kecamatan_id,
            'komoditas_id' => $first->komoditas_id,
            'opt_id' => $first->opt_id,
            'jenis_opt_id' => $first->jenis_opt_id,
            'uppt' => $first->relationLoaded('uppt') ? $first->uppt : null,
            'kecamatan' => $first->relationLoaded('kecamatan') ? $first->kecamatan : null,
            'komoditas' => $first->relationLoaded('komoditas') ? $first->komoditas : null,
            'opt' => $first->relationLoaded('opt') ? $first->opt : null,
            'jenisOpt' => $first->relationLoaded('jenisOpt') ? $first->jenisOpt : null,
            'cara_pengendalian' => $chainObservations->reverse()->first(fn (Pengamatan $p): bool => filled($p->cara_pengendalian))?->cara_pengendalian,
            'months' => $monthStates,
        ];
    }

    /** @return array{luas_komoditi_ha: float, serangan_ringan: float, serangan_berat: float, serangan_jumlah: float, kondisi_serangan: float} */
    private function emptySummary(): array
    {
        return [
            'luas_komoditi_ha' => 0.0,
            'serangan_ringan' => 0.0,
            'serangan_berat' => 0.0,
            'serangan_jumlah' => 0.0,
            'kondisi_serangan' => 0.0,
        ];
    }

    /** @param array{luas_komoditi_ha: float, serangan_ringan: float, serangan_berat: float, serangan_jumlah: float, kondisi_serangan: float} $summary
     * @return array{luas_komoditi_ha: float, serangan_ringan: float, serangan_berat: float, serangan_jumlah: float, kondisi_serangan: float}
     */
    private function applyObservation(array $summary, Pengamatan $pengamatan): array
    {
        $summary['luas_komoditi_ha'] = (float) $pengamatan->luas_komoditi_ha;
        $summary['serangan_ringan'] = round($summary['serangan_ringan'] + (float) $pengamatan->serangan_ringan, 2);
        $summary['serangan_berat'] = round($summary['serangan_berat'] + (float) $pengamatan->serangan_berat, 2);
        $summary['serangan_jumlah'] = round($summary['serangan_ringan'] + $summary['serangan_berat'], 2);
        $summary['kondisi_serangan'] = round(
            $summary['kondisi_serangan'] + $this->attackTotal($pengamatan) - $this->controlTotal($pengamatan),
            2
        );

        return $summary;
    }

    private function attackTotal(Pengamatan $pengamatan): float
    {
        return round((float) $pengamatan->serangan_ringan + (float) $pengamatan->serangan_berat, 2);
    }

    private function controlTotal(Pengamatan $pengamatan): float
    {
        return round(collect(self::CONTROL_FIELDS)->sum(fn (string $field): float => (float) $pengamatan->{$field}), 2);
    }

    private function candidate(array $attributes, ?int $id): Pengamatan
    {
        $candidate = new Pengamatan(Arr::only($attributes, (new Pengamatan)->getFillable()));
        $candidate->setAttribute('id', $id);

        return $candidate;
    }

    /** @return Collection<int, Pengamatan> */
    private function chainObservations(Pengamatan $reference, ?int $excludeId): Collection
    {
        $periode = PeriodePengamatan::forDate($reference->tanggal_pengamatan);

        return Pengamatan::query()
            ->where('uppt_id', $reference->uppt_id)
            ->where('komoditas_id', $reference->komoditas_id)
            ->where('opt_id', $reference->opt_id)
            ->where(fn (Builder $query) => $reference->jenis_opt_id
                ? $query->where('jenis_opt_id', $reference->jenis_opt_id)
                : $query->whereNull('jenis_opt_id'))
            ->where(fn (Builder $query) => $reference->kecamatan_id
                ? $query->where('kecamatan_id', $reference->kecamatan_id)
                : $query->whereNull('kecamatan_id'))
            ->observedBetween(PeriodePengamatan::start($periode), PeriodePengamatan::end($periode))
            ->when($excludeId !== null, fn (Builder $query) => $query->whereKeyNot($excludeId))
            ->get()
            ->toBase();
    }

    /**
     * @template TCollection of Collection<int, Pengamatan>
     *
     * @param  TCollection  $pengamatans
     * @return TCollection
     */
    private function sorted(Collection $pengamatans): Collection
    {
        return $pengamatans->sortBy(fn (Pengamatan $pengamatan): string => $this->sortKey($pengamatan))->values();
    }

    private function sortKey(Pengamatan $pengamatan): string
    {
        return CarbonImmutable::parse($pengamatan->tanggal_pengamatan)->toDateString()
            .'-'.str_pad((string) ($pengamatan->id ?? PHP_INT_MAX), 20, '0', STR_PAD_LEFT);
    }
}
