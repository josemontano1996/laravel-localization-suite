# RedirectorService Documentation

## Overview

The `RedirectorService` is a localization-aware redirect service that automatically handles locale parameters in all redirects. It wraps Laravel's built-in `Redirector` class and ensures that every redirect includes the current locale context, making it seamless to work with multi-locale applications.

## Purpose

When building multi-locale applications, redirects need to maintain locale context. The `RedirectorService` eliminates the need to manually append locale parameters to every redirect response. It intelligently integrates with the `LocalizationService` to automatically include the current locale in:

- Route redirects
- Signed route redirects
- Temporary signed redirects
- Action redirects
- Back redirects
- Custom path redirects

## Installation & Registration

The service is automatically registered when you load the `LocalizationServiceProvider`:

```php
use Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService;

// Access via dependency injection
class YourController extends Controller
{
    public function __construct(private RedirectorService $redirector) {}
}

// Or access via Laravel's service container
$redirector = app(RedirectorService::class);
```

You can also access it through a custom redirect macro:

```php
// In your service provider or RouteServiceProvider
app('redirect')->macro('localized', fn() => app(RedirectorService::class));

// Then use it like:
return redirect()->localized()->route('post.show', ['id' => 123]);
```

## Available Methods

### `route(string $name, mixed $params = [], int $status = 302, array $headers = [])`

Redirect to a named route with automatic locale parameter injection.

**Parameters:**

- `$name` - Route name to redirect to
- `$params` - Route parameters (array or single value)
- `$status` - HTTP status code (default: 302)
- `$headers` - Custom response headers

**How Locale is Injected:**

The service automatically includes the current locale in all redirects:

- **Routes WITH `{locale}` segment** → Locale is injected into the URL path
- **Routes WITHOUT `{locale}` segment** → Locale is added as a query parameter

Both approaches are valid and useful for different scenarios:

- Use path-based locales for user-facing pages (SEO-friendly)
- Use query parameter locales for API routes, legacy routes, or external integrations

**Example with `{locale}` route segment (Recommended for user pages):**

```php
// Route definition with locale in path
Route::get('/{locale}/posts/{id}', [PostController::class, 'show'])->name('posts.show');

// Redirect with locale automatically injected in path
return $this->redirector->route('posts.show', ['id' => 5]);
// URL: http://example.com/en/posts/5
```

**Example without `{locale}` route segment (Useful for APIs):**

```php
// Route definition without locale in path
Route::get('/api/posts/{id}', [ApiController::class, 'show'])->name('api.posts.show');

// Redirect - locale becomes query parameter
return $this->redirector->route('api.posts.show', ['id' => 5]);
// URL: http://example.com/api/posts/5?locale=en
```

### `action(array|string $action, mixed $params = [], int $status = 302, array $headers = [])`

Redirect to a controller action with automatic locale parameter injection.

**Example:**

```php
return $this->redirector->action([PostController::class, 'show'], ['id' => 5]);
// Automatically includes locale parameter in the action URL
```

### `to(string $path, int $status = 302, array $headers = [], ?bool $secure = null)`

Redirect to a specific path with automatic locale context.

**Example:**

```php
return $this->redirector->to('/dashboard');
// Locale parameter is automatically added based on current locale
```

### `away(string $path, int $status = 302, array $headers = [])`

Redirect to an external URL **without** locale injection (for external links).

**Example:**

```php
return $this->redirector->away('https://external-site.com');
// No locale parameter added - bypasses localization logic
```

### `back(int $status = 302, array $headers = [], $fallback = false)`

Redirect back to the previous page while preserving locale context.

**Example:**

```php
return $this->redirector->back();
// Returns to previous page with locale context preserved
```

### `signedRoute(string $name, mixed $params = [], $expiration = null, int $status = 302, array $headers = [])`

Redirect to a signed route with automatic locale parameter and signature.

**Example:**

```php
return $this->redirector->signedRoute('email.verify', ['id' => 1], now()->addHours(24));
// Creates signed URL with locale parameter included
```

