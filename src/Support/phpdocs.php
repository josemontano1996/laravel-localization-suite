<?php

declare(strict_types=1);

namespace Illuminate\Http {
    /**
     * @method array acceptedLocales()
     * @method string|null preferredLocale(array $supported = [])
     * @method string locale()
     */
    class Request {}
}

namespace Josemontano1996\LaravelLocalizationSuite\Services {
    use Illuminate\Http\RedirectResponse;

    /**
     * @mixin \Illuminate\Routing\Redirector
     */
    class RedirectorService
    {
        public function route(string $name, mixed $params = [], int $status = 302, array $headers = []): RedirectResponse
        {
            return new RedirectResponse('');
        }

        public function intended(string $default = '/', int $status = 302, array $headers = []): RedirectResponse
        {
            return new RedirectResponse('');
        }

        public function action(array|string $action, mixed $params = [], int $status = 302, array $headers = []): RedirectResponse
        {
            return new RedirectResponse('');
        }

        public function refresh(int $status = 302, array $headers = []): RedirectResponse
        {
            return new RedirectResponse('');
        }

        public function signedRoute(string $name, mixed $params = [], $expiration = null, int $status = 302, array $headers = []): RedirectResponse
        {
            return new RedirectResponse('');
        }

        public function temporarySignedRoute(string $name, $expiration, mixed $params = [], int $status = 302, array $headers = []): RedirectResponse
        {
            return new RedirectResponse('');
        }

        public function back(int $status = 302, array $headers = [], $fallback = false): RedirectResponse
        {
            return new RedirectResponse('');
        }

        public function to(string $path, int $status = 302, array $headers = [], ?bool $secure = null): RedirectResponse
        {
            return new RedirectResponse('');
        }

        public function away(string $path, int $status = 302, array $headers = []): RedirectResponse
        {
            return new RedirectResponse('');
        }
    }
}

namespace Illuminate\Routing {
    use Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService;

    class Redirector
    {
        /**
         * Access the localized redirect proxy.
         */
        public function localized(): RedirectorService
        {
            return new RedirectorService(
                $this,
                app(\Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract::class)
            );
        }
    }
}

namespace Illuminate\Support\Facades {
    /**
     * @method static string withLocale(string $locale)
     * @method static string localeRoute($name, $params = [], $absolute = true)
     */
    class URL {}
}
