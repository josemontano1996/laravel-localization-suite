# LocalizationService Documentation

## Overview

The `LocalizationService` is the core service of the localization suite that manages application locales, translations, and locale-aware URL generation. It provides a unified interface for handling multiple languages across your Laravel application, including route generation, translation lookups, and number formatting.

## Purpose

The `LocalizationService` is responsible for:

- **Locale Management**: Detecting, storing, and switching between supported locales
- **Route Generation**: Creating locale-aware URLs with proper locale parameters
- **Translations**: Managing multi-language text using Laravel's translation system
- **Number Formatting**: Formatting numbers, currencies, and other locale-specific data

## Installation & Registration

The service is automatically registered when you load the `LocalizationServiceProvider`:

```php
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;

// Access via dependency injection
class YourController extends Controller
{
    public function __construct(private LocalizationServiceContract $service) {}
}

// Or access via Laravel's service container
$service = app(LocalizationServiceContract::class);

// Or use the helper function
$service = localization();
```

## Configuration

Configure supported locales and defaults in `config/app.php`:

```php
'locale' => 'en',
'fallback_locale' => 'es',
'supported_locales' => ['en', 'es', 'fr'],
```

Or use the localization config file at `config/localization.php`:

```php
return [
    'route_key' => 'locale', // URL parameter name for locale
    // Other configuration...
];
```

## Available Methods

### `getRouteKey(): string`

Returns the configured route parameter key used for locale in URLs.

**Returns:** String - Usually `'locale'`

**Example:**

```php
$key = $service->getRouteKey(); // 'locale'

// Use in route definitions
Route::get('/{locale}/posts', ...)->where('locale', '[a-z]{2}');
```

### `getCurrentLocale(): string`

Gets the currently active locale. Returns the driver's locale if set, otherwise falls back to config default.

**Returns:** String - Current locale code (e.g., `'en'`, `'es'`, `'fr'`)

**Example:**

```php
$current = $service->getCurrentLocale(); // 'en'
```

### `setCurrentLocale(string $locale): void`

Sets the application's current locale. If the locale is not supported, automatically falls back to the default locale.

**Parameters:**

- `$locale` - The locale code to switch to

**Example:**

```php
$service->setCurrentLocale('es'); // Switches to Spanish
$service->setCurrentLocale('invalid'); // Falls back to default locale
```

**Features:**

- Validates locale against supported locales
### `getConfigLocale(): string`

Gets the application's configured default locale from `config/app.php`.

**Returns:** String - Default locale, or fallback locale if not set

**Example:**

```php
$default = $service->getConfigLocale(); // 'en'
```

### `getSupportedLocales(): array`

Gets the list of all supported locales from configuration.

**Returns:** Array - List of supported locale codes

**Example:**

```php
$supported = $service->getSupportedLocales(); // ['en', 'es', 'fr']

if (in_array('de', $supported)) {
    // German is supported
}
```

### `route(BackedEnum|string $name, mixed $parameters = [], bool $absolute = true): string`

Generates a URL to a named route with the current locale automatically included.

**Parameters:**

- `$name` - Route name (string or BackedEnum)
- `$parameters` - Route parameters (array or single value, default: `[]`)
- `$absolute` - Whether to generate absolute URL (default: `true`)

**Returns:** String - Complete URL with locale parameter

**How Locale is Injected:**

The service automatically includes the current locale in all generated URLs:

- **Routes WITH `{locale}` segment** → Locale is injected into the URL path
- **Routes WITHOUT `{locale}` segment** → Locale is added as a query parameter

Both approaches are valid and useful for different scenarios:

- Use path-based locales for user-facing pages (SEO-friendly, clean URLs)
- Use query parameter locales for API routes, webhooks, legacy routes, or external integrations

**Example with `{locale}` route segment (Recommended for user-facing pages):**

```php
// Route definition with locale segment
Route::get('/{locale}/posts/{id}', [PostController::class, 'show'])->name('posts.show');

$url = $service->route('posts.show', ['id' => 5]);
// Returns: 'http://example.com/en/posts/5'

$url = $service->route('posts.show', ['id' => 5], false);
// Returns: '/en/posts/5' (relative URL)

$service->setCurrentLocale('es');
$url = $service->route('posts.show', ['id' => 5]);
// Returns: 'http://example.com/es/posts/5'
```

