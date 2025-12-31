# Driver Architecture

This package uses a driver-based architecture to manage localization state. Each driver is responsible for how the current locale is stored, resolved, and isolated per request or coroutine. Choose the driver that best fits your deployment environment and concurrency needs.

---

## Built-in Drivers

### Native Driver

- **Class:** `Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\NativeDriver`
- **Environment:** Standard Laravel (FPM/mod_php)
- **How it works:** Uses Laravel's global `app()->setLocale()` and `app()->getLocale()` methods. Locale is stored globally, so this is **not concurrency safe** but is the default for traditional PHP environments.
- **Global state:** Yes (`isSafeToMutateGlobalState()` returns `true`).

---

### Context Driver

- **Class:** `Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\ContextDriver`
- **Environment:** Laravel 11+ with the Context API
- **How it works:** Stores the locale in Laravel's Context, providing request-level isolation. This prevents cross-request bleed in most cases, but is not safe for true concurrency (e.g., Swoole/Octane).
- **Global state:** No (`isSafeToMutateGlobalState()` returns `false`).

---

### Swoole Driver

- **Class:** `Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\SwooleDriver`
- **Environment:** Laravel Octane with Swoole
- **How it works:** Stores the locale in Swoole's coroutine context, ensuring each concurrent request has its own locale state. Throws an exception if the `swoole` extension is not loaded.
- **Concurrency:** **Safe for concurrent environments** (each coroutine/request is fully isolated).
- **Global state:** No (`isSafeToMutateGlobalState()` returns `false`).
- **Requirements:** `swoole` PHP extension

---

### OpenSwoole Driver

- **Class:** `Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\OpenSwooleDriver`
- **Environment:** Laravel Octane with OpenSwoole
- **How it works:** Stores the locale in OpenSwoole's coroutine context, similar to the Swoole driver. Throws an exception if the `openswoole` extension is not loaded.
- **Concurrency:** **Safe for concurrent environments** (each coroutine/request is fully isolated).
- **Global state:** No (`isSafeToMutateGlobalState()` returns `false`).
- **Requirements:** `openswoole` PHP extension

---

## How to Select a Driver

Set the driver in your `config/localization.php`:

```php
'driver' => env('LOCALIZATION_DRIVER', 'native'),
```

Or via your `.env` file:

```
LOCALIZATION_DRIVER=native
```

Replace `native` with `context`, `swoole`, or `openswoole` as needed for your environment.

---

## Custom Drivers

You can create your own driver by implementing the `LocalizationDriverContract` found in `src/Contracts/LocalizationDriverContract.php`. Your custom driver must implement:

- `getCurrentLocale(): ?string`
- `setCurrentLocale(string $locale): void`
- `isSafeToMutateGlobalState(): bool`

**Example:**

```php
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationDriverContract;

class MyCustomDriver implements LocalizationDriverContract
{
	public function getCurrentLocale(): ?string
	{
		// Return the current locale (from session, request, etc.)
	}

	public function setCurrentLocale(string $locale): void
	{
		// Store the locale
	}

	public function isSafeToMutateGlobalState(): bool
	{
		// Return true if your driver mutates global state (e.g., app()->setLocale())
		return false;
	}
}
```

Register your custom driver in your service provider or in `config/localization.php`:

```php
'driver' => \App\Localization\Drivers\MyCustomDriver::class,
```

Refer to the existing drivers in `src/Drivers/Localization/` for implementation examples and best practices.
