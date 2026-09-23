<?php

use Dev1191\BikramSambat\Exceptions\InvalidBsDateException;
use Dev1191\BikramSambat\Support\BsDate;

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
    $nextDayBs = $start->addBsDays(1);

    expect($nextDay->toBsString())->toBe('2081-01-02')
        ->and($nextDayBs->toBsString())->toBe('2081-01-02');
    expect($nextDay->subDays(1)->toBsString())->toBe('2081-01-01');
    expect($nextDay->subBsDays(1)->toBsString())->toBe('2081-01-01');
});

it('adds and subtracts months with day clamping', function () {
    $date = BsDate::create(2081, 1, 15);
    expect($date->addBsMonths(2)->toBsString())->toBe('2081-03-15');
    expect($date->subBsMonths(1)->toBsString())->toBe('2080-12-15');

    // Clamping test: Baisakh has 31 days in 2081, test clamping when target month has fewer days
    $endMonth = BsDate::create(2081, 1, 31);
    $nextMonth = $endMonth->addBsMonths(1);
    expect($nextMonth->getMonth())->toBe(2)
        ->and($nextMonth->getDay())->toBeLessThanOrEqual($nextMonth->daysInMonth());
});

it('adds and subtracts years with day clamping', function () {
    $date = BsDate::create(2080, 5, 20);
    expect($date->addBsYears(2)->toBsString())->toBe('2082-05-20');
    expect($date->subBsYears(1)->toBsString())->toBe('2079-05-20');
});

it('calculates start and end period boundaries', function () {
    $date = BsDate::create(2081, 2, 15);

    expect($date->startOfBsMonth()->toBsString())->toBe('2081-02-01')
        ->and($date->endOfBsMonth()->getDay())->toBe($date->daysInMonth())
        ->and($date->startOfBsYear()->toBsString())->toBe('2081-01-01')
        ->and($date->endOfBsYear()->getMonth())->toBe(12);

    // Fiscal Year boundaries (Month 2 falls in FY 2080/81)
    expect($date->startOfBsFiscalYear()->toBsString())->toBe('2080-04-01')
        ->and($date->endOfBsFiscalYear()->getMonth())->toBe(3)
        ->and($date->endOfBsFiscalYear()->getYear())->toBe(2081);

    // Shrawan (Month 4) starts FY 2081/82
    $shrawan = BsDate::create(2081, 4, 10);
    expect($shrawan->startOfBsFiscalYear()->toBsString())->toBe('2081-04-01')
        ->and($shrawan->endOfBsFiscalYear()->getYear())->toBe(2082);

    // Quarter boundaries
    expect($shrawan->startOfBsQuarter()->toBsString())->toBe('2081-04-01')
        ->and($shrawan->endOfBsQuarter()->getMonth())->toBe(6);
});

it('provides carbon parity comparison helpers', function () {
    $today = BsDate::today();
    expect($today->isToday())->toBeTrue()
        ->and($today->isYesterday())->toBeFalse()
        ->and($today->isTomorrow())->toBeFalse()
        ->and($today->isCurrentMonth())->toBeTrue()
        ->and($today->isCurrentYear())->toBeTrue();

    $yesterday = $today->subDays(1);
    expect($yesterday->isYesterday())->toBeTrue()
        ->and($yesterday->isPast())->toBeTrue();

    $tomorrow = $today->addDays(1);
    expect($tomorrow->isTomorrow())->toBeTrue()
        ->and($tomorrow->isFuture())->toBeTrue();

    expect($today->isSameMonth($today->toBsString()))->toBeTrue();
    expect($today->isSameYear($yesterday))->toBeTrue();
});

it('formats extended date tokens accurately', function () {
    // 2081-01-01 BS is Sunday (2024-04-14 AD)
    $date = BsDate::create(2081, 1, 1);

    expect($date->format('d'))->toBe('01')
        ->and($date->format('j'))->toBe('1')
        ->and($date->format('l'))->toBe('Sunday')
        ->and($date->format('l', inDevanagari: true))->toBe('आइतबार')
        ->and($date->format('D'))->toBe('Sun')
        ->and($date->format('D', inDevanagari: true))->toBe('आइत')
        ->and($date->format('w'))->toBe('0')
        ->and($date->format('N'))->toBe('7')
        ->and($date->format('S'))->toBe('st')
        ->and($date->format('m'))->toBe('01')
        ->and($date->format('n'))->toBe('1')
        ->and($date->format('F'))->toBe('Baisakh')
        ->and($date->format('F', inDevanagari: true))->toBe('बैशाख')
        ->and($date->format('M'))->toBe('Bai')
        ->and($date->format('M', inDevanagari: true))->toBe('बै')
        ->and($date->format('t'))->toBe((string) $date->daysInMonth())
        ->and($date->format('Q'))->toBe('4')
        ->and($date->format('x'))->toBe('2080/81');
});

it('formats diffForHumans in Nepali and English', function () {
    $now = BsDate::today();

    // Past date (3 days ago)
    $threeDaysAgo = $now->subDays(3);
    expect($threeDaysAgo->diffForHumans($now, locale: 'en'))->toBe('3 days ago')
        ->and($threeDaysAgo->diffForHumans($now, locale: 'np'))->toBe('३ दिन अगाडि');

    // Future date (2 months later)
    $twoMonthsLater = $now->addBsMonths(2);
    expect($twoMonthsLater->diffForHumans($now, locale: 'en'))->toBe('in 2 months')
        ->and($twoMonthsLater->diffForHumans($now, locale: 'np'))->toBe('२ महिना पछि');

    // Just now
    expect($now->diffForHumans($now, locale: 'en'))->toBe('just now')
        ->and($now->diffForHumans($now, locale: 'np'))->toBe('भर्खरै');
});
