<?php

declare(strict_types = 1);

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/ACP3',
    ])
    ->withParallel()
    ->withCache(__DIR__ . '/.rector-cache')
    ->withPhpSets()
    ->withPHPStanConfigs([__DIR__ . '/phpstan.neon.dist']);
