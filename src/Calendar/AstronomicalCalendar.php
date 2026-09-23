<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Calendar;

class AstronomicalCalendar
{
    private const TZ_OFFSET_HOURS = 5.75;
    private const AYANAMSA_AT_J2000 = 23.858083; // Standard Lahiri J2000.0 baseline
    private const AYANAMSA_RATE_PER_CENTURY = 1.396042; // Precession per Julian century
    private const SUN_MEAN_DAILY_MOTION = 0.985647;

    public const MONTH_NAMES = [
        1 => 'Baisakh', 2 => 'Jestha', 3 => 'Ashadh', 4 => 'Shrawan',
        5 => 'Bhadra', 6 => 'Ashwin', 7 => 'Kartik', 8 => 'Mangsir',
        9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra',
    ];

    // ---------------------------------------------------------------
    // Astronomy & Solar Calculations
    // ---------------------------------------------------------------

    public static function sunSiderealLongitude(float $jd): float
    {
        $t = ($jd - 2451545.0) / 36525.0;

        $l0 = 280.46646 + 36000.76983 * $t + 0.0003032 * ($t ** 2);
        $m = 357.52911 + 35999.05029 * $t - 0.0001537 * ($t ** 2);
        $mRad = deg2rad($m);

        $c = (1.914602 - 0.004817 * $t - 0.000014 * ($t ** 2)) * sin($mRad)
            + (0.019993 - 0.000101 * $t) * sin(2 * $mRad)
            + 0.000289 * sin(3 * $mRad);

        $trueLongitude = self::normalizeDegrees($l0 + $c);

        // Standard Lahiri Ayanamsa computation
        $ayanamsa = self::AYANAMSA_AT_J2000 + (self::AYANAMSA_RATE_PER_CENTURY * $t);

        return self::normalizeDegrees($trueLongitude - $ayanamsa);
    }

    public static function findSankrantiJD(float $jdGuess, int $targetRashi): float
    {
        $target = $targetRashi * 30.0;
        $jd = $jdGuess;

        for ($i = 0; $i < 20; $i++) {
            $longitude = self::sunSiderealLongitude($jd);
            $diff = self::signedAngleDiff($target, $longitude);
            $jd += $diff / self::SUN_MEAN_DAILY_MOTION;
            if (abs($diff) < 0.00002) {
                break;
            }
        }

        return $jd;
    }

    public static function signedAngleDiff(float $target, float $current): float
    {
        return fmod($target - $current + 540.0, 360.0) - 180.0;
    }

    public static function normalizeDegrees(float $deg): float
    {
        $deg = fmod($deg, 360.0);
        return $deg < 0 ? $deg + 360.0 : $deg;
    }

    // ---------------------------------------------------------------
    // Civil Day Mapping
    // ---------------------------------------------------------------

    public static function nepalCivilDay(float $jd): int
    {
        return (int) floor($jd + 0.5 + (self::TZ_OFFSET_HOURS / 24.0));
    }

    /**
     * Map Nepal civil day to midday Julian Day in UTC
     */
    public static function nepalCivilDayToJD(int $civilDay): float
    {
        // 12:00:00 Nepal Standard Time = 06:15:00 UTC
        return ((float) $civilDay) - (self::TZ_OFFSET_HOURS / 24.0);
    }

    /**
     * Standard Sankranti boundary rule:
     * If ingress happens after sunset (~18:00 NST / ~12:15 UTC),
     * Sankranti civil day begins on the following morning.
     */
    public static function sankrantiToCivilDay(float $sankrantiJd): int
    {
        // Sample with a 6-hour sunset buffer (18:00 cutoff)
        return self::nepalCivilDay($sankrantiJd + (6.0 / 24.0));
    }

    // ---------------------------------------------------------------
    // Gregorian <-> JD (Meeus standard)
    // ---------------------------------------------------------------

    public static function gregorianToJD(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }

        $a = (int) floor($year / 100);
        $b = 2 - $a + (int) floor($a / 4);

        return floor(365.25 * ($year + 4716))
            + floor(30.6001 * ($month + 1))
            + $day + $b - 1524.5;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public static function jdToGregorian(float $jd): array
    {
        $jd += 0.5;
        $z = (int) floor($jd);
        $f = $jd - $z;

        if ($z < 2299161) {
            $a = $z;
        } else {
            $alpha = (int) floor(($z - 1867216.25) / 36524.25);
            $a = $z + 1 + $alpha - (int) floor($alpha / 4);
        }

        $b = $a + 1524;
        $c = (int) floor(($b - 122.1) / 365.25);
        $d = (int) floor(365.25 * $c);
        $e = (int) floor(($b - $d) / 30.6001);

        // Must use floor to extract the integer day, NOT round
        $day = (int) floor($b - $d - floor(30.6001 * $e));
        $month = (int) ($e < 14 ? $e - 1 : $e - 13);
        $year = (int) ($month > 2 ? $c - 4716 : $c - 4715);

        return [$year, $month, $day];
    }
}
