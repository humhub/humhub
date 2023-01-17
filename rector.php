<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_74);

    $rectorConfig->paths([
        __DIR__ . '/protected/humhub',
    ]);

    $rectorConfig->sets([
////        SetList::CODE_QUALITY,
////        SetList::CODING_STYLE,
////        SetList::DEAD_CODE,
        SetList::PHP_74,
    ]);

    $rectorConfig->skip([
//        // CODE_QUALITY
//        CompleteDynamicPropertiesRector::class,
//        // CODING_STYLE
//        SymplifyQuoteEscapeRector::class,
//        // DEAD_CODE
//        RemoveNonExistingVarAnnotationRector::class,
//        RemoveUnusedNonEmptyArrayBeforeForeachRector::class,
//        RemoveUselessParamTagRector::class,
        // PHP_74
        AddLiteralSeparatorToNumberRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class,
    ]);

//    $rectorConfig->rules([
//        ExplicitPublicClassMethodRector::class,
//    ]);

//    $rectorConfig->importNames();
};
