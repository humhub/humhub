<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude([
        'messages/',
    ])
    ->in([
        'protected/humhub/modules/ldap',
        'protected/humhub/modules/notification',
        'protected/humhub/modules/dashboard',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
