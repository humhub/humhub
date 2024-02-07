<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude([
        'messages/',
    ])
    ->in([
        'protected/humhub/modules',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
