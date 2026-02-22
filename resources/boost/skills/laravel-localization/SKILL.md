---
name: laravel-localization-suite
description: Build and work with Laravel Localization Suite features, including isolated locales, localized routing, and redirects.
---

# Laravel Localization Suite

## When to use this skill
Use this skill when working with a Laravel application that has `josemontano1996/laravel-localization-suite` installed to build multilingual features, generate routes, handle redirects, or format data according to a user's locale.

## Features & Usage

### 1. Choosing the Correct Driver
Before interacting with the localization service, ensure the project is using the correct driver in `config/localization.php` for its environment:
- **`native`**: Standard PHP-FPM environments.
- **`context`**: General Laravel 11+ environments.
- **`swoole` / `openswoole`**: **Truly concurrency safe**. Essential for Laravel Octane environments using Swoole or OpenSwoole where concurrent requests are handled by the same worker (e.g., when hooks like `HOOKS_ALL` are enabled). They use Coroutine Contexts to prevent state bleed.

### 2. Locale Detection and Setting

Determine the best locale based on browser headers and supported languages:

```php
$supported = ['en', 'es', 'fr'];
$bestMatch = request()->preferredLocale($supported);

localization()->setCurrentLocale($bestMatch);
```

### 3. Localized Routing

Define routes that automatically receive a locale prefix and handle validation + content negotiation via middleware:

```php
use Illuminate\Support\Facades\Route;

Route::localized()
    ->middleware('localization')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
```

To generate links to these routes, maintaining the active locale context:

```php
// In PHP
$url = localization()->route('dashboard');
$url = URL::localeRoute('dashboard');

// In Blade
<a href="@route('dashboard')">Dashboard</a>
```

### 4. Creating a Language Switcher

To generate a URL for the current page but in a different language:

```php
// In PHP
$spanishUrl = URL::withLocale('es');

// In Blade
@locales($code)
    <a href="{{ URL::withLocale($code) }}">{{ strtoupper($code) }}</a>
@endlocales
```

### 5. Locale-Aware Redirects

Whenever redirecting the user, use the `localized()` macro on the redirector to ensure the locale parameter is automatically appended to the redirect destination:

```php
// Redirect to named route
return redirect()->localized()->route('dashboard');

// Redirect back
return redirect()->localized()->back();

// Redirect to intended (after login)
return redirect()->localized()->intended('/dashboard');
```

### 6. Blade Formatting

Avoid raw PHP calls for locale-aware formatting. Use the powerful Blade directives instead:

```blade
{{-- Translations --}}
<h1>@t('messages.welcome')</h1>
<p>@tchoice('messages.items', 2)</p>

{{-- Numbers & Currency (auto-adapts to locale formats like 1,234.56 vs 1.234,56) --}}
@number(1234.56)
@currency(99.99, 'EUR')
@percent(0.15)

{{-- Dates (auto-adapts to locale strings like "January 15" vs "15 de enero") --}}
@date($post->created_at)
@datetime($post->created_at)
```
