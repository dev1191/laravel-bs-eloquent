# First-class Bikram Sambat (Nepali Date) Eloquent Casts, Scopes, and Validation for Laravel 10 - 13

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dev1191/laravel-bs-eloquent.svg?style=flat-square)](https://packagist.org/packages/dev1191/laravel-bs-eloquent)
[![GitHub Tests Action Status](https://github.com/dev1191/laravel-bs-eloquent/actions/workflows/run-tests.yml/badge.svg)](https://github.com/dev1191/laravel-bs-eloquent/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dev1191/laravel-bs-eloquent.svg?style=flat-square)](https://packagist.org/packages/dev1191/laravel-bs-eloquent)

A modern, high-performance Laravel package for **Bikram Sambat (BS / Nepali Date)**. It stores standard Gregorian (AD) dates in your database to preserve SQL index performance, while giving you transparent Eloquent casts, scopes, Nepali fiscal year calculations, and a full suite of FormRequest validation rules.

---

## ✨ Key Features

- **Automatic Eloquent Date Casting (`AsBikramSambat`)**: Write BS dates directly (`$model->published_at = '2081-01-01'`) and let the cast automatically convert and store them as Gregorian (`'2024-04-14'`). When retrieved, you get a rich `BsDate` object.
- **Configurable API / JSON Serialization**: Serialize dates as `dual` (`{"ad": "2024-04-14", "bs": "2081-01-01"}`), plain `bs` (`"2081-01-01"`), `ad`, or full object details.
- **Index-Preserving Eloquent Scopes (`HasBikramSambatScopes`)**:
  - `whereBs('column', '2081-01-01')`
  - `whereBsMonth('column', 2081, 1)`
  - `whereBsYear('column', 2081)`
  - `whereBsBetween('column', '2081-01-01', '2081-01-31')`
  - `whereBsFiscalYear('column', '2080/81')`
  - `whereBsQuarter('column', 2081, 1)`
- **Built-in Nepal Fiscal Year (*Aarthik Barsha*)**: Full support for fiscal years (Shrawan 1 to Ashadh 31/32) and quarterly divisions (Q1-Q4).
- **Fluent Date Arithmetic & Period Boundaries**: Easily manipulate dates (`addBsDays`, `subBsMonths`, `startOfBsFiscalYear`, `endOfBsMonth`).
- **Carbon Status Checks**: Built-in boolean state and comparison methods (`isToday()`, `isYesterday()`, `isTomorrow()`, `isPast()`, `isFuture()`, `isCurrentMonth()`, `isCurrentYear()`, `isSameMonth()`, `isSameYear()`).
- **Relative Time Localization (`diffForHumans`)**: Natural Nepali and English relative timestamps (e.g., *“३ दिन अगाडि”*, *“२ महिना पछि”*, or *“3 days ago”*).
- **Extended Formatting Tokens**: Full token mapping mimicking PHP's `date()` syntax (e.g. `l`, `D`, `F`, `M`, `t`, `S`, `Q`, `x`) with Devanagari numerals.
- **Validation Rules**: Complete suite of Laravel validation rules: `bs_date`, `bs_after`, `bs_before`, `bs_fiscal_year`.
- **Artisan CLI Conversion Tool (`php artisan bs:convert`)**: Instant terminal conversion for any BS or AD date directly from the command line, showing Gregorian equivalent, fiscal year, fiscal quarter, and Devanagari output.
- **Pure Astronomical Formula Engine**: Zero hardcoded calendar arrays or third-party packages. Calculates dates directly from planetary motion, Lahiri Ayanamsa, and solar Sankranti ingress moments.
- **Devanagari Numerals & Nepali Month Names**: Convert and format seamlessly in Nepali (e.g. `२०८१-०१-०१`, `बैशाख`).

---

## 📦 Installation

Install the package via Composer:

```bash
composer require dev1191/laravel-bs-eloquent
```

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag="laravel-bs-eloquent-config"
```

The published `config/bikram-sambat.php`:

```php
return [
    /*
     * Serialization format for API / JSON:
     * - 'dual':   ['ad' => '2024-04-14', 'bs' => '2081-01-01']
     * - 'bs':     '2081-01-01'
     * - 'ad':     '2024-04-14'
     * - 'object': ['bs_year' => 2081, 'bs_month' => 1, 'fiscal_year' => '2080/81', ...]
     */
    'serialization' => env('BS_DATE_SERIALIZATION', 'dual'),

    'default_format' => 'Y-m-d',
    'devanagari' => false,
];
```

---

## 🚀 Usage

### 1. Eloquent Model Casting

Add the cast to your model:

```php
use Illuminate\Database\Eloquent\Model;
use Dev1191\BikramSambat\Casts\AsBikramSambat;
use Dev1191\BikramSambat\Concerns\HasBikramSambatScopes;

class Post extends Model
{
    use HasBikramSambatScopes;

    protected $casts = [
        'published_at' => AsBikramSambat::class,
    ];
}
```

#### Writing BS Dates:
```php
// You can assign a BS string, DateTime, Carbon, or BsDate instance:
$post = Post::create([
    'title' => 'Sample Post',
    'published_at' => '2081-01-01',
]);

// In the database table, it's stored as Gregorian '2024-04-14' (preserves indexes!)
```

#### Reading BS Dates:
```php
$post->published_at->toBsString(); // "2081-01-01"
$post->published_at->toAdString(); // "2024-04-14"
$post->published_at->getMonthName(); // "Baisakh"
$post->published_at->getMonthName(nepali: true); // "बैशाख"
$post->published_at->fiscalYear(); // "2080/81"
$post->published_at->fiscalQuarter(); // 4
$post->published_at->format('Y-m-d', inDevanagari: true); // "२०८१-०१-०१"
```

---

### 2. Eloquent Query Scopes

All queries automatically convert BS boundaries to Gregorian dates so your SQL index remains fast and untouched:

```php
// Exact date match
Post::whereBs('published_at', '2081-01-01')->get();

// All posts in Baisakh 2081
Post::whereBsMonth('published_at', 2081, 1)->get();

// All posts in year 2081 BS
Post::whereBsYear('published_at', 2081)->get();

// Date range
Post::whereBsBetween('published_at', '2081-01-01', '2081-01-15')->get();

// Fiscal Year (Aarthik Barsha)
Post::whereBsFiscalYear('published_at', '2081/82')->get();

// Fiscal Quarter (Q1: Shrawan-Ashwin, Q2: Kartik-Poush, Q3: Magh-Chaitra, Q4: Baisakh-Ashadh)
Post::whereBsQuarter('published_at', 2081, 1)->get();
```

---

### 3. FormRequest Validation Rules

Use custom validation rules or standard string rule aliases:

```php
use Illuminate\Foundation\Http\FormRequest;
use Dev1191\BikramSambat\Rules\BsDate;
use Dev1191\BikramSambat\Rules\BsAfter;
use Dev1191\BikramSambat\Rules\BsBefore;
use Dev1191\BikramSambat\Rules\BsFiscalYear;

class StoreInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Using string rules:
            'invoice_date' => ['required', 'bs_date', 'bs_after:2080-01-01', 'bs_before:2082-01-01'],
            'fiscal_year'  => ['required', 'bs_fiscal_year'],

            // Or using rule classes:
            'payment_date' => ['nullable', new BsDate()],
        ];
    }
}
```

---

### 4. Artisan CLI Conversion

```bash
php artisan bs:convert 2081-01-01
```

Output:
```text
Bikram Sambat (BS): 2081-01-01 (Baisakh)
Gregorian (AD):     2024-04-14
Fiscal Year:        2080/81 (Q4)
Devanagari:         २०८१-०१-०१ (बैशाख)
```

---

### 5. Date Manipulation & Carbon Parity (`BsDate`)

The `BsDate` object provides Carbon-like fluent operations designed specifically for Bikram Sambat calendar rules:

#### Fluent Date Arithmetic:
```php
use Dev1191\BikramSambat\Support\BsDate;

