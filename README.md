# Laravel Localization Suite

A comprehensive, runtime safe, localization package for Laravel 12+ with first-class driver support for **Laravel Octane**, **Swoole** and **OpenSwoole** concurrency hooks.

Localize your laravel app without having to worry about the runtime your app will run on, FPM, Octane, or even Octane with Swoole or OpenSwoole asynchronous hooks, just change the localization driver and you will get a worry free localized app.

[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-red)](https://laravel.com)

## ✨ Features

- **Driver-based architecture** — Swap between Native, Context, Swoole, or OpenSwoole drivers depending on your needs.
- **Octane-ready** — Octane-safe drivers grant locale isolation preventing cross-request bleed
- **Concurrency safe drivers** — Swoole and OpenSwoole drivers are completely concurrency safe, meaning they support multiple concurrent requests per worker and they support all Swoole based HOOKS.
- **Localized routing** — Simple locale-prefixed route groups with automatic detection
- **Middlewares** - Useful middlewares for smart locale detection with metadata integration for best SEO practices
- **Blade directives** — `@t`, `@route`, `@locale`, `@currency`, `@date` and more
- **Macros** — `redirect()->localized()`, `Route::localized()`, `URL::withLocale()`
- **Validation** — Context-aware validation messages without touching global state

---

## 📦 Installation

```bash
composer require josemontano1996/laravel-localization-suite
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=localization-config
```

---

## ⚙️ Configuration

## Drivers

### PHP Version Support Matrix

| Driver | PHP 8.4 | PHP 8.5 | Per Worker Concurrency Safe |
|--------|---------|---------|------------------|
| `native` | ✅ | ✅ | ❌ |
| `context` | ✅ | ✅ | ❌ |
| `swoole` | ✅ | ❌ | ✅ |
| `openswoole` | ✅ | ❌ | ✅ |

> **Note:** Swoole and OpenSwoole do not yet support PHP 8.5. Use `native` or `context` drivers for PHP 8.5 environments.

For a detailed explanation of all available drivers, their concurrency guarantees, and how to implement your own, see the [Drivers Documentation](docs/DRIVERS.md).

### `config/localization.php`

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Localization Driver
    |--------------------------------------------------------------------------
    |
    | Built-in Drivers:
    |  - "native": Standard Laravel behavior.
    |  - "context": Laravel 11+ Context.
    |  - "swoole": Swoole Coroutine Context (concurrency safe).
    |  - "openswoole": OpenSwoole Coroutine Context (concurrency safe).
    |
    | Custom Drivers:
    | You may provide a fully qualified class name implementing:
    | \Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract
    |
    */
    'driver' => env('LOCALIZATION_DRIVER', 'native'),

    // Route parameter name for the locale segment
    'route_key' => 'locale',
];
```

### Required Laravel Configuration

Add these to your `config/app.php`:

```php
'locale' => 'en',
'fallback_locale' => 'en',
'supported_locales' => ['en', 'es', 'fr', 'de'], // Required!
```

> [!IMPORTANT]
> The `supported_locales` array **must** be populated with at least one locale.

---

## 🚀 Quick Start

### 1. Define Localized Routes

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

Route::localized()
    ->middleware('localization')
    ->group(function () {
        Route::get('/', fn () => view('home'))->name('home');
        Route::get('/about', fn () => view('about'))->name('about');
    });
```

This creates routes like:

- `/en/`, `/es/`, `/fr/`
- `/en/about`, `/es/about`, `/fr/about`

### 2. Use in Controllers

```php
use Localization;

class HomeController extends Controller
{
    public function index()
    {
        // Get current locale
        $locale = Localization::getCurrentLocale();

        // Translate with context-aware locale
        $greeting = Localization::t('messages.welcome');

        // Generate localized route URL
        $aboutUrl = Localization::route('about');

        return view('home', compact('greeting', 'aboutUrl'));
    }
}
```

### 3. Use in Blade Templates