**Example without `{locale}` route segment (Useful for APIs and webhooks):**

```php
// Route definition without locale segment
Route::get('/api/posts/{id}', [ApiController::class, 'show'])->name('api.posts.show');

$url = $service->route('api.posts.show', ['id' => 5]);
// Returns: 'http://example.com/api/posts/5?locale=en'

// Webhook endpoint without locale in path
Route::post('/webhook/payment', [WebhookController::class, 'payment'])->name('webhook.payment');

$url = $service->route('webhook.payment');
// Returns: 'http://example.com/webhook/payment?locale=en'
```

### `t(string $key, array $replace = [], ?string $locale = null): string`

Translates a language string key using the current locale. Wrapper around Laravel's `__()` helper.

**Parameters:**

- `$key` - Translation key (e.g., `'messages.hello'`)
- `$replace` - Variable replacements
- `$locale` - Optional locale override (if null, uses current locale)

**Returns:** String - Translated text

**Example:**

```php
// Translation file: resources/lang/en/messages.php
// ['hello' => 'Hello', 'welcome' => 'Welcome, :name!']

$text = $service->t('messages.hello');
// Returns: 'Hello'

$text = $service->t('messages.welcome', ['name' => 'John']);
// Returns: 'Welcome, John!'

$text = $service->t('messages.hello', [], 'es');
// Returns: Spanish translation regardless of current locale
```

### `tchoice(string $key, int|float $number, array $replace = [], ?string $locale = null): string`

Translates a pluralized language string based on a count. Wrapper around Laravel's `trans_choice()` helper.

**Parameters:**

- `$key` - Translation key with plural forms
- `$number` - Count to determine plural form
- `$replace` - Variable replacements
- `$locale` - Optional locale override

**Returns:** String - Appropriately pluralized translation

**Example:**

```php
// Translation file: resources/lang/en/messages.php
// ['apples' => '{0} No apples|{1} One apple|[2,*] :count apples']

$text = $service->tchoice('messages.apples', 0);
// Returns: 'No apples'

$text = $service->tchoice('messages.apples', 1);
// Returns: 'One apple'

$text = $service->tchoice('messages.apples', 5);
// Returns: '5 apples'

$text = $service->tchoice('messages.apples', 3, [], 'es');
// Returns: Spanish plural translation
```

### `formatNumber($value, int $style, array $options = []): string`

Formats a number according to the current locale using PHP's `NumberFormatter`.

**Parameters:**

- `$value` - Number to format
- `$style` - NumberFormatter style constant (see NumberFormatter class)
- `$options` - Additional formatting options:
  - `decimals` - Number of decimal places
  - `currency` - Currency code (for CURRENCY style)

**Returns:** String - Formatted number

**Available Styles:**

- `NumberFormatter::DECIMAL` (1) - Default number formatting
- `NumberFormatter::CURRENCY` (4) - Currency formatting
- `NumberFormatter::PERCENT` (2) - Percentage formatting
- `NumberFormatter::SCIENTIFIC` (3) - Scientific notation

**Examples:**

```php
// Decimal formatting
$formatted = $service->formatNumber(1234.567, NumberFormatter::DECIMAL);
// 'en': '1,234.567'
// 'es': '1.234,567'

// With specific decimal places
$formatted = $service->formatNumber(1234.56789, NumberFormatter::DECIMAL, ['decimals' => 2]);
// 'en': '1,234.57'

// Currency formatting
$formatted = $service->formatNumber(1234.56, NumberFormatter::CURRENCY, ['currency' => 'USD']);
// 'en': '$1,234.56'
// 'es': '1.234,56 $'
// 'fr': '1 234,56 $'

// Percentage formatting
$formatted = $service->formatNumber(0.85, NumberFormatter::PERCENT);
// 'en': '85%'
// 'es': '85 %'

// Scientific notation
$formatted = $service->formatNumber(12340, NumberFormatter::SCIENTIFIC);
// 'en': '1.234E+4'
```

## Usage Examples

### Route Definition Best Practices

The package supports both path-based and query parameter locales. Choose the approach that best fits your use case:

**Path-based locales (Recommended for user-facing pages):**

