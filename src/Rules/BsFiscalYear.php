<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Rules;

use Closure;
use Dev1191\BikramSambat\Support\FiscalYear;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class BsFiscalYear implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail("The :attribute must be a string in Nepali Fiscal Year format (e.g. 2080/81).");
            return;
        }

        try {
            FiscalYear::range($value);
        } catch (Throwable $e) {
            $fail("The :attribute is not a valid Nepali Fiscal Year (expected format: YYYY/YY, e.g., 2080/81).");
        }
    }
}
