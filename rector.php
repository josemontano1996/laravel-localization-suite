<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/tests/Integration/runtimes',
    ])

    // 1. tell Rector what PHP version we support
    ->withPhpSets(php84: true)                       
    ->withPreparedSets(deadCode: true)
    ->withCodeQualityLevel(40)
    ->withTypeCoverageLevel(40)
;