$date = BsDate::parse('2081-01-15');

// Days
$date->addBsDays(5);    // 2081-01-20
$date->subBsDays(2);    // 2081-01-13

// Months (with automatic month-length day clamping)
$date->addBsMonths(2);  // 2081-03-15
$date->subBsMonths(1);  // 2080-12-15

// Years
$date->addBsYears(1);   // 2082-01-15
$date->subBsYears(2);   // 2079-01-15
```

#### Period Boundaries:
```php
$date = BsDate::parse('2081-04-15');

// Month Boundaries
$date->startOfBsMonth();      // 2081-04-01
$date->endOfBsMonth();        // 2081-04-31 (or 32 depending on astronomical calendar)

// Year Boundaries
$date->startOfBsYear();       // 2081-01-01
$date->endOfBsYear();         // 2081-12-30

// Nepal Fiscal Year Boundaries (Shrawan 1 to Ashadh end)
$date->startOfBsFiscalYear(); // 2081-04-01
$date->endOfBsFiscalYear();   // 2082-03-31

// Fiscal Quarter Boundaries
$date->startOfBsQuarter();    // 2081-04-01 (Q1 start)
$date->endOfBsQuarter();      // 2081-06-31 (Q1 end)
```

#### Relative Time Localization (`diffForHumans`):
```php
$date = BsDate::today()->subDays(3);

