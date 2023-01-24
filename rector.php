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
        SetList::PHP_74,
    ]);

    $rectorConfig->skip([
        // Skip files:
        __DIR__ . '/protected/**/messages/**/*',
        __DIR__ . '/protected/**/vendor/**/*',
        __DIR__ . '/protected/**/node_modules/**/*',
        // PHP_74
        AddLiteralSeparatorToNumberRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class,
    ]);

    $rectorConfig->rules([
        // CODE_QUALITY
        \Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector::class,
        \Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector::class,
        \Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector::class,
        \Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector::class,
        \Rector\CodeQuality\Rector\FuncCall\BoolvalToTypeCastRector::class,
        \Rector\CodeQuality\Rector\If_\CombineIfRector::class,
        \Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class,
        \Rector\CodeQuality\Rector\FuncCall\FloatvalToTypeCastRector::class,
        \Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector::class,
        \Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector::class,
        \Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector::class,
        \Rector\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector::class,
        \Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector::class,
        \Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector::class,
        \Rector\CodeQuality\Rector\If_\ShortenElseIfRector::class,
        \Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector::class,
        \Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector::class,
        \Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector::class,
        \Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector::class,
        \Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector::class,
        \Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector::class,
        \Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessLastVariableAssignRector::class,
        \Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector::class,
        \Rector\CodeQuality\Rector\FuncCall\StrvalToTypeCastRector::class,
        \Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector::class,
        \Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector::class,
        // CODING_STYLE
        \Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector::class,
        \Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector::class,
        \Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector::class,
        \Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector::class,
        \Rector\CodingStyle\Rector\ClassMethod\DataProviderArrayItemsNewlinedRector::class,
        \Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector::class,
        \Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector::class,
        \Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector::class,
        \Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector::class,
        // DEAD_CODE
    ]);

//    $rectorConfig->importNames();
};
