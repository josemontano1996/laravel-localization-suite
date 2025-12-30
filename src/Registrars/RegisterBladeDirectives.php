<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Registrars;

use Illuminate\Support\Facades\Blade;
use Josemontano1996\LaravelLocalizationSuite\Contracts\LocalizationServiceContract;

class RegisterBladeDirectives
{
    public static function register(): void
    {
        $service = 'app(\\Josemontano1996\\LaravelLocalizationSuite\\Contracts\\LocalizationServiceContract::class)';

        // --- Navigation & Translation ---
        Blade::directive('route', fn ($expr) => "<?php echo {$service}->route($expr); ?>");
        Blade::directive('t', fn ($expr) => "<?php echo {$service}->t($expr); ?>");
        Blade::directive('tchoice', fn ($expr) => "<?php echo {$service}->tchoice($expr); ?>");

        // --- State ---
        Blade::directive('locale', fn () => "<?php echo {$service}->getCurrentLocale(); ?>");

        Blade::if('localeIs', function ($code) {
            return app(LocalizationServiceContract::class)
                ->getCurrentLocale() === $code;
        });

        Blade::directive('locales', function ($expression) use ($service) {
            $variable = trim($expression, '() ');

            return "<?php foreach({$service}->getSupportedLocales() as {$variable}): ?>";
        });

        Blade::directive('endlocales', fn () => '<?php endforeach; ?>');

        // --- Formatting ---
        Blade::directive('number', fn ($expr) => "<?php echo {$service}->formatNumber(...[$expr], style: \NumberFormatter::DECIMAL); ?>");

        Blade::directive('currency', function ($expression) use ($service) {
            return "<?php 
                \$args = [$expression];
                echo {$service}->formatNumber(\$args[0] ?? 0, \NumberFormatter::CURRENCY, [
                    'currency' => \$args[1] ?? 'USD',
                    'decimals' => \$args[2] ?? null
                ]); 
            ?>";
        });

        Blade::directive('percent', fn ($expr) => "<?php echo {$service}->formatNumber(...[$expr], style: \NumberFormatter::PERCENT); ?>");

        // --- Dates ---
        Blade::directive('date', function ($expression) use ($service) {
            return "<?php 
                \$args = [$expression];
                echo \\Carbon\\CarbonImmutable::parse(\$args[0] ?? 'now')
                    ->locale({$service}->getCurrentLocale())
                    ->isoFormat(\$args[1] ?? 'LL'); 
            ?>";
        });

        Blade::directive('datetime', function ($expression) use ($service) {
            return "<?php 
                \$args = [$expression];
                echo \\Carbon\\CarbonImmutable::parse(\$args[0] ?? 'now')
                    ->locale({$service}->getCurrentLocale())
                    ->isoFormat(\$args[1] ?? 'LLL'); 
            ?>";
        });
    }
}
