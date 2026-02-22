### [← Back to Main Documentation](../README.md)

# Middlewares

The Laravel Localization Suite provides two primary middlewares to automatically handle locale detection from the URL and set the appropriate HTTP headers.

## Available Middlewares

### 1. `SetLocaleFromRoute`
**Alias:** `localization.from_route`

This middleware is responsible for detecting the locale from the route parameters (e.g., the `{locale}` segment) and setting it as the active application locale.

**How it works:**
- It reads the route parameter defined in your config (`localization.route_key`, defaults to `locale`).
- **If the locale is valid and supported:** It sets the current locale using the LocalizationService.
- **If the locale is present but unsupported:** It intercepts the request, negotiates a fallback locale (based on the request's `Accept-Language` header or your configured default), replaces the invalid segment in the URL, and issues a standard HTTP redirect to the correct localized URL.
- **If no locale is present:** It negotiates the best locale available for the user and sets it as the active locale.

### 2. `SetLocalizedHeaders`
**Alias:** `localization.headers`

This middleware enriches your HTTP response with the correct headers for caching and SEO purposes.

**How it works:**
- It adds the `Content-Language` header to the response, matching the currently active locale.
- It appends `Accept-Language` to the `Vary` header, informing HTTP caches (like CDNs or proxies) that the response content varies based on the user's language preferences.

---

## Grouped Middleware

For convenience, the package groups both middlewares under a single alias.

**Alias:** `localization`

The `localization` middleware group automatically runs both `localization.from_route` and `localization.headers` in sequence.

---

## Usage Example

The most common way to use these middlewares is applying the `localization` group to your localized routes:

```php
use Illuminate\Support\Facades\Route;

Route::localized()
    ->middleware('localization') // Runs both detection and headers
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/about', [PageController::class, 'about'])->name('about');
    });
```

### Using Individual Middlewares

If you need finer control, you can apply them individually:

```php
Route::middleware(['localization.from_route', 'localization.headers'])->group(function () {
    // ...
});
```

### [← Back to Main Documentation](../README.md)
