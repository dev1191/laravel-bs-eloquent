# Git Workflow & Package Update Guide

This document defines the standard Git process, quality gates, and commit conventions for `dev1191/laravel-bs-eloquent`. Both human contributors and AI assistants (like Gemini) must follow these procedures when adding new features, fixing bugs, or updating dependencies.

---

## 📋 Quick Reference Workflow Checklist

Whenever adding a feature or fixing a bug, execute these steps in order:

- [ ] **1. Create Branch**: Never develop directly on dirty `main`. Use `feature/*`, `fix/*`, or `chore/*`.
- [ ] **2. Write Tests**: Add or update Pest test cases under `tests/Unit/` or `tests/Feature/`.
- [ ] **3. Implement Code**: Keep code clean, standard Laravel conventions, zero third-party date dependencies.
- [ ] **4. Run Code Formatter**: `composer format` (Laravel Pint).
- [ ] **5. Run Static Analysis**: `composer analyse` (PHPStan).
- [ ] **6. Run Test Suite**: `composer test` (Pest). Ensure 100% pass.
- [ ] **7. Update Docs**: If public API, config, or scope changes, update `README.md`.
- [ ] **8. Commit**: Use Conventional Commits (`feat: ...`, `fix: ...`, `docs: ...`).
- [ ] **9. Push & Tag**: Merge to `main` and tag according to Semantic Versioning (`vX.Y.Z`).

---

## 🌿 1. Branching Strategy

| Branch Prefix | Purpose | Example |
| :--- | :--- | :--- |
| `feature/` | New functionality, scopes, rules, or converters | `feature/date-range-macros` |
| `fix/` | Bug fixes, timezone edge cases, date calculation corrections | `fix/leap-year-month-length` |
| `docs/` | Documentation improvements, README updates | `docs/add-carbon-comparison-guide` |
| `chore/` | CI/CD changes, composer package bumps, repo maintenance | `chore/update-pest-v4` |
| `main` | Production-ready, stable codebase | Used for releases and tags |

### Commands:
```bash
# Start a new feature
git checkout main
git pull origin main
git checkout -b feature/<feature-name>

# Start a bug fix
git checkout main
git pull origin main
git checkout -b fix/<bug-name>
```

---

## 🧪 2. Mandatory Verification Gates (Run Before Commit)

Before any commit is made, **all three verification tools must pass**:

### 1. Code Formatting (Laravel Pint)
```bash
vendor/bin/pint
# Or via composer:
composer format
```

### 2. Static Analysis (PHPStan / Larastan)
```bash
vendor/bin/phpstan analyse --memory-limit=1G
# Or via composer:
composer analyse
```
*Rule: 0 errors allowed. If a false positive occurs on Larastan framework internals, add an entry to `phpstan.neon.dist` under `ignoreErrors`.*

### 3. Test Suite (Pest PHP)
```bash
vendor/bin/pest
# Or via composer:
composer test
```
*Rule: All assertions must pass. Never disable tests.*

---

## 📝 3. Commit Message Standards (Conventional Commits)

Commit messages must follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```text
<type>(<optional scope>): <short summary in imperative present tense>

[optional body explaining rationale and background]

[optional footer, e.g., Fixes #123]
```

### Supported Types:
* `feat:` A new feature or public API addition (triggers a MINOR version bump).
* `fix:` A bug fix in existing code (triggers a PATCH version bump).
* `test:` Adding missing tests or correcting existing tests.
* `docs:` Documentation changes only (e.g. `README.md`, docblocks).
* `style:` Code style changes that do not affect code logic (Pint formatting).
* `refactor:` Code changes that neither fix a bug nor add a feature.
* `perf:` Performance improvements.
* `chore:` Build scripts, GitHub Actions, composer updates.

### Examples:
* `feat(cast): add support for strict type casting on BsDate`
* `fix(converter): correct month days calculation for BS year 2085`
* `docs(readme): add query scope usage examples for whereBsFiscalYear`
* `test(scopes): add test coverage for whereBsQuarter edge cases`

---

## 📖 4. Documentation & Changelog Requirements

1. **`README.md`**:
   * If a new Eloquent scope, validation rule, or `BsDate` method is introduced, add clear code examples to `README.md`.
   * Keep markdown tables and code blocks formatted cleanly.
2. **`CHANGELOG.md`**:
   * Whenever a GitHub Release is created, the GitHub Action `.github/workflows/update-changelog.yml` automatically appends release notes.
   * If updating manually, list changes under `## [Unreleased]` or the corresponding version header:
     - `### Added`
     - `### Changed`
     - `### Fixed`

---

## 🚀 5. Release & Tagging Process (Semantic Versioning)

Releases strictly adhere to **SemVer (`v<MAJOR>.<MINOR>.<PATCH>`)**:

* **PATCH (`v1.0.1`)**: Backwards-compatible bug fixes and small internal refactors.
* **MINOR (`v1.1.0`)**: New features, methods, or scopes added without breaking existing APIs.
* **MAJOR (`v2.0.0`)**: Breaking changes (e.g., changing method signatures, dropping older PHP/Laravel versions).

### Releasing a New Version:

#### Option A: Via GitHub Releases (Recommended)
1. Ensure all changes are merged into `main` and CI is green.
2. Go to **GitHub Repository -> Releases -> Draft a new release**.
3. Choose tag: `v1.0.0` (or next version).
4. Target: `main`.
5. Click **Generate release notes**.
6. Click **Publish release**.
*(This triggers automatic changelog generation and syncs with Packagist).*

#### Option B: Via Git CLI
```bash
git checkout main
git pull origin main

# Create and push tag
git tag v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

---

## 🤖 6. Specific Instructions for Gemini & AI Coding Assistants

When Gemini is asked to update code, add a feature, or resolve an issue:

1. **Investigate First**: Check existing implementation patterns in `src/` and tests in `tests/`.
2. **Implement with Minimal Footprint**: Do not introduce unnecessary dependencies. Maintain pure astronomical algorithms and standard Laravel conventions.
3. **Always Run Tests & Pint**:
   - Run `vendor/bin/pest` to verify no regressions.
   - Run `vendor/bin/pint` to ensure code styling matches.
   - Run `vendor/bin/phpstan analyse --memory-limit=1G` to guarantee static analysis passes.
4. **Update `README.md`**: If any new method, scope, or config key was added, immediately update the documentation in `README.md`.
5. **Summarize with Git Commit Proposal**: Provide the exact conventional commit message and git commands for the user.
