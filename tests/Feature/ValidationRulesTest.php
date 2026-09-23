<?php

use Dev1191\BikramSambat\Rules\BsAfter;
use Dev1191\BikramSambat\Rules\BsBefore;
use Dev1191\BikramSambat\Rules\BsDate;
use Dev1191\BikramSambat\Rules\BsFiscalYear;
use Illuminate\Support\Facades\Validator;

it('validates bs_date with custom rule and string rule', function () {
    // Valid date
    $v1 = Validator::make(['date' => '2081-01-01'], ['date' => new BsDate]);
    expect($v1->passes())->toBeTrue();

    $v2 = Validator::make(['date' => '2081-01-01'], ['date' => 'bs_date']);
    expect($v2->passes())->toBeTrue();

    // Invalid day (month 1 in 2081 has 31 days)
    $v3 = Validator::make(['date' => '2081-01-35'], ['date' => 'bs_date']);
    expect($v3->fails())->toBeTrue();

    // Invalid format
    $v4 = Validator::make(['date' => 'not-a-date'], ['date' => 'bs_date']);
    expect($v4->fails())->toBeTrue();
});

it('validates bs_after rule', function () {
    $v1 = Validator::make(['date' => '2081-01-02'], ['date' => new BsAfter('2081-01-01')]);
    expect($v1->passes())->toBeTrue();

    $v2 = Validator::make(['date' => '2080-12-30'], ['date' => new BsAfter('2081-01-01')]);
    expect($v2->fails())->toBeTrue();

    // Via string rule
    $v3 = Validator::make(['date' => '2081-05-01'], ['date' => 'bs_after:2081-01-01']);
    expect($v3->passes())->toBeTrue();
});

it('validates bs_before rule', function () {
    $v1 = Validator::make(['date' => '2080-12-30'], ['date' => new BsBefore('2081-01-01')]);
    expect($v1->passes())->toBeTrue();

    $v2 = Validator::make(['date' => '2081-02-01'], ['date' => new BsBefore('2081-01-01')]);
    expect($v2->fails())->toBeTrue();

    // Via string rule
    $v3 = Validator::make(['date' => '2080-01-01'], ['date' => 'bs_before:2081-01-01']);
    expect($v3->passes())->toBeTrue();
});

it('validates bs_fiscal_year rule', function () {
    $v1 = Validator::make(['fy' => '2080/81'], ['fy' => new BsFiscalYear]);
    expect($v1->passes())->toBeTrue();

    $v2 = Validator::make(['fy' => '2080/81'], ['fy' => 'bs_fiscal_year']);
    expect($v2->passes())->toBeTrue();

    $v3 = Validator::make(['fy' => '2080-81'], ['fy' => 'bs_fiscal_year']);
    expect($v3->fails())->toBeTrue();
});

it('supports localized validation messages in Nepali and English', function () {
    app()->setLocale('np');
    $vNp = Validator::make(['date' => 'invalid'], ['date' => 'bs_date']);
    expect($vNp->fails())->toBeTrue()
        ->and($vNp->errors()->first('date'))->toContain('एक मान्य विक्रम संवत् मिति हुनुपर्छ');

    app()->setLocale('en');
    $vEn = Validator::make(['date' => 'invalid'], ['date' => 'bs_date']);
    expect($vEn->fails())->toBeTrue()
        ->and($vEn->errors()->first('date'))->toContain('must be a valid Bikram Sambat date');
});
