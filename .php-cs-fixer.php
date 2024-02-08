<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude([
        'messages/',
        'views/',
    ])
    ->in([
        'protected/humhub/modules',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        'phpdoc_scalar' => true,
        'cast_spaces' => false,
        'single_line_empty_body' => false,
    ])
    ->setFinder($finder);

