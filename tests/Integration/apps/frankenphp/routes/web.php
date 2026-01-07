<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::localized(function () {
    Route::get('/bleed-test-native-laravel', function () {
        $requestedLocale = localization()->getCurrentLocale();

        $sleep = (int) request('sleep', 0);
        if ($sleep > 0) {
            usleep($sleep * 1000); // convert ms to us
        }
        $currentLocale = localization()->getCurrentLocale();

        return response()->json([
            'bleeded' => $requestedLocale !== $currentLocale,
            'context_locale' => $currentLocale,
            'requested_locale' => $requestedLocale,
        ]);
    })->middleware('localization');
});
