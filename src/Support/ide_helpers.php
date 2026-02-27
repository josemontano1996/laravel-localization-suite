<?php

namespace Illuminate\Http {
    /**
     * @method array acceptedLocales()
     * @method string|null preferredLocale(array $supported = [])
     * @method string locale()
     */
    class Request {}
}

namespace Illuminate\Support\Facades {
    /**
     * @method static string withLocale(string $locale)
     * @method static string localeRoute(\BackedEnum|string $name, mixed $params = [], bool $absolute = true)
     */
    class URL {}

    /**
     * @method static \Illuminate\Routing\RouteRegistrar localized(\Closure $callback = null)
     */
    class Route {}

    /**
     * @method static \Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService localized()
     */
    class Redirect {}
}

namespace Illuminate\Routing {
    /**
     * @method \Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService localized()
     */
    class Redirector {}

    /**
     * @method \Illuminate\Routing\RouteRegistrar localized(\Closure $callback = null)
     */
    class Router {}
}