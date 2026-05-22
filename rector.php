<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/protected/humhub',
    ])
    ->withSkip([
        \Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector::class,
        \Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector::class,
        \Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector::class,
        __DIR__ . '/protected/humhub/config/*',
        __DIR__ . '/protected/humhub/messages/*',
        __DIR__ . '/protected/humhub/modules/*/messages/*',
        __DIR__ . '/protected/humhub/modules/*/config.php',
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withRules([
        \humhub\libs\rector\ForceExplicitNullableParamRector::class,
    ])
    ->withConfiguredRule(
        RenameClassRector::class,
        [
            //'OldNamespace\\OldClass' => 'NewNamespace\\NewClass',
        ],
    );
