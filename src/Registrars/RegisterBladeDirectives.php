<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Registrars;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;

class RegisterBladeDirectives
{
    public static function register(): void
    {
        // @route('name')
        Blade::directive('route', function ($expression): string {
            return "<?php echo localization()->route($expression); ?>";
        });

        // @t('key') - The custom translation directive that respects Context
        Blade::directive('t', function ($expression): string {
            return "<?php echo __($expression, [], localization()->getCurrentLocale()); ?>";
        });

        // @tchoice('key', $count, ['n' => $count]) - Pluralization with context locale
        Blade::directive('tchoice', function ($expression): string {
            return "<?php echo tchoice_ctx($expression); ?>";
        });

        // Blade conditionals for locale matching
        Blade::if('localeIs', function ($code): bool {
            return localization()->getCurrentLocale() === $code;
        });

        // @locale - Output the current locale
        Blade::directive('locale', function (): string {
            return '<?php echo localization()->getCurrentLocale(); ?>';
        });

        // @locales - Iterate over supported locales
        Blade::directive('locales', function ($expression): string {
            return "<?php foreach(localization()->getSupportedLocales() as {$expression}): ?>";
        });

        // @endlocales - Close the locale iteration
        Blade::directive('endlocales', function (): string {
            return '<?php endforeach; ?>';
        });

        // @date($value, $pattern = 'LL') using CarbonImmutable isoFormat with translations
        Blade::directive('date', function ($expression): string {
            $parts = array_map('trim', explode(',', (string) $expression, 2));
            $value = $parts[0] ?? 'now()';
            $pattern = $parts[1] ?? "'LL'";

            return '<?php echo \\Carbon\\CarbonImmutable::parse('.$value.')->locale(localization()->getCurrentLocale())->isoFormat('.$pattern.'); ?>';
        });

        // @datetime($value, $pattern = 'LLL') using CarbonImmutable
        Blade::directive('datetime', function ($expression): string {
            $parts = array_map('trim', explode(',', (string) $expression, 2));
            $value = $parts[0] ?? 'now()';
            $pattern = $parts[1] ?? "'LLL'";

            return '<?php echo \\Carbon\\CarbonImmutable::parse('.$value.')->locale(localization()->getCurrentLocale())->isoFormat('.$pattern.'); ?>';
        });

        // @number($value, $decimals = null)
        Blade::directive('number', function ($expression): string {
            $parts = array_map('trim', explode(',', (string) $expression, 2));
            $value = $parts[0] ?? '0';
            $decimals = $parts[1] ?? null;

            $hasDecimals = $decimals !== null ? 'true' : 'false';
            $decValue = $decimals !== null ? $decimals : '0';

            return '<?php '.
                '$__locale = localization()->getCurrentLocale();'.
                'try {'.
                    '$fmt = new \\NumberFormatter($__locale, \\NumberFormatter::DECIMAL);'.
                    'if ('.$hasDecimals.') {'.
                        '$fmt->setAttribute(\\NumberFormatter::MIN_FRACTION_DIGITS, (int)('.$decimals.'));'.
                        '$fmt->setAttribute(\\NumberFormatter::MAX_FRACTION_DIGITS, (int)('.$decimals.'));'.
                    '}'.
                    '$out = $fmt->format('.$value.');'.
                    'if ($out === false) { throw new \\RuntimeException("Intl format failed"); }'.
                    'echo $out;'.
                '} catch (\\Throwable $e) {'.
                    'echo number_format((float)('.$value.'), (int)('.$decValue.'));'.
                '}'.
            '?>';
        });

        // @currency($value, $currency = 'USD', $decimals = null)
        Blade::directive('currency', function ($expression): string {
            $parts = array_map('trim', explode(',', (string) $expression, 3));
            $value = $parts[0] ?? '0';
            $currency = $parts[1] ?? "'USD'";
            $decimals = $parts[2] ?? null;

            $hasDecimals = $decimals !== null ? 'true' : 'false';
            $decValue = $decimals !== null ? $decimals : '2';

            return '<?php '.
                '$__locale = localization()->getCurrentLocale();'.
                'try {'.
                    '$fmt = new \\NumberFormatter($__locale, \\NumberFormatter::CURRENCY);'.
                    'if ('.$hasDecimals.') {'.
                        '$fmt->setAttribute(\\NumberFormatter::MIN_FRACTION_DIGITS, (int)('.$decimals.'));'.
                        '$fmt->setAttribute(\\NumberFormatter::MAX_FRACTION_DIGITS, (int)('.$decimals.'));'.
                    '}'.
                    '$out = $fmt->formatCurrency((float)('.$value.'), '.$currency.');'.
                    'if ($out === false) { throw new \\RuntimeException("Intl format failed"); }'.
                    'echo $out;'.
                '} catch (\\Throwable $e) {'.
                    'echo number_format((float)('.$value.'), (int)('.$decValue.')) . " " . '.$currency.';'.
                '}'.
            '?>';
        });

        // @percent($value, $decimals = null)
        Blade::directive('percent', function ($expression): string {
            $parts = array_map('trim', explode(',', (string) $expression, 2));
            $value = $parts[0] ?? '0';
            $decimals = $parts[1] ?? null;

            $hasDecimals = $decimals !== null ? 'true' : 'false';
            $decValue = $decimals !== null ? $decimals : '0';

            return '<?php '.
                '$__locale = localization()->getCurrentLocale();'.
                'try {'.
                    '$fmt = new \\NumberFormatter($__locale, \\NumberFormatter::PERCENT);'.
                    'if ('.$hasDecimals.') {'.
                        '$fmt->setAttribute(\\NumberFormatter::MIN_FRACTION_DIGITS, (int)('.$decimals.'));'.
                        '$fmt->setAttribute(\\NumberFormatter::MAX_FRACTION_DIGITS, (int)('.$decimals.'));'.
                    '}'.
                    '$out = $fmt->format('.$value.');'.
                    'if ($out === false) { throw new \\RuntimeException("Intl format failed"); }'.
                    'echo $out;'.
                '} catch (\\Throwable $e) {'.
                    'echo number_format((float)('.$value.') * 100, (int)('.$decValue.')) . "%";'.
                '}'.
            '?>';
        });
    }
}
