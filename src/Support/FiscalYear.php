<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Support;

use Carbon\Carbon;
use Dev1191\BikramSambat\Calendar\Converter;
use InvalidArgumentException;

class FiscalYear
{
    /**
     * Get Fiscal Year string (e.g., '2080/81') from a BsDate or year & month
     */
    public static function fromBsDate(BsDate $date): string
    {
        return self::fromYearAndMonth($date->getYear(), $date->getMonth());
    }

    public static function fromYearAndMonth(int $year, int $month): string
    {
        // In Nepal, Fiscal Year starts from Shrawan (Month 4) to Ashadh (Month 3)
        if ($month >= 4) {
            $startYear = $year;
            $endYearShort = ($year + 1) % 100;
        } else {
            $startYear = $year - 1;
            $endYearShort = $year % 100;
        }

        return sprintf('%04d/%02d', $startYear, $endYearShort);
    }

    /**
     * Get Fiscal Quarter (1 to 4)
     * Q1: Shrawan (4) - Ashwin (6)
     * Q2: Kartik (7) - Poush (9)
     * Q3: Magh (10) - Chaitra (12)
     * Q4: Baisakh (1) - Ashadh (3)
     */
    public static function quarterFromMonth(int $month): int
    {
        return match (true) {
            $month >= 4 && $month <= 6 => 1,
            $month >= 7 && $month <= 9 => 2,
            $month >= 10 && $month <= 12 => 3,
            default => 4,
        };
    }

    /**
     * Get Gregorian Date range for a given fiscal year string, e.g., '2080/81'
     *
     * @return array{start: Carbon, end: Carbon, start_bs: string, end_bs: string}
     */
    public static function range(string $fiscalYear): array
    {
        if (! preg_match('/^(\d{4})\/(\d{2})$/', trim($fiscalYear), $matches)) {
            throw new InvalidArgumentException("Invalid fiscal year format '{$fiscalYear}'. Expected 'YYYY/YY' e.g. '2080/81'.");
        }

        $startBsYear = (int) $matches[1];
        $endBsYear = $startBsYear + 1;

        // Start: Shrawan 1 of $startBsYear
        $startBs = "{$startBsYear}-04-01";
        $startAdArr = Converter::bsToAd($startBsYear, 4, 1);
        $startAd = Carbon::createFromDate($startAdArr['year'], $startAdArr['month'], $startAdArr['day'])->startOfDay();

        // End: Last day of Ashadh (Month 3) of $endBsYear
        $lastDayAshadh = Converter::daysInBsMonth($endBsYear, 3);
        $endBs = sprintf('%04d-03-%02d', $endBsYear, $lastDayAshadh);
        $endAdArr = Converter::bsToAd($endBsYear, 3, $lastDayAshadh);
        $endAd = Carbon::createFromDate($endAdArr['year'], $endAdArr['month'], $endAdArr['day'])->endOfDay();

        return [
            'start' => $startAd,
            'end' => $endAd,
            'start_bs' => $startBs,
            'end_bs' => $endBs,
        ];
    }
}
