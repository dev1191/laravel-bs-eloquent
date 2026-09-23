<?php

namespace Dev1191\BikramSambat\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dev1191\BikramSambat\BikramSambat
 */
class BikramSambat extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Dev1191\BikramSambat\BikramSambat::class;
    }
}
