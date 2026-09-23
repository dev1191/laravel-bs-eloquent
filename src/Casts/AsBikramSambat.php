<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Casts;

use Carbon\Carbon;
use DateTimeInterface;
use Dev1191\BikramSambat\Support\BsDate;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AsBikramSambat implements CastsAttributes
{
    protected ?string $serializationFormat;

    public function __construct(?string $serializationFormat = null)
    {
        $this->serializationFormat = $serializationFormat;
    }

    /**
     * Cast the given value from database (Gregorian date) to BsDate.
     *
     * @param  Model  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     */
    public function get($model, string $key, $value, array $attributes): ?BsDate
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Database value is stored as Gregorian AD date (YYYY-MM-DD)
        return BsDate::fromAd($value);
    }

    /**
     * Prepare the given value for storage in the database (as Gregorian YYYY-MM-DD).
     *
     * @param  Model  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof BsDate) {
            return $value->toAdString();
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        // Parse input as BS date (or AD fallback) and convert to AD string for database
        $bsDate = BsDate::parse((string) $value);

        return $bsDate->toAdString();
    }
}
