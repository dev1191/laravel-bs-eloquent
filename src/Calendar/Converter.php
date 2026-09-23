<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Calendar;

class Converter
{
    /**
     * Convert Gregorian (AD) Date to Bikram Sambat (BS)
     * Purely calculated using astronomical solar ingress and sidereal longitude.
     *
     * @return array{year: int, month: int, day: int, month_name: string}
     */
    public static function adToBs(int $adYear, int $adMonth, int $adDay): array
    {
        // Midday Julian Day in UTC
        $noonJd = AstronomicalCalendar::gregorianToJD($adYear, $adMonth, $adDay) + (12.0 - 5.75) / 24.0;
        $civilDay = AstronomicalCalendar::nepalCivilDay($noonJd);

        $sunLong = AstronomicalCalendar::sunSiderealLongitude($noonJd);
        $currentRashi = (int) floor($sunLong / 30.0);

        // Find the sankranti ingress moment for the current rashi
        $sankrantiJd = AstronomicalCalendar::findSankrantiJD($noonJd - 15.0, $currentRashi);
        $sankrantiCivilDay = AstronomicalCalendar::sankrantiToCivilDay($sankrantiJd);

        // If today's civil day is at or after this month's Sankranti civil day:
        if ($civilDay >= $sankrantiCivilDay) {
            $rashi = $currentRashi;
            $monthStartCivilDay = $sankrantiCivilDay;
        } else {
            // Before this month's civil start: belongs to previous rashi month
            $rashi = ($currentRashi + 11) % 12;
            $prevSankrantiJd = AstronomicalCalendar::findSankrantiJD($noonJd - 45.0, $rashi);
            $monthStartCivilDay = AstronomicalCalendar::sankrantiToCivilDay($prevSankrantiJd);
        }

        // Check if on or after next month's sankranti
        $nextRashi = ($rashi + 1) % 12;
        $nextSankrantiJd = AstronomicalCalendar::findSankrantiJD($noonJd + 15.0, $nextRashi);
        $nextSankrantiCivilDay = AstronomicalCalendar::sankrantiToCivilDay($nextSankrantiJd);
        if ($civilDay >= $nextSankrantiCivilDay) {
            $rashi = $nextRashi;
            $monthStartCivilDay = $nextSankrantiCivilDay;
        }

        $bsMonth = $rashi + 1;
        $bsDay = $civilDay - $monthStartCivilDay + 1;

        // Determine BS Year: Mesha Sankranti (Baisakh 1) of year AD occurs around April 13-15
        $meshaJd = AstronomicalCalendar::findSankrantiJD(AstronomicalCalendar::gregorianToJD($adYear, 4, 14), 0);
        $meshaCivilDay = AstronomicalCalendar::sankrantiToCivilDay($meshaJd);

        $bsYear = ($civilDay >= $meshaCivilDay) ? $adYear + 57 : $adYear + 56;

        return [
            'year' => $bsYear,
            'month' => $bsMonth,
            'day' => $bsDay,
            'month_name' => AstronomicalCalendar::MONTH_NAMES[$bsMonth],
        ];
    }

    /**
     * Convert Bikram Sambat (BS) Date to Gregorian (AD)
     * Purely calculated using astronomical solar ingress and sidereal longitude.
     *
     * @return array{year: int, month: int, day: int}
     */
    public static function bsToAd(int $bsYear, int $bsMonth, int $bsDay): array
    {
        $targetRashi = $bsMonth - 1;
        $approxAdYear = ($bsMonth >= 1 && $bsMonth <= 9) ? $bsYear - 57 : $bsYear - 56;

        $approxAdMonths = [1 => 4, 2 => 5, 3 => 6, 4 => 7, 5 => 8, 6 => 9, 7 => 10, 8 => 11, 9 => 12, 10 => 1, 11 => 2, 12 => 3];
        $approxMonth = $approxAdMonths[$bsMonth];
        $approxJd = AstronomicalCalendar::gregorianToJD($approxAdYear, $approxMonth, 14);

        $sankrantiJd = AstronomicalCalendar::findSankrantiJD($approxJd, $targetRashi);
        $sankrantiCivilDay = AstronomicalCalendar::sankrantiToCivilDay($sankrantiJd);

        $targetCivilDay = $sankrantiCivilDay + ($bsDay - 1);
        $targetJd = AstronomicalCalendar::nepalCivilDayToJD($targetCivilDay);

        [$adY, $adM, $adD] = AstronomicalCalendar::jdToGregorian($targetJd);

        return ['year' => $adY, 'month' => $adM, 'day' => $adD];
    }

    /**
     * Get the number of days in a given BS month
     * Calculated dynamically from consecutive Sankrantis.
     */
    public static function daysInBsMonth(int $bsYear, int $bsMonth): int
    {
        $start = self::bsToAd($bsYear, $bsMonth, 1);
        $nextMonth = ($bsMonth === 12) ? 1 : $bsMonth + 1;
        $nextYear = ($bsMonth === 12) ? $bsYear + 1 : $bsYear;
        $end = self::bsToAd($nextYear, $nextMonth, 1);

        $startJd = AstronomicalCalendar::gregorianToJD($start['year'], $start['month'], $start['day']);
        $endJd = AstronomicalCalendar::gregorianToJD($end['year'], $end['month'], $end['day']);

        return (int) round($endJd - $startJd);
    }
}
