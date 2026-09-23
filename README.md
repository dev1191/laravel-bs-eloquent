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
- **Validation Rules**: Complete suite of Laravel validation rules: `bs_date`, `bs_after`, `bs_before`, `bs_fiscal_year`.
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

## 🧪 Testing

Run tests with Pest:

```bash
composer test
```

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
