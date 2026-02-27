<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelLocalizationSuite\Drivers\Localization {
    /**
     * Test-only switches to control constructor checks without requiring real extensions.
     */
    class ConstructorSwitch
    {
        public static array $extension = [
            'openswoole' => true,
            'swoole' => true,
        ];

        public static array $classExists = [];
    }

    function extension_loaded(string $name): bool
    {
        if (array_key_exists($name, ConstructorSwitch::$extension)) {
            return ConstructorSwitch::$extension[$name];
        }

        return \extension_loaded($name);
    }

    function class_exists(string $name, bool $autoload = true): bool
    {
        if (array_key_exists($name, ConstructorSwitch::$classExists)) {
            return ConstructorSwitch::$classExists[$name];
        }

        return \class_exists($name, $autoload);
    }
}

namespace {

    use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\ConstructorSwitch;
    use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\OpenSwooleDriver;
    use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\SwooleDriver;
    use Josemontano1996\LaravelLocalizationSuite\Exceptions\OpenSwooleDriverException;
    use Josemontano1996\LaravelLocalizationSuite\Exceptions\SwooleDriverException;

    beforeEach(function (): void {
        ConstructorSwitch::$extension = ['openswoole' => true, 'swoole' => true];
        ConstructorSwitch::$classExists = [];
    });

    afterEach(function (): void {
        ConstructorSwitch::$extension = ['openswoole' => true, 'swoole' => true];
        ConstructorSwitch::$classExists = [];
    });

    describe('OpenSwooleDriver constructor', function (): void {
        it('throws when the openswoole extension is missing', function (): void {
            ConstructorSwitch::$extension['openswoole'] = false;

            expect(fn (): \Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\OpenSwooleDriver => new OpenSwooleDriver)
                ->toThrow(OpenSwooleDriverException::class, 'The "openswoole" PHP extension is required to use the OpenSwooleDriver.');
        });

        it('throws when coroutine support is missing', function (): void {
            ConstructorSwitch::$extension['openswoole'] = true;
            ConstructorSwitch::$classExists[OpenSwoole\Coroutine::class] = false;

            expect(fn (): \Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\OpenSwooleDriver => new OpenSwooleDriver)
                ->toThrow(OpenSwooleDriverException::class, 'OpenSwoole Coroutine support is not available or the class does not exist.');
        });
    });

    describe('SwooleDriver constructor', function (): void {
        it('throws when the swoole extension is missing', function (): void {
            ConstructorSwitch::$extension['swoole'] = false;

            expect(fn (): \Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\SwooleDriver => new SwooleDriver)
                ->toThrow(SwooleDriverException::class, 'The SwooleDriver requires the "swoole" PHP extension to be installed and enabled.');
        });

        it('throws when coroutine support is missing', function (): void {
            ConstructorSwitch::$extension['swoole'] = true;
            ConstructorSwitch::$classExists[Swoole\Coroutine::class] = false;

            expect(fn (): \Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\SwooleDriver => new SwooleDriver)
                ->toThrow(SwooleDriverException::class, 'The SwooleDriver requires the "swoole" PHP extension to be installed and enabled.');
        });
    });

}
