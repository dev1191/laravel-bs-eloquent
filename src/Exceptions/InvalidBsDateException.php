<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Exceptions;

use InvalidArgumentException;

class InvalidBsDateException extends InvalidArgumentException
{
    public static function invalidFormat(string $date): self
    {
        return new self("The provided date '{$date}' is not in a valid Bikram Sambat format (YYYY-MM-DD).");
    }

    public static function invalidDate(int $year, int $month, int $day, int $maxDays): self
    {
        return new self("The date {$year}-{$month}-{$day} is invalid. Month {$month} in year {$year} only has {$maxDays} days.");
    }

    public static function invalidMonth(int $month): self
    {
        return new self("The month '{$month}' is invalid. Bikram Sambat month must be between 1 and 12.");
    }
}
