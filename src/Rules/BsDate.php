<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Rules;

use Closure;
use Dev1191\BikramSambat\Support\BsDate as BsDateObject;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class BsDate implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $fail("The :attribute must be a valid Bikram Sambat date string.");
            return;
        }

        try {
            BsDateObject::parse((string) $value);
        } catch (Throwable $e) {
            $fail("The :attribute must be a valid Bikram Sambat date in YYYY-MM-DD format.");
        }
    }
}
