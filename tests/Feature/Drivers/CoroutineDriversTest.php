<?php

declare(strict_types=1);

use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\OpenSwooleDriver;
use Josemontano1996\LaravelLocalizationSuite\Drivers\Localization\SwooleDriver;
use Mockery;

class CoroutineStore
{
    /** @var array<int, ArrayObject> */
    private array $contexts = [];

    public function get(int $id): ArrayObject
    {
        if (! isset($this->contexts[$id])) {
            $this->contexts[$id] = new ArrayObject;
        }

        return $this->contexts[$id];
    }
}

beforeEach(function () {
    Mockery::close();
});

afterEach(function () {
    Mockery::close();
});

describe('OpenSwooleDriver', function () {
    it('stores locale per coroutine context without touching global state using mocks', function () {
        $store = new CoroutineStore;

        $coroutine = Mockery::mock('alias:OpenSwoole\\Coroutine');
        $coroutine->shouldReceive('getCid')->times(4)->andReturn(1, 1, 1, -1);
        $context = $store->get(1);
        $coroutine->shouldReceive('getContext')->times(3)->with(1)->andReturn($context, $context, $context);

        $driver = (new \ReflectionClass(OpenSwooleDriver::class))->newInstanceWithoutConstructor();

        expect($driver->isSafeToMutateGlobalState())->toBeFalse();
        expect($driver->getCurrentLocale())->toBeNull();

        $driver->setCurrentLocale('es');
        expect($driver->getCurrentLocale())->toBe('es');

        expect($driver->getCurrentLocale())->toBeNull();
    });
});

describe('SwooleDriver', function () {
    it('stores locale per coroutine context without touching global state using mocks', function () {
        $store = new CoroutineStore;

        $coroutine = Mockery::mock('alias:Swoole\\Coroutine');
        $coroutine->shouldReceive('getuid')->times(4)->andReturn(1, 1, 1, -1);
        $context = $store->get(1);
        $coroutine->shouldReceive('getContext')->times(3)->with(1)->andReturn($context, $context, $context);

        $driver = (new \ReflectionClass(SwooleDriver::class))->newInstanceWithoutConstructor();

        expect($driver->isSafeToMutateGlobalState())->toBeFalse();
        expect($driver->getCurrentLocale())->toBeNull();

        $driver->setCurrentLocale('fr');
        expect($driver->getCurrentLocale())->toBe('fr');

        expect($driver->getCurrentLocale())->toBeNull();
    });
});
