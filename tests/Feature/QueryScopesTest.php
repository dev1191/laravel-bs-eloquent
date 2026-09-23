<?php

use Dev1191\BikramSambat\Casts\AsBikramSambat;
use Dev1191\BikramSambat\Concerns\HasBikramSambatScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('test_invoices', function (Blueprint $table) {
        $table->id();
        $table->string('number');
        $table->date('invoice_date');
        $table->timestamps();
    });

    TestInvoice::create(['number' => 'INV-001', 'invoice_date' => '2080-12-30']); // 2080 Chaitra
    TestInvoice::create(['number' => 'INV-002', 'invoice_date' => '2081-01-01']); // 2081 Baisakh (FY 2080/81 Q4)
    TestInvoice::create(['number' => 'INV-003', 'invoice_date' => '2081-04-15']); // 2081 Shrawan (FY 2081/82 Q1)
    TestInvoice::create(['number' => 'INV-004', 'invoice_date' => '2081-08-10']); // 2081 Mangsir (FY 2081/82 Q2)
});

class TestInvoice extends Model
{
    use HasBikramSambatScopes;

    protected $table = 'test_invoices';

    protected $guarded = [];

    protected $casts = [
        'invoice_date' => AsBikramSambat::class,
    ];
}

it('queries whereBs for exact date', function () {
    $results = TestInvoice::whereBs('invoice_date', '2081-01-01')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->number)->toBe('INV-002');
});

it('queries whereBsMonth for a specific month', function () {
    $results = TestInvoice::whereBsMonth('invoice_date', 2081, 1)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->number)->toBe('INV-002');
});

it('queries whereBsYear for an entire BS year', function () {
    $results = TestInvoice::whereBsYear('invoice_date', 2081)->get();

    expect($results)->toHaveCount(3);
});

it('queries whereBsBetween a date range', function () {
    $results = TestInvoice::whereBsBetween('invoice_date', '2080-12-01', '2081-02-01')->get();

    expect($results)->toHaveCount(2);
});

it('queries whereBsFiscalYear for fiscal year range', function () {
    $results = TestInvoice::whereBsFiscalYear('invoice_date', '2081/82')->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('number')->all())->toBe(['INV-003', 'INV-004']);
});

it('queries whereBsQuarter for fiscal quarter', function () {
    $q1 = TestInvoice::whereBsQuarter('invoice_date', 2081, 1)->get(); // Shrawan - Ashwin
    expect($q1)->toHaveCount(1)
        ->and($q1->first()->number)->toBe('INV-003');

    $q2 = TestInvoice::whereBsQuarter('invoice_date', 2081, 2)->get(); // Kartik - Poush
    expect($q2)->toHaveCount(1)
        ->and($q2->first()->number)->toBe('INV-004');
});
