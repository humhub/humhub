<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/protected/humhub',
    ])
    ->withSkip([
        __DIR__ . '/protected/humhub/messages/*',
        __DIR__ . '/protected/humhub/modules/*/messages/*',
    ])
    ->withPhpSets(php82:true)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