// In Nepali (natural language with Devanagari digits)
$date->diffForHumans(locale: 'np'); // "३ दिन अगाडि"

// In English
$date->diffForHumans(locale: 'en'); // "3 days ago"

$future = BsDate::today()->addBsMonths(2);
$future->diffForHumans(locale: 'np'); // "२ महिना पछि"
$future->diffForHumans(locale: 'en'); // "in 2 months"

// Just now
BsDate::now()->diffForHumans(locale: 'np'); // "भर्खरै"
BsDate::now()->diffForHumans(locale: 'en'); // "just now"
```

#### Extended Format Tokens:
`$date->format($pattern, inDevanagari: false, locale: null)` supports standard PHP `date()` specifiers adapted to BS:

| Token | Description | Example (BS) |
|---|---|---|
| `d` | 2-digit day of month with leading zeros | `01` to `32` |
| `j` | Day of month without leading zeros | `1` to `32` |
| `l` | Full day of the week | `Sunday` / `आइतबार` |
| `D` | 3-letter / short day of the week | `Sun` / `आइत` |
| `w` | Numeric day of week (0 for Sunday, 6 for Saturday) | `0` |
| `N` | ISO-8601 numeric day of week (1 for Monday, 7 for Sunday) | `7` |
| `S` | English ordinal suffix for the day | `st`, `nd`, `rd`, `th` |
| `m` | 2-digit month with leading zeros | `01` to `12` |
| `n` | Month without leading zeros | `1` to `12` |
| `F` | Full month name | `Baisakh` / `बैशाख` |
| `M` | Short month name | `Bai` / `बै` |
| `t` | Number of days in the given BS month | `31` |
| `Y` | 4-digit BS year | `2081` |
| `y` | 2-digit BS year | `81` |
| `L` | Whether it is a leap year (366 days) | `1` or `0` |
| `Q` | Fiscal quarter (1 to 4) | `4` |
| `x` | Fiscal year string | `2080/81` |

```php
$date = BsDate::create(2081, 1, 1);

$date->format('l, F j, Y');                        // "Sunday, Baisakh 1, 2081"
$date->format('l, F j, Y', inDevanagari: true);    // "आइतबार, बैशाख १, २०८१"
$date->format('Y/m/d (x Q)');                      // "2081/01/01 (2080/81 4)"
```

#### Carbon Status Checks:
```php
$date->isToday();
$date->isYesterday();
$date->isTomorrow();
$date->isPast();
$date->isFuture();
$date->isCurrentMonth();
$date->isCurrentYear();
$date->isSameMonth('2081-01-30');
$date->isSameYear('2081-08-10');
```

---

### 5. Artisan CLI Conversion Command

Quickly inspect and convert dates directly from the command line without opening a tinker session:

```bash
# Convert a Bikram Sambat date to Gregorian (AD) with full metadata
php artisan bs:convert 2081-01-01

# Convert a Gregorian (AD) date to Bikram Sambat
php artisan bs:convert 2024-04-14 --from=ad
```

**Sample Output:**
```text
Bikram Sambat (BS): 2081-01-01 (Baisakh)
Gregorian (AD):     2024-04-14
Day:                Sunday (आइतबार)
Fiscal Year:        2080/81 (Q4)
Devanagari:         २०८१-०१-०१ (बैशाख)
```

---

## 🧪 Testing

Run tests with Pest:

```bash
composer test
```

---

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines on the Git workflow, branching strategy, testing requirements, and commit conventions.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
