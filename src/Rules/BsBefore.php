<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Rules;

use Closure;
use Dev1191\BikramSambat\Support\BsDate;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class BsBefore implements ValidationRule
{
    protected string $comparisonDate;

    public function __construct(string $comparisonDate)
    {
        $this->comparisonDate = $comparisonDate;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $fail("The :attribute must be a valid Bikram Sambat date string.");
            return;
        }

        try {
            $date = BsDate::parse((string) $value);
            $comparison = BsDate::parse($this->comparisonDate);

            if (!$date->isBefore($comparison)) {
                $fail("The :attribute must be a Bikram Sambat date before {$this->comparisonDate}.");
            }
        } catch (Throwable $e) {
            $fail("The :attribute must be a valid Bikram Sambat date.");
        }
    }
}
