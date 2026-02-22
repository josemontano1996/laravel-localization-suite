### [← Back to Main Documentation](../README.md)

# Macros

The Laravel Localization Suite registers several macros on built-in Laravel classes to make working with locales more intuitive.

## Redirector Macros

### `redirect()->localized()`
Creates a localized redirector instance. Any subsequent redirect methods (`route()`, `action()`, `to()`, `back()`, `signedRoute()`, etc.) will automatically maintain the current locale context.

```php
return redirect()->localized()->route('dashboard');
// Redirects to: /en/dashboard
```
*See the [RedirectorService Documentation](./REDIRECTOR.md) for more details.*

---

## Request Macros

### `request()->locale()`
Quickly get the current active locale from the request.

```php
$currentLocale = request()->locale(); // e.g., 'en'
```

### `request()->acceptedLocales()`
Parses the `Accept-Language` header to return an array of accepted locales sorted by quality (preference), using Laravel's underlying `getLanguages()` method.

```php
$accepted = request()->acceptedLocales();
// Example: ['en-US' => 0, 'en' => 1, 'es' => 2]
```

### `request()->preferredLocale(?array $supported = [])`
Determines the best matching locale from the request's `Accept-Language` header by comparing it against an array of supported locales. If no supported locales are provided, it returns the most preferred accepted locale.

```php
$bestMatch = request()->preferredLocale(['es', 'fr', 'en']);
```

---

## URL Macros

### `URL::withLocale(string $locale)`
Generates the current URL but with a different locale. This is incredibly useful for building language switchers.

```php
// Current URL: /en/about-us
$spanishUrl = URL::withLocale('es');
// Returns: /es/about-us
```

### `URL::localeRoute(string $name, array $params = [], bool $absolute = true)`
Generates a localized route URL directly from the `URL` facade.

```php
$url = URL::localeRoute('products.show', ['product' => 1]);
```

---

## Route Macros

### `Route::localized(\Closure $callback)`
Creates a route group prefixed with the locale parameter (e.g., `/{locale}/`). It automatically restricts the `{locale}` parameter using a regex composed of your supported locales from the configuration.

```php
Route::localized(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
```

Using this macro ensures that only supported locales will match the route. If an unsupported locale is provided in the URL, it will not match this route group.

### [← Back to Main Documentation](../README.md)
