<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Registrars;

use Illuminate\Support\Facades\Blade;
use Josemontano1996\LaravelLocalizationSuite\Facades\Localization;

class RegisterBladeDirectives
{
    public static function register(): void
    {
        $facade = '\\'.Localization::class;

        // --- Navigation & Translation ---
        Blade::directive('route', fn ($expr) => "<?php echo {$facade}::route($expr); ?>");
        Blade::directive('t', fn ($expr) => "<?php echo {$facade}::t($expr); ?>");
        Blade::directive('tchoice', fn ($expr) => "<?php echo {$facade}::tchoice($expr); ?>");

        // --- State ---
        Blade::directive('locale', fn () => "<?php echo {$facade}::getCurrentLocale(); ?>");

        Blade::if('localeIs', function ($code) {
            return Localization::getCurrentLocale() === $code;
        });
        
        Blade::directive('locales', function ($expression) use ($facade) {
            $variable = trim($expression, '() ');

            return "<?php foreach({$facade}::getSupportedLocales() as {$variable}): ?>";
        });

        Blade::directive('endlocales', fn () => '<?php endforeach; ?>');

        // --- Formatting ---
        Blade::directive('number', fn ($expr) => "<?php echo {$facade}::formatNumber(...[$expr], style: \NumberFormatter::DECIMAL); ?>");

        Blade::directive('currency', function ($expression) use ($facade) {
            return "<?php 
                \$args = [$expression];
                echo {$facade}::formatNumber(\$args[0] ?? 0, \NumberFormatter::CURRENCY, [
                    'currency' => \$args[1] ?? 'USD',
                    'decimals' => \$args[2] ?? null
                ]); 
            ?>";
        });

        Blade::directive('percent', fn ($expr) => "<?php echo {$facade}::formatNumber(...[$expr], style: \NumberFormatter::PERCENT); ?>");

        // --- Dates ---
        Blade::directive('date', function ($expression) use ($facade) {
            return "<?php 
                \$args = [$expression];
                echo \\Carbon\\CarbonImmutable::parse(\$args[0] ?? 'now')
                    ->locale({$facade}::getCurrentLocale())
                    ->isoFormat(\$args[1] ?? 'LL'); 
            ?>";
        });

        Blade::directive('datetime', function ($expression) use ($facade) {
            return "<?php 
                \$args = [$expression];
                echo \\Carbon\\CarbonImmutable::parse(\$args[0] ?? 'now')
                    ->locale({$facade}::getCurrentLocale())
                    ->isoFormat(\$args[1] ?? 'LLL'); 
            ?>";
        });
    }
}
