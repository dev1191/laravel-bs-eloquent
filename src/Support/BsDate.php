<?php

declare(strict_types=1);

namespace Dev1191\BikramSambat\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Dev1191\BikramSambat\Calendar\AstronomicalCalendar;
use Dev1191\BikramSambat\Calendar\Converter;
use Dev1191\BikramSambat\Exceptions\InvalidBsDateException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Stringable;

class BsDate implements Arrayable, Jsonable, JsonSerializable, Stringable
{
    protected int $year;
    protected int $month;
    protected int $day;
    protected string $monthName;
    protected Carbon $adDate;

    public const NEPALI_DIGITS = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
    public const NEPALI_DAY_NAMES = [
        0 => 'आइतबार', 1 => 'सोमबार', 2 => 'मङ्गलबार', 3 => 'बुधबार',
        4 => 'बिहीबार', 5 => 'शुक्रबार', 6 => 'शनिबार',
    ];

    public const NEPALI_MONTH_NAMES = [
        1 => 'बैशाख', 2 => 'जेठ', 3 => 'असार', 4 => 'श्रावण',
        5 => 'भाद्र', 6 => 'असोज', 7 => 'कार्तिक', 8 => 'मंसिर',
        9 => 'पौष', 10 => 'माघ', 11 => 'फागुन', 12 => 'चैत',
    ];

    public function __construct(int $year, int $month, int $day)
    {
        $this->validateAndSet($year, $month, $day);
    }

    protected function validateAndSet(int $year, int $month, int $day): void
    {
        if ($month < 1 || $month > 12) {
            throw InvalidBsDateException::invalidMonth($month);
        }

        $daysInMonth = Converter::daysInBsMonth($year, $month);
        if ($day < 1 || $day > $daysInMonth) {
            throw InvalidBsDateException::invalidDate($year, $month, $day, $daysInMonth);
        }

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->monthName = AstronomicalCalendar::MONTH_NAMES[$month];

        $ad = Converter::bsToAd($year, $month, $day);
        $this->adDate = Carbon::createFromDate($ad['year'], $ad['month'], $ad['day'])->startOfDay();
    }

    public static function create(int $year, int $month, int $day): self
    {
        return new self($year, $month, $day);
    }

    public static function parse(string|DateTimeInterface|CarbonInterface|self $date): self
    {
        if ($date instanceof self) {
            return new self($date->getYear(), $date->getMonth(), $date->getDay());
        }

        if ($date instanceof DateTimeInterface) {
            return self::fromAd($date);
        }

        $date = trim($date);

        // Check if string matches YYYY-MM-DD
        if (preg_match('/^(\d{4})[-\/\.](\d{1,2})[-\/\.](\d{1,2})$/', $date, $matches)) {
            $y = (int) $matches[1];
            $m = (int) $matches[2];
            $d = (int) $matches[3];

            // If year is in AD range (e.g. 1920-2050) and definitely not BS
            if ($y >= 1900 && $y <= 2050 && $y < 1970) {
                return self::fromAd(Carbon::createFromDate($y, $m, $d));
            }

            return new self($y, $m, $d);
        }

        // Try Carbon parsing as AD
        try {
            $carbon = Carbon::parse($date);
            return self::fromAd($carbon);
        } catch (\Throwable $e) {
            throw InvalidBsDateException::invalidFormat($date);
        }
    }

    public static function fromAd(DateTimeInterface|string $date): self
    {
        $carbon = ($date instanceof Carbon) ? $date : Carbon::parse($date);
        $bs = Converter::adToBs((int) $carbon->year, (int) $carbon->month, (int) $carbon->day);

        return new self($bs['year'], $bs['month'], $bs['day']);
    }

    public static function now(): self
    {
        return self::fromAd(Carbon::now());
    }

    public static function today(): self
    {
        return self::now();
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function getMonthName(bool $nepali = false): string
    {
        return $nepali ? self::NEPALI_MONTH_NAMES[$this->month] : $this->monthName;
    }

    public function getDayName(bool $nepali = false): string
    {
        $dow = $this->adDate->dayOfWeek;
        return $nepali ? self::NEPALI_DAY_NAMES[$dow] : $this->adDate->format('l');
    }

    public function toAd(): Carbon
    {
        return $this->adDate->copy();
    }

    public function toAdDate(): Carbon
    {
        return $this->toAd();
    }

    public function toAdString(): string
    {
        return $this->adDate->toDateString();
    }

    public function toBsString(): string
    {
        return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
    }

    public function fiscalYear(): string
    {
        return FiscalYear::fromBsDate($this);
    }

    public function fiscalQuarter(): int
    {
        return FiscalYear::quarterFromMonth($this->month);
    }

    public function daysInMonth(): int
    {
        return Converter::daysInBsMonth($this->year, $this->month);
    }

    public function isLeapYear(): bool
    {
        // Bikram Sambat uses astronomical months without a 4-year leap day rule, but checks 365 vs 366 days
        $totalDays = 0;
        for ($m = 1; $m <= 12; $m++) {
            $totalDays += Converter::daysInBsMonth($this->year, $m);
        }
        return $totalDays >= 366;
    }

    public function format(string $format, bool $inDevanagari = false): string
    {
        $replacements = [
            'Y' => sprintf('%04d', $this->year),
            'y' => sprintf('%02d', $this->year % 100),
            'm' => sprintf('%02d', $this->month),
            'n' => (string) $this->month,
            'd' => sprintf('%02d', $this->day),
            'j' => (string) $this->day,
            'F' => $this->monthName,
            'M' => substr($this->monthName, 0, 3),
            'l' => $this->adDate->format('l'), // Day of week is identical in AD & BS
            'D' => $this->adDate->format('D'),
        ];

        $output = strtr($format, $replacements);

        if ($inDevanagari) {
            $output = str_replace(
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                self::NEPALI_DIGITS,
                $output
            );
        }

        return $output;
    }

    public function addDays(int $days): self
    {
        $targetAd = $this->adDate->copy()->addDays($days);
        return self::fromAd($targetAd);
    }

    public function subDays(int $days): self
    {
        return $this->addDays(-$days);
    }

    public function isBefore(self|string $date): bool
    {
        $other = ($date instanceof self) ? $date : self::parse($date);
        return $this->adDate->lt($other->toAd());
    }

    public function isAfter(self|string $date): bool
    {
        $other = ($date instanceof self) ? $date : self::parse($date);
        return $this->adDate->gt($other->toAd());
    }

    public function isSameDay(self|string $date): bool
    {
        $other = ($date instanceof self) ? $date : self::parse($date);
        return $this->adDate->isSameDay($other->toAd());
    }

    public function toFullArray(): array
    {
        return [
            'bs_year' => $this->year,
            'bs_month' => $this->month,
            'bs_day' => $this->day,
            'bs_date' => $this->toBsString(),
            'month_name' => $this->monthName,
            'fiscal_year' => $this->fiscalYear(),
            'ad_date' => $this->toAdString(),
        ];
    }

    public function toArray(): mixed
    {
        $mode = config('bikram-sambat.serialization', 'dual');

        return match ($mode) {
            'bs' => $this->toBsString(),
            'ad' => $this->toAdString(),
            'object' => $this->toFullArray(),
            default => [
                'ad' => $this->toAdString(),
                'bs' => $this->toBsString(),
            ],
        };
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function __toString(): string
    {
        return $this->toBsString();
    }
}
