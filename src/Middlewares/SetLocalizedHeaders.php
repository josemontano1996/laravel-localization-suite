<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Symfony\Component\HttpFoundation\Response;

final class SetLocalizedHeaders
{
    /**
     * Create a new SetLocaleHeaders middleware instance.
     *
     * @param  LocalizationServiceContract  $localizationService  The localization service
     */
    public function __construct(
        private readonly LocalizationServiceContract $localizationService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $locale = $this->localizationService->getCurrentLocale();

        $response->header('Content-Language', $locale);

        $response->vary('Accept-Language');

        return $response;
    }
}
