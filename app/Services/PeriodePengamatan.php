<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Periode pengamatan berjalan dari Februari tahun Y sampai Januari tahun Y+1 dan disebut "Tahun Y".
 */
class PeriodePengamatan
{
    public const START_MONTH = 2;

    public static function forDate(CarbonInterface|string $date): int
    {
        $date = CarbonImmutable::parse($date);

        return $date->month >= self::START_MONTH ? $date->year : $date->year - 1;
    }

    public static function current(): int
    {
        return self::forDate(now());
    }

    public static function start(int $periode): CarbonImmutable
    {
        return CarbonImmutable::create($periode, self::START_MONTH, 1)->startOfDay();
    }

    public static function end(int $periode): CarbonImmutable
    {
        return self::start($periode)->addYear()->subDay()->endOfDay();
    }

    /**
     * Awal setiap bulan dalam periode, mulai Februari sampai Januari tahun berikutnya.
     *
     * @return list<CarbonImmutable>
     */
    public static function months(int $periode): array
    {
        return array_map(fn (int $offset): CarbonImmutable => self::start($periode)->addMonths($offset), range(0, 11));
    }

    /**
     * Awal setiap bulan dalam triwulan tetap: TW I Feb–Apr, TW II Mei–Jul, TW III Agu–Okt, TW IV Nov–Jan.
     *
     * @return list<CarbonImmutable>
     */
    public static function quarterMonths(int $periode, int $triwulan): array
    {
        return array_slice(self::months($periode), ($triwulan - 1) * 3, 3);
    }

    public static function quarterForDate(CarbonInterface|string $date): int
    {
        $date = CarbonImmutable::parse($date)->startOfMonth();
        $offset = (int) self::start(self::forDate($date))->diffInMonths($date);

        return intdiv($offset, 3) + 1;
    }

    public static function monthKey(CarbonInterface $date): string
    {
        return $date->format('Y-m');
    }

    public static function quarterLabel(int $triwulan): string
    {
        return ['I', 'II', 'III', 'IV'][$triwulan - 1];
    }
}
