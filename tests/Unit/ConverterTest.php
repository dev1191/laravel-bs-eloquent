<?php

use Dev1191\BikramSambat\Calendar\Converter;

it('converts AD date to BS accurately', function () {
    // 2024-04-13 AD was 2080-12-31 BS (last day of 2080 BS)
    $bsLastDay = Converter::adToBs(2024, 4, 13);
    expect($bsLastDay['year'])->toBe(2080)
        ->and($bsLastDay['month'])->toBe(12)
        ->and($bsLastDay['day'])->toBe(31);

    // 2024-04-14 AD was 2081-01-01 BS (New Year)
    $bsNewYear = Converter::adToBs(2024, 4, 14);
    expect($bsNewYear['year'])->toBe(2081)
        ->and($bsNewYear['month'])->toBe(1)
        ->and($bsNewYear['day'])->toBe(1);
});

it('converts BS date to AD accurately', function () {
    $ad = Converter::bsToAd(2081, 1, 1);
    expect($ad['year'])->toBe(2024)
        ->and($ad['month'])->toBe(4)
        ->and($ad['day'])->toBe(14);
});

it('performs bidirectional round-trip conversions', function () {
    $dates = [
        ['year' => 2080, 'month' => 1, 'day' => 1],
        ['year' => 2081, 'month' => 5, 'day' => 15],
        ['year' => 2082, 'month' => 10, 'day' => 25],
    ];

    foreach ($dates as $d) {
        $ad = Converter::bsToAd($d['year'], $d['month'], $d['day']);
        $bs = Converter::adToBs($ad['year'], $ad['month'], $ad['day']);

        expect($bs['year'])->toBe($d['year'])
            ->and($bs['month'])->toBe($d['month'])
            ->and($bs['day'])->toBe($d['day']);
    }
});

it('returns correct days in BS month', function () {
    $days = Converter::daysInBsMonth(2081, 1);
    expect($days)->toBe(31);
});
