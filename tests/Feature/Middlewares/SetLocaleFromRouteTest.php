<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Middlewares\SetLocaleFromRoute;
use Josemontano1996\LaravelLocalizationSuite\Middlewares\SetLocalizedHeaders;

describe('SetLocaleFromRoute Middleware', function () {

    describe('Named Routes', function () {
        it('handles supported locales in named routes', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/route1', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                })->name('test.route1');

            $response = $this->get('/en/route1');

            expect($response->status())->toBe(200);
            expect($response->json('locale'))->toBe('en');
        });

        it('redirects to fallback locale for unsupported locales in named routes', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/route2', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                })->name('test.route2');

            $response = $this->get('/de/route2', ['Accept-Language' => 'fr']);

            expect($response->status())->toBe(302);
            expect($response->headers->get('location'))->toContain('/fr/route2');
        });

        it('uses config locale as fallback when no Accept-Language header', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/route3', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                })->name('test.route3');

            $response = $this->get('/de/route3');

            expect($response->status())->toBe(302);
            expect($response->headers->get('location'))->toContain('/en/route3');
        });

        it('respects preferred locale from Accept-Language header', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/route4', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                })->name('test.route4');

            $response = $this->get('/de/route4', ['Accept-Language' => 'en,fr;q=0.9']);

            expect($response->status())->toBe(302);
            expect($response->headers->get('location'))->toContain('/en/route4');
        });

        it('preserves route parameters on redirect', function () {
            Route::middleware([SetLocaleFromRoute::class])
                ->get('/{locale}/post/{id}', function () {
                    return 'post';
                })->name('test.post');

            $response = $this->get('/de/post/123', ['Accept-Language' => 'en']);

            expect($response->status())->toBe(302);
            $location = $response->headers->get('location');
            expect($location)->toContain('/en/post/123');
        });
    });

    describe('Unnamed Routes', function () {
        it('handles supported locales in unnamed routes', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/unnamed1', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/en/unnamed1');

            expect($response->status())->toBe(200);
            expect($response->json('locale'))->toBe('en');
        });

        it('redirects to path for unsupported locales in unnamed routes', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/unnamed2', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/de/unnamed2', ['Accept-Language' => 'fr']);

            expect($response->status())->toBe(302);
            expect($response->headers->get('location'))->toContain('/fr/unnamed2');
        });

        it('uses config locale as fallback for unnamed routes', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/unnamed3', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/de/unnamed3');

            expect($response->status())->toBe(302);
            expect($response->headers->get('location'))->toContain('/en/unnamed3');
        });

        it('respects preferred locale for unnamed routes', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/unnamed4', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/de/unnamed4', ['Accept-Language' => 'en,fr;q=0.9']);

            expect($response->status())->toBe(302);
            expect($response->headers->get('location'))->toContain('/en/unnamed4');
        });
    });

    describe('Routes without locale parameter', function () {
        it('uses preferred locale when no locale in route', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/nolocale1', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/nolocale1', ['Accept-Language' => 'fr']);
            expect($response->status())->toBe(200);
            expect($response->json('locale'))->toBe('fr');
        });

        it('uses config locale as fallback when no Accept-Language header', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/nolocale2', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/nolocale2');

            expect($response->status())->toBe(200);
            expect($response->json('locale'))->toBe('en');
        });

        it('uses first supported locale from Accept-Language header', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/nolocale3', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/nolocale3', ['Accept-Language' => 'de,en;q=0.9,fr;q=0.8']);

            expect($response->status())->toBe(200);
            expect($response->json('locale'))->toBe('en');
        });
    });

    describe('Service locale state', function () {
        it('sets correct locale in service during request', function () {
            Route::middleware([SetLocaleFromRoute::class, SetLocalizedHeaders::class])
                ->get('/{locale}/statetest', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/en/statetest');

            expect($response->json('locale'))->toBe('en');
        });
    });

    describe('Edge cases', function () {
        it('handles empty locale parameter', function () {
            // Route with optional locale - when locale is empty/null, middleware should use Accept-Language
            Route::middleware([SetLocaleFromRoute::class])
                ->get('/optional-test/{locale?}', function ($locale = null) {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/optional-test', ['Accept-Language' => 'fr']);

            expect($response->status())->toBe(200);
            expect($response->json('locale'))->toBe('fr');
        });

        it('works with multiple route parameters', function () {
            Route::middleware([SetLocaleFromRoute::class])
                ->get('/{locale}/user/{id}/post/{postId}', function () {
                    $service = app(LocalizationServiceContract::class);

                    return response()->json(['locale' => $service->getCurrentLocale()]);
                });

            $response = $this->get('/en/user/1/post/5');

            expect($response->status())->toBe(200);
            expect($response->json('locale'))->toBe('en');
        });

        it('handles unsupported locale with multiple parameters', function () {
            Route::middleware([SetLocaleFromRoute::class])
                ->get('/{locale}/profile/{id}/settings/{section}', function () {
                    return 'ok';
                });

            $response = $this->get('/de/profile/1/settings/privacy', ['Accept-Language' => 'fr']);

            expect($response->status())->toBe(302);
            $location = $response->headers->get('location');
            expect($location)->toContain('/fr/profile/1/settings/privacy');
        });
    });

});
