<?php

declare(strict_types=1);

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\URL;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;
use Josemontano1996\LaravelLocalizationSuite\Services\RedirectorService;

describe('RedirectorService', function () {
    beforeEach(function () {
        $this->redirector = Mockery::mock(Redirector::class);
        $this->service = Mockery::mock(LocalizationServiceContract::class);
        $this->redirectorService = new RedirectorService($this->redirector, $this->service);
    });

    test('route() delegates to localization service and redirector', function () {
        $this->service->shouldReceive('route')->with('home', [])->andReturn('/en/home');
        $this->redirector->shouldReceive('to')->with('/en/home', 302, [])->andReturn(new RedirectResponse('/en/home'));
        $response = $this->redirectorService->route('home');
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en/home');
    });

    test('intended() uses redirector intended url and localization', function () {
        $this->redirector->shouldReceive('getIntendedUrl')->andReturn('/profile');
        $this->service->shouldReceive('route')->with('/profile')->andReturn('/en/profile');
        $this->redirector->shouldReceive('to')->with('/en/profile', 302, [])->andReturn(new RedirectResponse('/en/profile'));
        $response = $this->redirectorService->intended('/profile');
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en/profile');
    });

    test('action() injects locale and calls redirector', function () {
        $this->service->shouldReceive('getRouteKey')->andReturn('locale');
        $this->service->shouldReceive('getCurrentLocale')->andReturn('en');
        $params = ['id' => 1];
        $expectedParams = ['locale' => 'en', 'id' => 1];
        $this->redirector->shouldReceive('action')->with('UserController@show', $expectedParams, 302, [])->andReturn(new RedirectResponse('/en/user/1'));
        $response = $this->redirectorService->action('UserController@show', $params);
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en/user/1');
    });

    test('refresh() redirects to current path with locale', function () {
        $this->service->shouldReceive('route')->with('current')->andReturn('/en/current');
        $this->redirector->shouldReceive('to')->with('/en/current', 302, [])->andReturn(new RedirectResponse('/en/current'));
        // Simulate request()->path() as 'current' via helper and allow setUserResolver
        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('path')->andReturn('current');
        $mockRequest->shouldReceive('setUserResolver')->andReturnNull();
        app()->instance('request', $mockRequest);
        $response = $this->redirectorService->refresh();
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en/current');
    });

    test('signedRoute() and temporarySignedRoute() inject locale and call URL macros', function () {
        $this->service->shouldReceive('getRouteKey')->andReturn('locale');
        $this->service->shouldReceive('getCurrentLocale')->andReturn('en');
        $params = ['id' => 1];
        $expectedParams = ['locale' => 'en', 'id' => 1];
        URL::shouldReceive('signedRoute')->with('verify', $expectedParams, null)->andReturn('/en/verify/1?signature=abc');
        $this->redirector->shouldReceive('to')->with('/en/verify/1?signature=abc', 302, [])->andReturn(new RedirectResponse('/en/verify/1?signature=abc'));
        $response = $this->redirectorService->signedRoute('verify', $params);
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toContain('signature=abc');

        URL::shouldReceive('temporarySignedRoute')->with('verify', 123, $expectedParams)->andReturn('/en/verify/1?signature=def');
        $this->redirector->shouldReceive('to')->with('/en/verify/1?signature=def', 302, [])->andReturn(new RedirectResponse('/en/verify/1?signature=def'));
        $response = $this->redirectorService->temporarySignedRoute('verify', 123, $params);
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toContain('signature=def');
    });

    test('back() calls redirector back with localized fallback', function () {
        $this->service->shouldReceive('route')->with('/')->andReturn('/en');
        $this->redirector->shouldReceive('back')->with(302, [], '/en')->andReturn(new RedirectResponse('/en'));
        $response = $this->redirectorService->back();
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en');
    });

    test('to() calls service route and redirector', function () {
        $this->service->shouldReceive('route')->with('foo')->andReturn('/en/foo');
        $this->redirector->shouldReceive('to')->with('/en/foo', 302, [], null)->andReturn(new RedirectResponse('/en/foo'));
        $response = $this->redirectorService->to('foo');
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en/foo');
    });

    test('away() bypasses localization', function () {
        $this->redirector->shouldReceive('away')->with('https://external.com', 302, [])->andReturn(new RedirectResponse('https://external.com'));
        $response = $this->redirectorService->away('https://external.com');
        expect($response)->toBeInstanceOf(RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('https://external.com');
    });
});
