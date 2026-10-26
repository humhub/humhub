<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude([
        'messages/',
        'views/',
    ])
    ->notPath('protected/humhub/config/assets-prod.php')
    ->in([
        'protected/humhub',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        'phpdoc_scalar' => true,
        'cast_spaces' => false,
        'single_line_empty_body' => false,
        'phpdoc_var_annotation_correct_order' => true,
    ])
    ->setFinder($finder);