```php
// ✅ Best for SEO and user experience
Route::prefix('/{locale}')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{id}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::get('/about', [PageController::class, 'about'])->name('about');
});
// URLs: /en/posts/5, /es/about
```

**Query parameter locales (Useful for APIs and specific scenarios):**

```php
// ✅ Useful for API routes, webhooks, or legacy integrations
Route::prefix('/api')->group(function () {
    Route::get('/posts/{id}', [ApiController::class, 'show'])->name('api.posts.show');
    Route::get('/status', [ApiController::class, 'status'])->name('api.status');
});

Route::post('/webhook/payment', [WebhookController::class, 'payment'])->name('webhook.payment');
// URLs: /api/posts/5?locale=en, /webhook/payment?locale=es
```

**Mixed approach (Combine both as needed):**

```php
// User-facing routes with locale in path
Route::prefix('/{locale}')->group(function () {
    Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
});

// API routes with locale as query parameter
Route::prefix('/api')->group(function () {
    Route::get('/posts/{id}', [ApiController::class, 'show'])->name('api.posts.show');
});
```

### Setting Up Your Application

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;

class SetLocaleFromRequest
{
    public function handle($request, Closure $next)
    {
        $service = app(LocalizationServiceContract::class);

        // Get locale from URL parameter
        $locale = $request->route('locale') ?? $service->getCurrentLocale();

        $service->setCurrentLocale($locale);

        return $next($request);
    }
}
```

### Building Multilingual Views

```blade
<!-- resources/views/posts/show.blade.php -->

<h1>{{ $service->t('posts.title') }}</h1>

<p>{{ $service->t('posts.published_date') }}: {{ $post->published_at->toDateString() }}</p>

<a href="{{ $service->route('posts.edit', ['id' => $post->id]) }}">
    {{ $service->t('common.edit') }}
</a>

<!-- Number formatting in views -->
<span class="price">{{ $service->formatNumber($product->price, NumberFormatter::CURRENCY, ['currency' => 'USD']) }}</span>
```

### Controller Example

```php
<?php

namespace App\Http\Controllers;

use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use App\Models\Order;

class OrderController extends Controller
{
    public function __construct(private LocalizationServiceContract $service) {}

    public function show(Order $order)
    {
        return view('orders.show', [
            'order' => $order,
            'subtotal' => $this->service->formatNumber(
                $order->subtotal,
                NumberFormatter::CURRENCY,
                ['currency' => $order->currency]
            ),
            'total' => $this->service->formatNumber(
                $order->total,
                NumberFormatter::CURRENCY,
                ['currency' => $order->currency]
            ),
        ]);
    }

    public function printInvoice(Order $order)
    {
        // Temporarily switch locale for invoice generation
        $currentLocale = $this->service->getCurrentLocale();
        $this->service->setCurrentLocale($order->locale);

        $invoice = PDF::generate('invoices.pdf', [
            'order' => $order,
            'company_name' => $this->service->t('company.name'),
        ]);

        // Restore original locale
        $this->service->setCurrentLocale($currentLocale);

        return $invoice;
    }
}
```

### API Response with Localization

```php
public function json()
{
    $service = app(LocalizationServiceContract::class);

    return response()->json([
        'current_locale' => $service->getCurrentLocale(),
        'supported_locales' => $service->getSupportedLocales(),
        'url_key' => $service->getRouteKey(),
        'message' => $service->t('api.welcome'),
        'items_count' => $service->tchoice(
            'api.items_count',
            $this->items->count()
        ),
    ]);
}
```

### Working with Backed Enums

```php
<?php

enum Language: string
{
    case English = 'en';
    case Spanish = 'es';
    case French = 'fr';
}

// Routes can use Backed Enums
$url = $service->route(Language::English, ['id' => 5]);
```

This ensures all parts of your application use the same locale context.

## Related Documentation

- [RedirectorService](./REDIRECTOR.md) - Locale-aware redirects
- [Middlewares](./MIDDLEWARES.md) - Middleware for locale detection
- [Blade Directives](./BLADEDIRECTIVES.md) - Frontend localization helpers
- [Macros](./MACROS.md) - Helper macros and extensions
- [Drivers](./DRIVERS.md) - Locale storage drivers
