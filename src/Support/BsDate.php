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

    public const NEPALI_DAY_NAMES_SHORT = [
        0 => 'आइत', 1 => 'सोम', 2 => 'मङ्गल', 3 => 'बुध',
        4 => 'बिही', 5 => 'शुक्र', 6 => 'शनि',
    ];

    public const NEPALI_MONTH_NAMES = [
        1 => 'बैशाख', 2 => 'जेठ', 3 => 'असार', 4 => 'श्रावण',
        5 => 'भाद्र', 6 => 'असोज', 7 => 'कार्तिक', 8 => 'मंसिर',
        9 => 'पौष', 10 => 'माघ', 11 => 'फागुन', 12 => 'चैत',
    ];

    public const NEPALI_MONTH_NAMES_SHORT = [
        1 => 'बै', 2 => 'जे', 3 => 'अ', 4 => 'श्रा',
        5 => 'भा', 6 => 'असो', 7 => 'का', 8 => 'मं',
        9 => 'पौ', 10 => 'मा', 11 => 'फा', 12 => 'चै',
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

    public function format(string $format, bool $inDevanagari = false, ?string $locale = null): string
    {
        $isNepali = $locale === 'np' || $inDevanagari;
        $dow = $this->adDate->dayOfWeek;

        $replacements = [
            'Y' => sprintf('%04d', $this->year),
            'y' => sprintf('%02d', $this->year % 100),
            'm' => sprintf('%02d', $this->month),
            'n' => (string) $this->month,
            'd' => sprintf('%02d', $this->day),
            'j' => (string) $this->day,
            'F' => $isNepali ? self::NEPALI_MONTH_NAMES[$this->month] : $this->monthName,
            'M' => $isNepali ? self::NEPALI_MONTH_NAMES_SHORT[$this->month] : substr($this->monthName, 0, 3),
            'l' => $isNepali ? self::NEPALI_DAY_NAMES[$dow] : $this->adDate->format('l'),
            'D' => $isNepali ? self::NEPALI_DAY_NAMES_SHORT[$dow] : $this->adDate->format('D'),
            'w' => (string) $dow,
            'N' => (string) ($dow === 0 ? 7 : $dow),
            'S' => $this->englishOrdinal($this->day),
            't' => (string) $this->daysInMonth(),
            'L' => $this->isLeapYear() ? '1' : '0',
            'Q' => (string) $this->fiscalQuarter(),
            'x' => $this->fiscalYear(),
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

    protected function englishOrdinal(int $day): string
    {
        if (in_array($day % 100, [11, 12, 13], true)) {
            return 'th';
        }

        return match ($day % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
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

    public function addBsDays(int $days): self
    {
        return $this->addDays($days);
    }

    public function subBsDays(int $days): self
    {
        return $this->subDays($days);
    }

    public function addBsMonths(int $months): self
    {
        if ($months === 0) {
            return new self($this->year, $this->month, $this->day);
        }

        $totalMonths = ($this->year * 12) + ($this->month - 1) + $months;
        $targetYear = intdiv($totalMonths, 12);
        $targetMonth = ($totalMonths % 12) + 1;

        $maxDays = Converter::daysInBsMonth($targetYear, $targetMonth);
        $targetDay = min($this->day, $maxDays);

        return new self($targetYear, $targetMonth, $targetDay);
    }

    public function subBsMonths(int $months): self
    {
        return $this->addBsMonths(-$months);
    }

    public function addMonths(int $months): self
    {
        return $this->addBsMonths($months);
    }

    public function subMonths(int $months): self
    {
        return $this->subBsMonths($months);
    }

    public function addBsYears(int $years): self
    {
        if ($years === 0) {
            return new self($this->year, $this->month, $this->day);
        }

        $targetYear = $this->year + $years;
        $maxDays = Converter::daysInBsMonth($targetYear, $this->month);
        $targetDay = min($this->day, $maxDays);

        return new self($targetYear, $this->month, $targetDay);
    }

    public function subBsYears(int $years): self
    {
        return $this->addBsYears(-$years);
    }

    public function addYears(int $years): self
    {
        return $this->addBsYears($years);
    }

    public function subYears(int $years): self
    {
        return $this->subBsYears($years);
    }

    public function startOfBsMonth(): self
    {
        return new self($this->year, $this->month, 1);
    }

    public function endOfBsMonth(): self
    {
        return new self($this->year, $this->month, $this->daysInMonth());
    }

    public function startOfBsYear(): self
    {
        return new self($this->year, 1, 1);
    }

    public function endOfBsYear(): self
    {
        return new self($this->year, 12, Converter::daysInBsMonth($this->year, 12));
    }

    public function startOfBsFiscalYear(): self
    {
        $startYear = $this->month >= 4 ? $this->year : $this->year - 1;

        return new self($startYear, 4, 1);
    }

    public function endOfBsFiscalYear(): self
    {
        $endYear = $this->month >= 4 ? $this->year + 1 : $this->year;

        return new self($endYear, 3, Converter::daysInBsMonth($endYear, 3));
    }

    public function startOfBsQuarter(): self
    {
        $quarter = $this->fiscalQuarter();
        $startMonth = match ($quarter) {
            1 => 4,
            2 => 7,
            3 => 10,
            default => 1,
        };

        return new self($this->year, $startMonth, 1);
    }

    public function endOfBsQuarter(): self
    {
        $quarter = $this->fiscalQuarter();
        $endMonth = match ($quarter) {
            1 => 6,
            2 => 9,
            3 => 12,
            default => 3,
        };

        return new self($this->year, $endMonth, Converter::daysInBsMonth($this->year, $endMonth));
    }

    public function isToday(): bool
    {
        return $this->adDate->isToday();
    }

    public function isYesterday(): bool
    {
        return $this->adDate->isYesterday();
    }

    public function isTomorrow(): bool
    {
        return $this->adDate->isTomorrow();
    }

    public function isFuture(): bool
    {
        return $this->adDate->isFuture();
    }

    public function isPast(): bool
    {
        return $this->adDate->isPast();
    }

    public function isCurrentMonth(): bool
    {
        $now = self::now();

        return $this->year === $now->year && $this->month === $now->month;
    }

    public function isCurrentYear(): bool
    {
        return $this->year === self::now()->year;
    }

    public function isSameMonth(self|string $date): bool
    {
        $other = ($date instanceof self) ? $date : self::parse($date);

        return $this->year === $other->year && $this->month === $other->month;
    }

    public function isSameYear(self|string $date): bool
    {
        $other = ($date instanceof self) ? $date : self::parse($date);

        return $this->year === $other->year;
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

    public function diffForHumans(self|DateTimeInterface|string|null $other = null, ?string $locale = null, bool $absolute = false): string
    {
        $targetAd = match (true) {
            $other === null => Carbon::now(),
            $other instanceof self => $other->toAd(),
            $other instanceof DateTimeInterface => ($other instanceof Carbon) ? $other : Carbon::instance($other),
            default => self::parse($other)->toAd(),
        };

        $locale = $locale ?? (function_exists('app') && app()->has('translator') && app()->getLocale() === 'np' ? 'np' : 'en');
        $isNepali = $locale === 'np';

        $diffInSeconds = (int) $targetAd->diffInSeconds($this->adDate, false);
        $isPast = $diffInSeconds <= 0;
        $absSeconds = abs($diffInSeconds);

        if ($absSeconds < 45) {
            return $isNepali ? 'भर्खरै' : 'just now';
        }

        $intervals = [
            'year' => 31536000,
            'month' => 2592000,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1,
        ];

        $unit = 'second';
        $count = $absSeconds;

        foreach ($intervals as $name => $seconds) {
            if ($absSeconds >= $seconds) {
                $unit = $name;
                $count = (int) round($absSeconds / $seconds);
                break;
            }
        }

        if ($isNepali) {
            $key = $absolute ? "{$unit}s" : ($isPast ? "{$unit}s_ago" : "in_{$unit}s");
            $nepaliCount = str_replace(
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                self::NEPALI_DIGITS,
                (string) $count
            );

            $templates = [
                'seconds_ago' => ':count सेकेन्ड अगाडि',
                'minutes_ago' => ':count मिनेट अगाडि',
                'hours_ago' => ':count घण्टा अगाडि',
                'days_ago' => ':count दिन अगाडि',
                'weeks_ago' => ':count हप्ता अगाडि',
                'months_ago' => ':count महिना अगाडि',
                'years_ago' => ':count वर्ष अगाडि',
                'in_seconds' => ':count सेकेन्ड पछि',
                'in_minutes' => ':count मिनेट पछि',
                'in_hours' => ':count घण्टा पछि',
                'in_days' => ':count दिन पछि',
                'in_weeks' => ':count हप्ता पछि',
                'in_months' => ':count महिना पछि',
                'in_years' => ':count वर्ष पछि',
                'seconds' => ':count सेकेन्ड',
                'minutes' => ':count मिनेट',
                'hours' => ':count घण्टा',
                'days' => ':count दिन',
                'weeks' => ':count हप्ता',
                'months' => ':count महिना',
                'years' => ':count वर्ष',
            ];

            $template = (function_exists('trans') && ($t = trans("bs-eloquent::message.diff_for_humans.{$key}", [], 'np')) !== "bs-eloquent::message.diff_for_humans.{$key}")
                ? $t
                : $templates[$key];

            return str_replace(':count', $nepaliCount, $template);
        }

        $key = $absolute ? "{$unit}s" : ($isPast ? ($count === 1 ? "{$unit}_ago" : "{$unit}s_ago") : ($count === 1 ? "in_{$unit}" : "in_{$unit}s"));

        $templates = [
            'second_ago' => '1 second ago',
            'seconds_ago' => ':count seconds ago',
            'minute_ago' => '1 minute ago',
            'minutes_ago' => ':count minutes ago',
            'hour_ago' => '1 hour ago',
            'hours_ago' => ':count hours ago',
            'day_ago' => '1 day ago',
            'days_ago' => ':count days ago',
            'week_ago' => '1 week ago',
            'weeks_ago' => ':count weeks ago',
            'month_ago' => '1 month ago',
            'months_ago' => ':count months ago',
            'year_ago' => '1 year ago',
            'years_ago' => ':count years ago',
            'in_second' => 'in 1 second',
            'in_seconds' => 'in :count seconds',
            'in_minute' => 'in 1 minute',
            'in_minutes' => 'in :count minutes',
            'in_hour' => 'in 1 hour',
            'in_hours' => 'in :count hours',
            'in_day' => 'in 1 day',
            'in_days' => 'in :count days',
            'in_week' => 'in 1 week',
            'in_weeks' => 'in :count weeks',
            'in_month' => 'in 1 month',
            'in_months' => 'in :count months',
            'in_year' => 'in 1 year',
            'in_years' => 'in :count years',
            'seconds' => ':count seconds',
            'minutes' => ':count minutes',
            'hours' => ':count hours',
            'days' => ':count days',
            'weeks' => ':count weeks',
            'months' => ':count months',
            'years' => ':count years',
        ];

        $template = (function_exists('trans') && ($t = trans("bs-eloquent::message.diff_for_humans.{$key}", [], 'en')) !== "bs-eloquent::message.diff_for_humans.{$key}")
            ? $t
            : $templates[$key];

        return str_replace(':count', (string) $count, $template);
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
