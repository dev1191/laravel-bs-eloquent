<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Concerns;

use DateTimeInterface;
use Dev1191\BikramSambat\Calendar\Converter;
use Dev1191\BikramSambat\Support\BsDate;
use Dev1191\BikramSambat\Support\FiscalYear;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasBikramSambatScopes
 *
 * Provides high-performance Eloquent scopes for querying dates using Bikram Sambat.
 * All BS inputs are converted to Gregorian date ranges, preserving SQL index usage.
 */
trait HasBikramSambatScopes
{
    /**
     * Scope query to a specific BS date.
     */
    public function scopeWhereBs(Builder $query, string $column, string|BsDate|DateTimeInterface $bsDate): Builder
    {
        $parsed = ($bsDate instanceof BsDate) ? $bsDate : BsDate::parse($bsDate);

        return $query->whereDate($column, $parsed->toAdString());
    }

    /**
     * Scope query to a specific BS year and month.
     */
    public function scopeWhereBsMonth(Builder $query, string $column, int $bsYear, int $bsMonth): Builder
    {
        $startAdArr = Converter::bsToAd($bsYear, $bsMonth, 1);
        $daysInMonth = Converter::daysInBsMonth($bsYear, $bsMonth);
        $endAdArr = Converter::bsToAd($bsYear, $bsMonth, $daysInMonth);

        $startAd = sprintf('%04d-%02d-%02d', $startAdArr['year'], $startAdArr['month'], $startAdArr['day']);
        $endAd = sprintf('%04d-%02d-%02d', $endAdArr['year'], $endAdArr['month'], $endAdArr['day']);

        return $query->whereBetween($column, [$startAd, $endAd]);
    }

    /**
     * Scope query to an entire BS year.
     */
    public function scopeWhereBsYear(Builder $query, string $column, int $bsYear): Builder
    {
        $startAdArr = Converter::bsToAd($bsYear, 1, 1);
        $daysInChaitra = Converter::daysInBsMonth($bsYear, 12);
        $endAdArr = Converter::bsToAd($bsYear, 12, $daysInChaitra);

        $startAd = sprintf('%04d-%02d-%02d', $startAdArr['year'], $startAdArr['month'], $startAdArr['day']);
        $endAd = sprintf('%04d-%02d-%02d', $endAdArr['year'], $endAdArr['month'], $endAdArr['day']);

        return $query->whereBetween($column, [$startAd, $endAd]);
    }

    /**
     * Scope query between two BS dates (inclusive).
     */
    public function scopeWhereBsBetween(Builder $query, string $column, string|BsDate $fromBs, string|BsDate $toBs): Builder
    {
        $from = ($fromBs instanceof BsDate) ? $fromBs : BsDate::parse($fromBs);
        $to = ($toBs instanceof BsDate) ? $toBs : BsDate::parse($toBs);

        return $query->whereBetween($column, [$from->toAdString(), $to->toAdString()]);
    }

    /**
     * Scope query to a specific Nepali Fiscal Year (e.g., '2080/81').
     */
    public function scopeWhereBsFiscalYear(Builder $query, string $column, string $fiscalYear): Builder
    {
        $range = FiscalYear::range($fiscalYear);

        return $query->whereBetween($column, [
            $range['start']->toDateString(),
            $range['end']->toDateString(),
        ]);
    }

    /**
     * Scope query to a specific Nepali Fiscal Quarter (1 to 4).
     */
    public function scopeWhereBsQuarter(Builder $query, string $column, int $bsYear, int $quarter): Builder
    {
        [$startMonth, $endMonth, $endYear] = match ($quarter) {
            1 => [4, 6, $bsYear],
            2 => [7, 9, $bsYear],
            3 => [10, 12, $bsYear],
            4 => [1, 3, $bsYear + 1],
            default => [4, 6, $bsYear],
        };

        $startAdArr = Converter::bsToAd($bsYear, $startMonth, 1);
        $daysInEndMonth = Converter::daysInBsMonth($endYear, $endMonth);
        $endAdArr = Converter::bsToAd($endYear, $endMonth, $daysInEndMonth);

        $startAd = sprintf('%04d-%02d-%02d', $startAdArr['year'], $startAdArr['month'], $startAdArr['day']);
        $endAd = sprintf('%04d-%02d-%02d', $endAdArr['year'], $endAdArr['month'], $endAdArr['day']);

        return $query->whereBetween($column, [$startAd, $endAd]);
    }
}
