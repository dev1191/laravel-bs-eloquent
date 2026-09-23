# Gemini Project Rules & Git Workflow

Whenever Gemini works on this repository (`dev1191/laravel-bs-eloquent`), adhere to the following rules and standards:

## 1. Quality & Verification Gates
Before finishing any task that modifies PHP code:
1. **Formatting**: Run `vendor/bin/pint` to ensure code styling follows package rules.
2. **Static Analysis**: Run `php vendor/bin/phpstan analyse --memory-limit=1G` and ensure 0 errors.
3. **Tests**: Run `vendor/bin/pest` to verify 100% test passage. Never break existing tests.

## 2. Coding Guidelines
- **Zero Third-Party Date Libraries**: All Bikram Sambat conversions must use pure astronomical calculations based on Lahiri Ayanamsa and solar ingress.
- **Index Preservation**: In database columns, dates must always be stored in Gregorian (AD) format (`Y-m-d`). Eloquent casts (`AsBikramSambat`) handle transparent conversion to `BsDate`.
- **Framework Compatibility**: Support Laravel 10, 11, 12, and 13 with PHP 8.2, 8.3, and 8.4.

## 3. Git Commit Conventions
When proposing or making commits, use Conventional Commits:
- `feat: <summary>` for new features or public API additions.
- `fix: <summary>` for bug fixes.
- `test: <summary>` for adding or updating test cases.
- `docs: <summary>` for documentation and README updates.
- `chore: <summary>` for build scripts, dependency updates, and maintenance.

## 4. Documentation
If a new method, scope, or configuration option is introduced, update [README.md](file:///d:/laragon/www/laravel-bs-eloquent/README.md) with clear code snippets.
Refer to [CONTRIBUTING.md](file:///d:/laragon/www/laravel-bs-eloquent/CONTRIBUTING.md) for full branch and release details.
