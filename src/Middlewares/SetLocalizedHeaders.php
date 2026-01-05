<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Middlewares;

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

        $response->headers->set('Content-Language', $locale);

        // Add to Vary header (append if already exists)
        $vary = $response->headers->get('Vary');
        $varyValues = $vary ? array_map('trim', explode(',', $vary)) : [];

        if (! \in_array('Accept-Language', $varyValues, true)) {
            $varyValues[] = 'Accept-Language';
            $response->headers->set('Vary', implode(', ', $varyValues));
        }

        return $response;
    }
}
