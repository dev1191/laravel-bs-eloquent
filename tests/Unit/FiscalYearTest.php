<?php

use Dev1191\BikramSambat\Support\FiscalYear;

it('determines fiscal year from month and year', function () {
    expect(FiscalYear::fromYearAndMonth(2081, 1))->toBe('2080/81');
    expect(FiscalYear::fromYearAndMonth(2081, 3))->toBe('2080/81');
    expect(FiscalYear::fromYearAndMonth(2081, 4))->toBe('2081/82');
    expect(FiscalYear::fromYearAndMonth(2081, 12))->toBe('2081/82');
});

it('determines quarter from month', function () {
    expect(FiscalYear::quarterFromMonth(4))->toBe(1); // Shrawan -> Q1
    expect(FiscalYear::quarterFromMonth(7))->toBe(2); // Kartik -> Q2
    expect(FiscalYear::quarterFromMonth(10))->toBe(3); // Magh -> Q3
    expect(FiscalYear::quarterFromMonth(1))->toBe(4); // Baisakh -> Q4
});

it('calculates Gregorian date boundaries for fiscal year', function () {
    $range = FiscalYear::range('2080/81');

    expect($range['start_bs'])->toBe('2080-04-01');
    expect($range['start']->toDateString())->toBe('2023-07-17');
    expect($range['end_bs'])->toBe('2081-03-31');
    expect($range['end']->toDateString())->toBe('2024-07-15');
});
