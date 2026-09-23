<?php

use Dev1191\BikramSambat\Casts\AsBikramSambat;
use Dev1191\BikramSambat\Support\BsDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('test_posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->date('published_at')->nullable();
        $table->timestamps();
    });
});

class TestPost extends Model
{
    protected $table = 'test_posts';
    protected $guarded = [];

    protected $casts = [
        'published_at' => AsBikramSambat::class,
    ];
}

it('automatically converts BS string input to Gregorian AD date in database', function () {
    $post = TestPost::create([
        'title' => 'First Post',
        'published_at' => '2081-01-01',
    ]);

    // Fresh instance from database
    $post->refresh();

    // Attribute cast returns BsDate
    expect($post->published_at)->toBeInstanceOf(BsDate::class)
        ->and($post->published_at->toBsString())->toBe('2081-01-01')
        ->and($post->published_at->toAdString())->toBe('2024-04-14');

    // Raw attribute in DB is stored as Gregorian AD date
    expect($post->getRawOriginal('published_at'))->toBe('2024-04-14');
});

it('accepts BsDate instance when setting attribute', function () {
    $bsDate = BsDate::create(2081, 5, 15);

    $post = TestPost::create([
        'title' => 'Second Post',
        'published_at' => $bsDate,
    ]);

    $post->refresh();
    expect($post->published_at->toBsString())->toBe('2081-05-15');
});

it('serializes according to serialization config', function () {
    $post = TestPost::create([
        'title' => 'Dual Serialize Post',
        'published_at' => '2081-01-01',
    ]);

    // Default 'dual' serialization
    config()->set('bikram-sambat.serialization', 'dual');
    $array = $post->toArray();
    expect($array['published_at'])->toBe([
        'ad' => '2024-04-14',
        'bs' => '2081-01-01',
    ]);

    // 'bs' serialization
    config()->set('bikram-sambat.serialization', 'bs');
    $array = $post->toArray();
    expect($array['published_at'])->toBe('2081-01-01');
});
