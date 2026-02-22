## Laravel Localization Suite

This package provides driver-based, concurrency-safe localization for Laravel, including support for Octane, Swoole, and OpenSwoole.

### Core Concepts

**Choosing the Right Driver:**
- **`native` driver:** Use for standard Laravel (FPM/mod_php). Mutates global state (`app()->setLocale()`).
- **`context` driver:** Use for Laravel 11+ Context. Safe for standard Octane, but **not** truly concurrency safe (cannot handle multiple requests on the same worker simultaneously).
- **`swoole` and `openswoole` drivers:** Use for Octane with Swoole/OpenSwoole. **These are truly concurrency safe**. They store locale in the Coroutine Context, providing 100% isolation. This means you can safely enable `HOOKS_ALL` (or other Swoole async hooks) for true per-worker concurrency without cross-request state bleed.
- **Service & Auto-Discovery:** `LocalizationServiceProvider` and `Localization` Facade are automatically registered via Laravel Package Auto-Discovery. The `LocalizationServiceContract` handles current locale, route generation, translations, and number format. Available via `localization()` helper or `Localization` facade.

### Key Features / Example Usage

**1. Getting or Setting the Locale:**

@verbatim
<code-snippet name="Get or Set Locale" lang="php">
// Service
$locale = localization()->getCurrentLocale();
localization()->setCurrentLocale('es');

// Request Macro
$locale = request()->locale();
$accepted = request()->acceptedLocales(); // Array from Accept-Language
$bestMatch = request()->preferredLocale(['en', 'es']); // Matches Accept-Language to supported list
</code-snippet>
@endverbatim

**2. Localized Routing (Important!)**

Always use the `Route::localized()` macro and `localization` middleware for multilingual paths.

@verbatim
<code-snippet name="Defining Routes" lang="php">
Route::localized()
    ->middleware('localization')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Generating URLs" lang="php">
// Generates URL maintaining the current locale (e.g., /es/dashboard)
$url = localization()->route('dashboard');

// Same, but using URL macro
$url = URL::localeRoute('dashboard');

// Generate URL for a DIFFERENT locale (e.g. for language switchers)
$spanishUrl = URL::withLocale('es');
</code-snippet>
@endverbatim

**3. Redirects**

Always use the `localized()` redirect macro to maintain the user's current locale across responses.

@verbatim
<code-snippet name="Redirects" lang="php">
return redirect()->localized()->route('dashboard');
return redirect()->localized()->back();
return redirect()->localized()->to('/profile');
return redirect()->localized()->intended('/dashboard');
</code-snippet>
@endverbatim

**4. Blade Directives**

Use built-in directives instead of raw PHP for common localization tasks.

@verbatim
<code-snippet name="Blade Usage" lang="html">
<html lang="@locale">
<a href="@route('dashboard')">Dashboard</a>

<!-- Translations -->
<p>@t('messages.welcome')</p>
<p>@tchoice('messages.items', 5)</p>

<!-- Formatting -->
<span>@number(1234.56)</span>
<span>@currency(99.99, 'USD')</span>
<span>@date($user->created_at)</span>
</code-snippet>
@endverbatim

### When to use this package's features:
- If asked to generate a route link in a localized app, use `@route()` in blade, or `localization()->route()` in PHP.
- If asked to redirect, use `redirect()->localized()->...`.
- If asked to determine the user's language preference, use `request()->preferredLocale()`.
