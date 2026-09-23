<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat;

use Carbon\Carbon;
use DateTimeInterface;
use Dev1191\BikramSambat\Calendar\Converter;
use Dev1191\BikramSambat\Support\BsDate;
use Dev1191\BikramSambat\Support\FiscalYear;

class BikramSambat
{
    /**
     * Parse date to BsDate instance
     */
    public static function parse(string|DateTimeInterface|BsDate $date): BsDate
    {
        return BsDate::parse($date);
    }

    /**
     * Create a BsDate instance
     */
    public static function create(int $year, int $month, int $day): BsDate
    {
        return BsDate::create($year, $month, $day);
    }

    /**
     * Current BS date
     */
    public static function now(): BsDate
    {
        return BsDate::now();
    }

    /**
     * Current BS date (alias for now)
     */
    public static function today(): BsDate
    {
        return BsDate::today();
    }

    /**
     * Convert AD date to BS array
     *
     * @return array{year: int, month: int, day: int, month_name: string}
     */
    public static function toBs(int|string|DateTimeInterface $yearOrDate, ?int $month = null, ?int $day = null): array
    {
        if (is_int($yearOrDate) && $month !== null && $day !== null) {
            return Converter::adToBs($yearOrDate, $month, $day);
        }

        $carbon = ($yearOrDate instanceof Carbon) ? $yearOrDate : Carbon::parse($yearOrDate);

        return Converter::adToBs((int) $carbon->year, (int) $carbon->month, (int) $carbon->day);
    }

    /**
     * Convert BS date to AD array
     *
     * @return array{year: int, month: int, day: int}
     */
    public static function toAd(int $bsYear, int $bsMonth, int $bsDay): array
    {
        return Converter::bsToAd($bsYear, $bsMonth, $bsDay);
    }

    /**
     * Get days in a BS month
     */
    public static function daysInMonth(int $bsYear, int $bsMonth): int
    {
        return Converter::daysInBsMonth($bsYear, $bsMonth);
    }

    /**
     * Get Fiscal Year string for year & month
     */
    public static function fiscalYear(int $year, int $month): string
    {
        return FiscalYear::fromYearAndMonth($year, $month);
    }

    /**
     * Get range of dates for Fiscal Year
     */
    public static function fiscalYearRange(string $fiscalYear): array
    {
        return FiscalYear::range($fiscalYear);
    }
}
