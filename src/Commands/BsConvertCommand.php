<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Commands;

use Dev1191\BikramSambat\Support\BsDate;
use Illuminate\Console\Command;

class BsConvertCommand extends Command
{
    public $signature = 'bs:convert
                        {date : Date string to convert (e.g. 2081-01-01 or 2024-04-14)}
                        {--from= : Source format: "bs" or "ad" (auto-detected if omitted)}';

    public $description = 'Convert date between Bikram Sambat (BS) and Gregorian (AD)';

    public function handle(): int
    {
        $input = trim((string) $this->argument('date'));
        $from = strtolower((string) ($this->option('from') ?? ''));

        try {
            if ($from === 'ad') {
                $parsed = BsDate::fromAd($input);
            } elseif ($from === 'bs') {
                $parsed = BsDate::parse($input);
            } else {
                // Auto-detect: if year is <= 2050 and fits standard AD, interpret as AD unless explicitly in BS range
                if (preg_match('/^(\d{4})[-\/\.](\d{1,2})[-\/\.](\d{1,2})$/', $input, $matches)) {
                    $y = (int) $matches[1];
                    $parsed = ($y <= 2060) ? BsDate::fromAd($input) : BsDate::parse($input);
                } else {
                    $parsed = BsDate::parse($input);
                }
            }

            $this->info("Bikram Sambat (BS): " . $parsed->toBsString() . " (" . $parsed->getMonthName() . ")");
            $this->info("Gregorian (AD):     " . $parsed->toAdString());
            $this->info("Day:                " . $parsed->getDayName() . " (" . $parsed->getDayName(nepali: true) . ")");
            $this->info("Fiscal Year:        " . $parsed->fiscalYear() . " (Q" . $parsed->fiscalQuarter() . ")");
            $this->info("Devanagari:         " . $parsed->format('Y-m-d', inDevanagari: true) . " (" . $parsed->getMonthName(nepali: true) . ")");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to convert date: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
