<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\Set\ValueObject\SetList;
use Rector\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_74);

    $rectorConfig->paths([
        __DIR__ . '/protected/humhub',
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::PHP_74,
    ]);

    $rectorConfig->skip([
        // Skip CODE_QUALITY rules:
        CompleteDynamicPropertiesRector::class,
        // Skip DEAD_CODE rules:
        RemoveNonExistingVarAnnotationRector::class,
        RemoveUnusedNonEmptyArrayBeforeForeachRector::class,
        RemoveUselessParamTagRector::class,
    ]);

    $rectorConfig->rules([
        ExplicitPublicClassMethodRector::class,
    ]);

//    $rectorConfig->importNames();
};
