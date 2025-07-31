<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/protected/humhub',
    ])
    ->withSkip([
        \Rector\Php81\Rector\Array_\FirstClassCallableRector::class,
        __DIR__ . '/protected/humhub/messages/*',
        __DIR__ . '/protected/humhub/modules/*/messages/*',
        __DIR__ . '/protected/humhub/modules/*/config.php',
    ])
    ->withPhpSets(php82:true)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
