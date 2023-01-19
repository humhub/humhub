<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector;
use Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector;
use Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector;
use Rector\CodeQuality\Rector\FuncCall\BoolvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\FloatvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector;
use Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector;
use Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector;
use Rector\CodeQuality\Rector\FuncCall\StrvalToTypeCastRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessLastVariableAssignRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
use Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector;
use Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
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
        // PHP_74
        AddLiteralSeparatorToNumberRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class,
    ]);

    $rectorConfig->rules([
        // CODE_QUALITY
        AddPregQuoteDelimiterRector::class,
        ArrayKeyExistsTernaryThenValueToCoalescingRector::class,
        ArrayKeysAndInArrayToArrayKeyExistsRector::class,
        BooleanNotIdenticalToNotIdenticalRector::class,
        BoolvalToTypeCastRector::class,
        CombineIfRector::class,
        ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class,
        FloatvalToTypeCastRector::class,
        InlineArrayReturnAssignRector::class,
        InlineConstructorDefaultToPropertyRector::class,
        InlineIfToExplicitIfRector::class,
        InlineIsAInstanceOfRector::class,
        IntvalToTypeCastRector::class,
        SetTypeToCastRector::class,
        ShortenElseIfRector::class,
        SimplifyArraySearchRector::class,
        SimplifyConditionsRector::class,
        SimplifyDeMorganBinaryRector::class,
        SimplifyForeachToArrayFilterRector::class,
        SimplifyIfElseToTernaryRector::class,
        SimplifyIfReturnBoolRector::class,
        SimplifyUselessLastVariableAssignRector::class,
        SimplifyUselessVariableRector::class,
        StrvalToTypeCastRector::class,
        SwitchNegatedTernaryRector::class,
        UnnecessaryTernaryExpressionRector::class,
        // CODING_STYLE
//        BinarySwitchToIfElseRector::class,
//        CallUserFuncArrayToVariadicRector::class,
//        CallUserFuncToMethodCallRector::class,
//        CountArrayToEmptyArrayComparisonRector::class,
//        DataProviderArrayItemsNewlinedRector::class,
        // DEAD_CODE
    ]);

//    $rectorConfig->importNames();
};
