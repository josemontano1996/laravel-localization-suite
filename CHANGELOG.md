# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-02-22

### Added

- **Driver Architecture**: Isolated localization state support via `native`, `context`, `swoole`, and `openswoole` drivers.
- **Language Detection**: Smart locale detection from URL segments and `Accept-Language` headers.
- **Localized Routing**: `Route::localized()` macro for automatic locale prefixing and regex constraints.
- **Localized Redirects**: `redirect()->localized()` macro to maintain locale context during redirects.
- **Blade Directives**: Comprehensive set of directives for translations (`@t`, `@tchoice`), routing (`@route`), and locale-aware formatting (`@number`, `@currency`, `@percent`, `@date`, `@datetime`).
- **Request & URL Macros**: Helpful extensions like `request()->preferredLocale()`, `URL::localeRoute()`, and `URL::withLocale()`.
- **Octane & Swoole Support**: First-class support for high-concurrency environments with complete state isolation.
- **Laravel Boost Support**: Built-in AI guidelines and skills for better assistant integration.
- **CI/CD**: GitHub Actions for unit tests (PHP 8.4+) and multi-runtime integration testing (Native, Context, Swoole, OpenSwoole).