```blade
{{-- Current locale --}}
<html lang="@locale">

{{-- Translations --}}
<h1>@t('messages.welcome')</h1>
<p>@t('messages.greeting', ['name' => $user->name])</p>

{{-- Pluralization --}}
<p>@tchoice('messages.items', $count)</p>

{{-- Localized routes --}}
<a href="@route('about')">{{ t('nav.about') }}</a>
<a href="@route('product.show', $product)">{{ $product->name }}</a>

{{-- Formatting --}}
<span>@number(1234.56)</span>          {{-- 1,234.56 or 1.234,56 depending on locale --}}
<span>@currency(99.99, 'EUR')</span>   {{-- €99.99 or 99,99 € --}}
<span>@percent(0.15)</span>            {{-- 15% --}}

{{-- Dates --}}
<time>@date($post->created_at)</time>           {{-- January 15, 2025 --}}
<time>@datetime($post->created_at)</time>       {{-- January 15, 2025 at 3:45 PM --}}
<time>@date($post->created_at, 'MMMM YYYY')</time> {{-- Custom format --}}

{{-- Conditionals --}}
@localeIs('en')
    <p>English-specific content</p>
@elselocaleIs('es')
    <p>Contenido en español</p>
@endlocaleIs

{{-- Language switcher --}}
<nav>
    @locales($lang)
        <a href="{{ URL::withLocale($lang) }}"
           @if($lang === localization()->getCurrentLocale()) class="active" @endif>
            {{ strtoupper($lang) }}
        </a>
    @endlocales
</nav>
```

---

## 🛣️ Routing

### Localized Route Groups

```php
Route::localized()
    ->middleware('localization')
    ->group(function () {
        // All routes here will be prefixed with /{locale}/
        Route::get('/', HomeController::class)->name('home');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    });
```

### Middleware

The package provides a `localization` middleware group that:

1. **Detects locale from URL** — Reads the `{locale}` route parameter
2. **Validates locale** — Redirects to best match if unsupported, falling back to smart request preferred locales.
3. **Sets response headers** — Adds `Content-Language` header

```php
// Individual middleware aliases available:
Route::middleware('localization.from_route'); // Set locale from URL
Route::middleware('localization.headers');     // Add Content-Language header
Route::middleware('localization');             // Both combined
```

### Generate Localized URLs

```php
// Using the service
localization()->route('products.show', ['product' => $product]);

// Using URL macro
URL::localeRoute('products.show', ['product' => $product]);

// Generate URL with a different locale
URL::withLocale('es'); // Same page but in Spanish
```

---

## 🔄 Redirects

The `redirect()->localized()` macro ensures all redirects respect the current locale:

```php
// Redirect to a named route (locale is injected automatically)
return redirect()->localized()->route('dashboard');

// Redirect to a path (locale is prefixed automatically)
return redirect()->localized()->to('/profile');

// Redirect back with locale fallback
return redirect()->localized()->back();

// Redirect to intended URL after authentication
return redirect()->localized()->intended('/dashboard');

// Localized signed routes
return redirect()->localized()->signedRoute('unsubscribe', ['user' => $user]);

// Controller actions
return redirect()->localized()->action([UserController::class, 'show'], ['id' => 1]);

// External URLs (bypasses localization)
return redirect()->localized()->away('https://google.com');
```

---

## 🌐 Request Helpers

```php
// Get current locale
$locale = request()->locale();

// Get browser's accepted locales
$accepted = request()->acceptedLocales(); // ['en' => 1.0, 'es' => 0.8, ...]

// Get best matching locale from supported list
$preferred = request()->preferredLocale(['en', 'es', 'fr']); // 'en'
```

---

## 🔧 Drivers

### Native Driver (Default)

Uses Laravel's built-in `App::setLocale()`. Safe for traditional FPM/mod_php.

```env
LOCALIZATION_DRIVER=native
```

### Context Driver