### `temporarySignedRoute(string $name, $expiration, mixed $params = [], int $status = 302, array $headers = [])`

Redirect to a temporary signed route with expiration timestamp.

**Example:**

```php
return $this->redirector->temporarySignedRoute(
    'password.reset',
    now()->addMinutes(60),
    ['token' => 'abc123', 'email' => 'user@example.com']
);
// Creates temporary signed URL valid for 60 minutes with locale included
```

### `refresh(int $status = 302, array $headers = [])`

Refresh the current page while preserving locale context.

**Example:**

```php
return $this->redirector->refresh();
// Reloads current page maintaining locale and all parameters
```

### `intended(string $default = '/', int $status = 302, array $headers = [])`

Redirect to the "intended" URL captured before authentication, with locale context.

**Example:**

```php
return $this->redirector->intended('/dashboard');
// Redirects to the originally requested page with locale preserved
```

## Usage Examples

### Login Controller

```php
<?php

namespace App\Http\Controllers\Auth;

use Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService;

class LoginController extends Controller
{
    public function __construct(private RedirectorService $redirector) {}

    public function authenticate()
    {
        // Validate credentials...

        return $this->redirector->intended('/dashboard');
        // User stays in their current locale after login
    }
}
```

### Post Controller

```php
<?php

namespace App\Http\Controllers;

use Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService;
use App\Models\Post;

class PostController extends Controller
{
    public function __construct(private RedirectorService $redirector) {}

    public function store()
    {
        $post = Post::create($this->validated());

        return $this->redirector->route('posts.show', ['id' => $post->id]);
        // Redirects to /en/posts/5 (if current locale is 'en')
    }

    public function update(Post $post)
    {
        $post->update($this->validated());

        return $this->redirector->back();
        // Returns to previous page maintaining locale
    }
}
```

### Email Verification

```php
public function sendVerificationEmail($userId)
{
    $user = User::find($userId);

    return $this->redirector->signedRoute(
        'email.verify',
        ['id' => $user->id],
        now()->addHours(24)
    );
    // Creates signed verification link with locale context
}
```

### External Redirects

```php
public function redirectToPayment()
{
    // External payment gateway - no locale needed
    return $this->redirector->away('https://payment-provider.com/checkout');
}
```

## How It Works

1. **Locale Injection**: The service automatically retrieves the current locale from `LocalizationService`
2. **Route Key**: Uses the configured route key (default: `locale`) to inject locale parameters
3. **Transparent Proxying**: Any method not explicitly defined in `RedirectorService` is proxied to Laravel's `Redirector`
4. **External URL Bypass**: The `away()` method bypasses localization for external URLs

## Key Features

### ✅ Automatic Locale Context

Every redirect automatically includes the current locale parameter, ensuring users stay in their selected language.

### ✅ Signed Route Support

Full support for Laravel's signed routes, with automatic locale parameter inclusion.

### ✅ Temporary Signed Routes

Create temporary signed URLs (with expiration) that maintain locale context.

### ✅ Method Proxying

Any method not explicitly handled is passed through to Laravel's `Redirector`, ensuring compatibility.

### ✅ Back Redirects with Context

Users can go back while maintaining their locale context.

### ✅ External URL Support

The `away()` method handles external redirects without locale injection.

## Integration with Middleware

The service works seamlessly with localization middlewares:

```php
// In your routes or middleware
Route::middleware(['localize', 'auth'])->group(function () {
    Route::post('/posts', function (RedirectorService $redirector) {
        $post = Post::create(request()->validated());

        // Current locale is maintained by middleware
        // Redirector automatically includes it
        return $redirector->route('posts.show', ['id' => $post->id]);
    });
});
```

## Related Documentation

- [LocalizationService](../src/Services/LocalizationService.php) - Core localization service
- [Middlewares](./MIDDLEWARES.md) - Locale detection and management
- [Blade Directives](./BLADEDIRECTIVES.md) - Frontend localization helpers
- [Macros](./MACROS.md) - Helper macros and extensions
