<?php

use Dev1191\BikramSambat\Support\BsDate;
use Dev1191\BikramSambat\Exceptions\InvalidBsDateException;

it('creates a BsDate instance and formats it', function () {
    $date = BsDate::create(2081, 1, 1);

    expect($date->getYear())->toBe(2081)
        ->and($date->getMonth())->toBe(1)
        ->and($date->getDay())->toBe(1)
        ->and($date->getMonthName())->toBe('Baisakh')
        ->and($date->toBsString())->toBe('2081-01-01')
        ->and((string) $date)->toBe('2081-01-01')
        ->and($date->toAdString())->toBe('2024-04-14');
});

it('formats in Devanagari numerals and Nepali month names', function () {
    $date = BsDate::create(2081, 1, 1);

    expect($date->format('Y-m-d', inDevanagari: true))->toBe('२०८१-०१-०१')
        ->and($date->getMonthName(nepali: true))->toBe('बैशाख');
});

it('throws exception for invalid month or day', function () {
    expect(fn () => BsDate::create(2081, 13, 1))->toThrow(InvalidBsDateException::class);
    expect(fn () => BsDate::create(2081, 1, 35))->toThrow(InvalidBsDateException::class);
});

it('parses string into BsDate correctly', function () {
    $date = BsDate::parse('2081-05-15');
    expect($date->getYear())->toBe(2081)
        ->and($date->getMonth())->toBe(5)
        ->and($date->getDay())->toBe(15);
});

it('computes fiscal year and quarter', function () {
    // Month 1 (Baisakh) of 2081 falls in Fiscal Year 2080/81 (Quarter 4)
    $baisakh = BsDate::create(2081, 1, 1);
    expect($baisakh->fiscalYear())->toBe('2080/81')
        ->and($baisakh->fiscalQuarter())->toBe(4);

    // Month 4 (Shrawan) starts Fiscal Year 2081/82 (Quarter 1)
    $shrawan = BsDate::create(2081, 4, 1);
    expect($shrawan->fiscalYear())->toBe('2081/82')
        ->and($shrawan->fiscalQuarter())->toBe(1);
});

it('adds and subtracts days accurately', function () {
    $start = BsDate::create(2081, 1, 1);
    $nextDay = $start->addDays(1);

    expect($nextDay->toBsString())->toBe('2081-01-02');
    expect($nextDay->subDays(1)->toBsString())->toBe('2081-01-01');
});