Uses Laravel 11+ `Context` facade for request-isolated state. This is safe for regular Octane environments.

```env
LOCALIZATION_DRIVER=context
```

### Swoole Driver

Uses Swoole's coroutine context. Required when enabling HOOKS_ALL flag for true per worker concurrency in **Laravel Octane with Swoole**.

```env
LOCALIZATION_DRIVER=swoole
```

### OpenSwoole Driver

Uses OpenSwoole's coroutine context. Required when enabling HOOKS_ALL flag for true per worker concurrency in **Laravel Octane with OpenSwoole**.

```env
LOCALIZATION_DRIVER=openswoole
```

### Custom Driver

Implement `LocalizationDriverContract`:

```php
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;

class MyCustomDriver implements LocalizationDriverContract
{
    public function getCurrentLocale(): ?string
    {
        // Return stored locale or null
    }

    public function setCurrentLocale(string $locale): void
    {
        // Store the locale
    }

    public function isSafeToMutateGlobalState(): bool
    {
        // Return true if driver allows mutating App locale, Carbon, etc.
        return false;
    }
}
```

```php
// config/localization.php
'driver' => \App\Drivers\MyCustomDriver::class,
```

---

## 📚 API Reference

### `localization()` Helper

| Method                                              | Description                                      |
| --------------------------------------------------- | ------------------------------------------------ |
| `getCurrentLocale(): string`                        | Get the current request locale                   |
| `setCurrentLocale(string $locale): void`            | Set the locale for the current request           |
| `getConfigLocale(): string`                         | Get the configured default locale                |
| `getSupportedLocales(): array`                      | Get list of supported locales                    |
| `getRouteKey(): string`                             | Get the route parameter name (default: `locale`) |
| `route($name, $params, $absolute): string`          | Generate localized route URL                     |
| `t($key, $replace, $locale): string`                | Translate a string                               |
| `tchoice($key, $number, $replace, $locale): string` | Pluralized translation                           |
| `formatNumber($value, $style, $options): string`    | Format number/currency/percent                   |

### Blade Directives

| Directive                        | Usage                           |
| -------------------------------- | ------------------------------- |
| `@locale`                        | Output current locale           |
| `@t('key')`                      | Translate string                |
| `@t('key', ['name' => $value])`  | Translate with replacements     |
| `@tchoice('key', $count)`        | Pluralized translation          |
| `@route('name')`                 | Localized route URL             |
| `@route('name', $params)`        | Localized route with parameters |
| `@number($value)`                | Locale-formatted number         |
| `@currency($value, 'USD')`       | Locale-formatted currency       |
| `@percent($value)`               | Locale-formatted percentage     |
| `@date($date)`                   | Locale-formatted date           |
| `@date($date, 'MMMM YYYY')`      | Date with custom format         |
| `@datetime($date)`               | Locale-formatted date and time  |
| `@localeIs('en')`                | Conditional for locale          |
| `@locales($var)` / `@endlocales` | Loop through supported locales  |

### Facade

```php
use Localization;

Localization::getCurrentLocale();
Localization::route('home');
Localization::t('messages.welcome');
```

---

## 🧪 Testing

```bash
composer test       # Run tests
composer lint       # Run PHP Pint
composer analyse    # Run PHPStan
```

---

## 📚 Detailed Documentation

- [Drivers Documentation](docs/DRIVERS.md): In-depth guide to all available drivers, concurrency, and custom driver implementation.
- [Middleware Documentation](docs/MIDDLEWARES.md): Learn about the provided middlewares and how to use them for locale detection and headers.
- [Blade Directives Documentation](docs/BLADEDIRECTIVES.md): Learn about the blade directives.
- [Macros Documentation](docs/MACROS.md): Learn about the package macros.

---

## 📄 License

MIT License. See [LICENSE](LICENSE.md) for details.

---

## 🤝 Contributing

Contributions are welcome! See [CONTRIBUTING](CONTRIBUTING.md) for details.
