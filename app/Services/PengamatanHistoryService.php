<?php

namespace App\Services;

use App\Models\Pengamatan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PengamatanHistoryService
{
    /** @return array{luas_komoditi_ha: float, serangan_ringan: float, serangan_berat: float, serangan_jumlah: float, kondisi_serangan: float} */
    public function previousSummary(array $attributes, ?int $excludeId = null): array
    {
        $date = CarbonImmutable::parse($attributes['tanggal_pengamatan']);
        $query = $this->historyQuery($attributes)
            ->whereYear('tanggal_pengamatan', $date->year)
            ->where(function (Builder $query) use ($date, $excludeId): void {
                $query->whereDate('tanggal_pengamatan', '<', $date->toDateString());

                if ($excludeId !== null) {
                    $query->orWhere(function (Builder $query) use ($date, $excludeId): void {
                        $query->whereDate('tanggal_pengamatan', $date->toDateString())
                            ->where('id', '<', $excludeId);
                    });
                } else {
                    $query->orWhereDate('tanggal_pengamatan', $date->toDateString());
                }
            });

        $summary = $this->emptySummary();

        foreach ($query->orderBy('tanggal_pengamatan')->orderBy('id')->get() as $pengamatan) {
            $summary = $this->applyObservation($summary, $pengamatan);
        }

        return $summary;
    }

    /** @param Collection<int, Pengamatan> $pengamatans
     * @return Collection<int, Pengamatan>
     */
    public function attachRunningTotals(Collection $pengamatans): Collection
    {
        $states = [];
        $ordered = $pengamatans->sort(function (Pengamatan $first, Pengamatan $second): int {
            $dateComparison = $first->tanggal_pengamatan->toDateString() <=> $second->tanggal_pengamatan->toDateString();

            return $dateComparison !== 0 ? $dateComparison : $first->id <=> $second->id;
        });

        foreach ($ordered as $pengamatan) {
            $key = $this->historyKey($pengamatan);
            $state = $states[$key] ?? $this->emptySummary();
            $state = $this->applyObservation($state, $pengamatan);

            $pengamatan->setAttribute('serangan_ringan_berjalan', $state['serangan_ringan']);
            $pengamatan->setAttribute('serangan_berat_berjalan', $state['serangan_berat']);
            $pengamatan->setAttribute('serangan_jumlah_berjalan', $state['serangan_jumlah']);
            $pengamatan->setAttribute('kendali_jumlah', $this->controlTotal($pengamatan));
            $pengamatan->setAttribute('kondisi_serangan_ha', $state['kondisi_serangan']);
            $states[$key] = $state;
        }

        return $pengamatans;
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
        $ringan = (float) $pengamatan->serangan_ringan;
        $berat = (float) $pengamatan->serangan_berat;
        $jumlahSeranganBaru = $ringan + $berat;

        $summary['luas_komoditi_ha'] = (float) $pengamatan->luas_komoditi_ha;
        $summary['serangan_ringan'] = round($summary['serangan_ringan'] + $ringan, 2);
        $summary['serangan_berat'] = round($summary['serangan_berat'] + $berat, 2);
        $summary['serangan_jumlah'] = round($summary['serangan_ringan'] + $summary['serangan_berat'], 2);
        $summary['kondisi_serangan'] = round(max(
            0,
            $summary['kondisi_serangan'] + $jumlahSeranganBaru - $this->controlTotal($pengamatan)
        ), 2);

        return $summary;
    }

    private function controlTotal(Pengamatan $pengamatan): float
    {
        return round(
            (float) $pengamatan->kendali_apbd_kab
            + (float) $pengamatan->kendali_apbd_prov
            + (float) $pengamatan->kendali_masyarakat
            + (float) $pengamatan->kendali_apbn,
            2
        );
    }

    private function historyQuery(array $attributes): Builder
    {
        return Pengamatan::query()
            ->where('uppt_id', $attributes['uppt_id'])
            ->where('komoditas_id', $attributes['komoditas_id'])
            ->where('opt_id', $attributes['opt_id'])
            ->where(function (Builder $query) use ($attributes): void {
                if (empty($attributes['jenis_opt_id'])) {
                    $query->whereNull('jenis_opt_id');
                } else {
                    $query->where('jenis_opt_id', $attributes['jenis_opt_id']);
                }
            })
            ->where(function (Builder $query) use ($attributes): void {
                if (empty($attributes['kecamatan_id'])) {
                    $query->whereNull('kecamatan_id');
                } else {
                    $query->where('kecamatan_id', $attributes['kecamatan_id']);
                }
            });
    }

    private function historyKey(Pengamatan $pengamatan): string
    {
        return implode(':', [
            $pengamatan->uppt_id,
            $pengamatan->kecamatan_id ?? 'null',
            $pengamatan->komoditas_id,
            $pengamatan->opt_id,
            $pengamatan->jenis_opt_id ?? 'null',
            $pengamatan->tanggal_pengamatan->year,
        ]);
    }
}
