<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Registrars\RegisterMacros;

describe('RegisterMacros', function () {
    beforeEach(function () {
        $this->service = app(LocalizationServiceContract::class);
        RegisterMacros::register();
    });

    describe('Redirector::localized() macro', function () {
        it('returns a RedirectorService instance', function () {
            $redirector = redirect()->localized();

            expect($redirector)->toBeInstanceOf(\Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService::class);
        });

        it('can chain redirect methods through localized()', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/redirect-test', function () {
                    return redirect()->localized()->route('post.show', ['id' => 5]);
                });

            $response = $this->get('/en/redirect-test');

            expect($response->status())->toBe(302)
                ->and($response->getTargetUrl())->toContain('/en/post/5');
        });
    });

    describe('Request::locale() macro', function () {
        it('returns the current locale from the service', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/locale-test', function () {
                    return response()->json(['locale' => request()->locale()]);
                });

            $response = $this->get('/en/locale-test');

            expect($response->json('locale'))->toBe('en');
        });

        it('reflects locale changes', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/locale-test2', function () {
                    return response()->json(['locale' => request()->locale()]);
                });

            $response = $this->get('/fr/locale-test2');

            expect($response->json('locale'))->toBe('fr');
        });
    });

    describe('Request::acceptedLocales() macro', function () {
        it('returns an array of accepted locales from Accept-Language header', function () {
            $request = Request::create('/', 'GET', [], [], [], [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9,fr;q=0.8',
            ]);

            $accepted = $request->acceptedLocales();

            expect($accepted)->toBeArray()
                ->and($accepted)->toHaveKey('en_US')
                ->and($accepted)->toHaveKey('en');
        });

        it('returns array with default language when no Accept-Language header', function () {
            $request = Request::create('/', 'GET');

            $accepted = $request->acceptedLocales();
            expect($accepted)->toBeArray()->not->toBeEmpty();
        });
    });

    describe('Request::preferredLocale() macro', function () {
        it('returns the first accepted locale when no supported list provided', function () {
            $request = Request::create('/', 'GET', [], [], [], [
                'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.9,en;q=0.8',
            ]);

            // PHP converts dashes to underscores in locale names
            expect($request->preferredLocale())->toBe('fr_FR');
        });

        it('returns first browser language when no Accept-Language header', function () {
            $request = Request::create('/', 'GET');

            // Without Accept-Language, it returns the browser's default (usually en_US or similar)
            expect($request->preferredLocale())->toBeString();
        });

        it('matches locale from supported list with underscore format', function () {
            $request = Request::create('/', 'GET', [], [], [], [
                'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.9,en;q=0.8',
            ]);

            // Match using underscore format
            expect($request->preferredLocale(['en', 'fr_FR', 'es']))->toBe('fr_FR');
        });

        it('matches language prefix when exact locale not found', function () {
            $request = Request::create('/', 'GET', [], [], [], [
                'HTTP_ACCEPT_LANGUAGE' => 'fr-CA,fr;q=0.9,en;q=0.8',
            ]);

            expect($request->preferredLocale(['en', 'fr', 'es']))->toBe('fr');
        });

        it('is case-insensitive when matching locales', function () {
            $request = Request::create('/', 'GET', [], [], [], [
                'HTTP_ACCEPT_LANGUAGE' => 'EN-US,en;q=0.9',
            ]);

            expect($request->preferredLocale(['en', 'fr', 'es']))->toBe('en');
        });

        it('preserves original casing from supported list', function () {
            $request = Request::create('/', 'GET', [], [], [], [
                'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.9',
            ]);

            expect($request->preferredLocale(['EN', 'fr', 'es']))->toBe('EN');
        });

        it('returns null when no matching locale found', function () {
            $request = Request::create('/', 'GET', [], [], [], [
                'HTTP_ACCEPT_LANGUAGE' => 'de-DE,de;q=0.9',
            ]);

            expect($request->preferredLocale(['en', 'fr', 'es']))->toBeNull();
        });

        it('respects quality values in Accept-Language header', function () {
            $request = Request::create('/', 'GET', [], [], [], [
                'HTTP_ACCEPT_LANGUAGE' => 'en;q=0.5,fr;q=0.9,es;q=0.3',
            ]);

            // Should match 'fr' first as it has highest quality (0.9)
            expect($request->preferredLocale(['en', 'fr', 'es']))->toBe('fr');
        });
    });

    describe('URL::withLocale() macro', function () {
        it('changes locale for current named route', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/url-test', function () {
                    // withLocale should use the current route's name
                    return response()->json(['url' => URL::withLocale('fr')]);
                })->name('home'); // Use existing route name

            $response = $this->get('/en/url-test');

            expect($response->json('url'))->toContain('/fr');
        });

        it('replaces current locale with new locale in route with parameters', function () {
            // Use the existing post.show route
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/post/{id}', function ($locale, $id) {
                    return response()->json(['url' => URL::withLocale('es')]);
                })->name('post.show');

            $response = $this->get('/en/post/5');

            expect($response->json('url'))->toContain('/es/post/5');
        });

        it('handles URLs without named routes by replacing locale in path', function () {
            $this->service->setCurrentLocale('en');
            $request = Request::create('/en/some/path', 'GET');
            app()->instance('request', $request);

            $url = URL::withLocale('fr');

            expect($url)->toContain('/fr/some/path');
        });
    });

    describe('URL::localeRoute() macro', function () {
        it('generates localized route URL using the service', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/route-test', function () {
                    return response()->json(['url' => URL::localeRoute('post.show', ['id' => 10])]);
                });

            $response = $this->get('/en/route-test');

            expect($response->json('url'))->toContain('/en/post/10');
        });

        it('passes parameters correctly to the service route method', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/route-test2', function () {
                    return response()->json(['url' => URL::localeRoute('user.profile', ['id' => 42])]);
                });

            $response = $this->get('/fr/route-test2');

            expect($response->json('url'))->toContain('/fr/user/42');
        });

        it('respects absolute parameter', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/route-test3', function () {
                    return response()->json([
                        'relative' => URL::localeRoute('home', [], false),
                        'absolute' => URL::localeRoute('home', [], true),
                    ]);
                });

            $response = $this->get('/en/route-test3');

            expect($response->json('relative'))->not->toContain('http')
                ->and($response->json('absolute'))->toContain('http');
        });
    });

    describe('Route::localized() macro', function () {
        it('creates route group with locale prefix', function () {
            Route::localized(function () {
                Route::get('/test', function () {
                    return 'test';
                })->name('test');
            });

            // Test that routes are accessible with locale prefix
            $response = $this->get('/en/test');
            expect($response->status())->toBe(200)
                ->and($response->getContent())->toBe('test');
        });

        it('constrains locale parameter to supported locales', function () {
            Route::localized(function () {
                Route::get('/page', function () {
                    return 'page';
                })->name('page');
            });

            // Valid locale should work
            $response = $this->get('/fr/page');
            expect($response->status())->toBe(200);

            // Invalid locale should not match the route
            $response = $this->get('/invalid/page');
            expect($response->status())->toBe(404);
        });

        it('uses dynamic route key from service configuration', function () {
            // The route key should be used as the parameter name
            Route::localized(function () {
                Route::get('/dynamic', function () {
                    return request()->route()->parameter($this->service->getRouteKey());
                })->name('dynamic');
            });

            $response = $this->get('/es/dynamic');
            expect($response->getContent())->toBe('es');
        });

        it('returns route registrar when no callback provided', function () {
            $registrar = Route::localized();

            expect($registrar)->toBeInstanceOf(\Illuminate\Routing\RouteRegistrar::class);

            // Can chain route definitions
            $registrar->get('/chained', function () {
                return 'chained';
            });

            $response = $this->get('/en/chained');
            expect($response->status())->toBe(200)
                ->and($response->getContent())->toBe('chained');
        });

        it('generates case-insensitive regex for supported locales', function () {
            Route::localized(function () {
                Route::get('/case-test', function () {
                    return 'case-test';
                })->name('case-test');
            });

            // Should match both lowercase and uppercase variants
            $response = $this->get('/EN/case-test');
            expect($response->status())->toBe(200);

            $response = $this->get('/Fr/case-test');
            expect($response->status())->toBe(200);
        });

        it('allows nested route definitions', function () {
            Route::localized(function () {
                Route::prefix('admin')->group(function () {
                    Route::get('/dashboard', function () {
                        return 'admin-dashboard';
                    })->name('admin.dashboard');
                });
            });

            $response = $this->get('/en/admin/dashboard');
            expect($response->status())->toBe(200)
                ->and($response->getContent())->toBe('admin-dashboard');
        });
    });

    describe('Macro integration', function () {
        it('works with multiple macros chained together', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/integration-test', function () {
                    return response()->json([
                        'current_locale' => request()->locale(),
                        'new_locale_url' => URL::withLocale('fr'),
                        'route_url' => URL::localeRoute('post.show', ['id' => 1]),
                    ]);
                });

            $response = $this->get('/en/integration-test');

            expect($response->json('current_locale'))->toBe('en')
                ->and($response->json('new_locale_url'))->toContain('/fr')
                ->and($response->json('route_url'))->toContain('/en/post/1');
        });

        it('macros use the scoped service instance', function () {
            Route::middleware(['localization.from_route'])
                ->get('/{locale}/scoped-test', function () {
                    return response()->json([
                        'locale' => request()->locale(),
                        'route' => URL::localeRoute('home'),
                    ]);
                });

            $response = $this->get('/es/scoped-test');

            expect($response->json('locale'))->toBe('es')
                ->and($response->json('route'))->toContain('/es');
        });
    });
});
