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
     * Redirect to a localized route using the LocalizationService.
     */
    public function route(string $name, mixed $params = [], int $status = 302, array $headers = []): RedirectResponse
    {
        // We use your service's logic to get the correct URL
        $url = $this->service->route($name, $params);

        return $this->redirector->to($url, $status, $headers);
    }
}
