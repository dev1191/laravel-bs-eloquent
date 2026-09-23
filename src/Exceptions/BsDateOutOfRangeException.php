<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Exceptions;

use OutOfRangeException;

class BsDateOutOfRangeException extends OutOfRangeException
{
    public static function forYear(int $year, int $min, int $max): self
    {
        return new self("Year {$year} is out of supported Bikram Sambat range ({$min} to {$max}).");
    }
}
