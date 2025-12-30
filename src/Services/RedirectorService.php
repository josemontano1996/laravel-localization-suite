<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;

class RedirectorService
{
    public function __construct(
        protected Redirector $redirector,
        protected LocalizationServiceContract $service
    ) {}

    /**
     * Pass any other methods directly to the underlying Redirector.
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->redirector->{$method}(...$parameters);
    }

    /**
     * Redirect to a localized route using the LocalizationService.
     */
    public function route(string $name, mixed $params = [], int $status = 302, array $headers = []): RedirectResponse
    {
        // We use your service's logic to get the correct URL
        $url = $this->service->route($name, $params);

        return $this->redirector->to($url, $status, $headers);
    }

    /**
     * Redirect to the "intended" URL before a middleware took over,
     * but ensure it stays within the current locale context.
     */
    public function intended(string $default = '/', int $status = 302, array $headers = []): RedirectResponse
    {
        $url = $this->redirector->getIntendedUrl() ?? $default;

        // We ensure the fallback or intended URL is localized
        return $this->redirector->to($this->service->route($url), $status, $headers);
    }

    /**
     * Redirect to a controller action, localized.
     * Example: redirect()->localized()->action([UserController::class, 'show'], ['id' => 1])
     */
    public function action(array|string $action, mixed $params = [], int $status = 302, array $headers = []): RedirectResponse
    {
        // Inject locale into the action parameters
        $params = array_merge(['locale' => $this->service->getCurrentLocale()], (array) $params);

        return $this->redirector->action($action, $params, $status, $headers);
    }

    /**
     * Refresh the current page but keep the localization intact.
     */
    public function refresh(int $status = 302, array $headers = []): RedirectResponse
    {
        return $this->redirector->to(app('url')->current(), $status, $headers);
    }

    /**
     * Create a localized signed route redirect.
     */
    public function signedRoute(string $name, mixed $params = [], $expiration = null, int $status = 302, array $headers = []): RedirectResponse
    {
        $params = array_merge(['locale' => $this->service->getCurrentLocale()], (array) $params);

        return $this->redirector->to(
            app('url')->signedRoute($name, $params, $expiration),
            $status,
            $headers
        );
    }

    /**
     * Create a localized temporary signed route redirect.
     */
    public function temporarySignedRoute(string $name, $expiration, mixed $params = [], int $status = 302, array $headers = []): RedirectResponse
    {
        $params = array_merge(['locale' => $this->service->getCurrentLocale()], (array) $params);

        return $this->redirector->to(
            app('url')->temporarySignedRoute($name, $expiration, $params),
            $status,
            $headers
        );
    }

    /**
     * Redirect back, ensuring the locale context is preserved.
     */
    public function back(int $status = 302, array $headers = [], $fallback = false): RedirectResponse
    {
        return $this->redirector->back($status, $headers, $this->service->route($fallback ?: '/'));
    }

    /**
     * Redirect to a specific path, automatically prefixing the locale.
     */
    public function to(string $path, int $status = 302, array $headers = [], ?bool $secure = null): RedirectResponse
    {
        // If the path doesn't already start with the locale, prefix it
        $url = $this->service->route($path);

        return $this->redirector->to($url, $status, $headers, $secure);
    }

    /**
     * Redirect to an external URL (ignores localization).
     */
    public function away(string $path, int $status = 302, array $headers = []): RedirectResponse
    {
        // We bypass our localization logic entirely for external links
        return $this->redirector->away($path, $status, $headers);
    }
}
