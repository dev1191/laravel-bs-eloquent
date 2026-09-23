<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat;

use Dev1191\BikramSambat\Commands\BsConvertCommand;
use Dev1191\BikramSambat\Support\BsDate;
use Dev1191\BikramSambat\Support\FiscalYear;
use Illuminate\Support\Facades\Validator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BikramSambatServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-bs-eloquent')
            ->hasConfigFile('bikram-sambat')
            ->hasTranslations()
            ->hasCommand(BsConvertCommand::class);
    }

    public function packageBooted(): void
    {
        $this->registerValidatorRules();
    }

    protected function registerValidatorRules(): void
    {
        Validator::extend('bs_date', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value) && !is_numeric($value)) {
                return false;
            }
            try {
                BsDate::parse((string) $value);
                return true;
            } catch (\Throwable) {
                return false;
            }
        });

        Validator::replacer('bs_date', function ($message, $attribute, $rule, $parameters) {
            $trans = trans('bs-eloquent::validation.bs_date');
            return ($trans !== 'bs-eloquent::validation.bs_date') ? $trans : 'The :attribute must be a valid Bikram Sambat date.';
        });

        Validator::extend('bs_after', function ($attribute, $value, $parameters, $validator) {
            $comparison = $parameters[0] ?? null;
            if (!$comparison) {
                return false;
            }
            try {
                $date = BsDate::parse((string) $value);
                $compareDate = BsDate::parse((string) $comparison);
                return $date->isAfter($compareDate);
            } catch (\Throwable) {
                return false;
            }
        });

        Validator::replacer('bs_after', function ($message, $attribute, $rule, $parameters) {
            $trans = trans('bs-eloquent::validation.bs_after', ['date' => $parameters[0] ?? '']);
            return ($trans !== 'bs-eloquent::validation.bs_after') ? $trans : 'The :attribute must be a Bikram Sambat date after :date.';
        });

        Validator::extend('bs_before', function ($attribute, $value, $parameters, $validator) {
            $comparison = $parameters[0] ?? null;
            if (!$comparison) {
                return false;
            }
            try {
                $date = BsDate::parse((string) $value);
                $compareDate = BsDate::parse((string) $comparison);
                return $date->isBefore($compareDate);
            } catch (\Throwable) {
                return false;
            }
        });

        Validator::replacer('bs_before', function ($message, $attribute, $rule, $parameters) {
            $trans = trans('bs-eloquent::validation.bs_before', ['date' => $parameters[0] ?? '']);
            return ($trans !== 'bs-eloquent::validation.bs_before') ? $trans : 'The :attribute must be a Bikram Sambat date before :date.';
        });

        Validator::extend('bs_fiscal_year', function ($attribute, $value, $parameters, $validator) {
            if (!is_string($value)) {
                return false;
            }
            try {
                FiscalYear::range($value);
                return true;
            } catch (\Throwable) {
                return false;
            }
        });

        Validator::replacer('bs_fiscal_year', function ($message, $attribute, $rule, $parameters) {
            $trans = trans('bs-eloquent::validation.bs_fiscal_year');
            return ($trans !== 'bs-eloquent::validation.bs_fiscal_year') ? $trans : 'The :attribute must be a valid Nepali Fiscal Year format (e.g. 2080/81).';
        });
    }
}
