<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

// Intentionally no require_once of Module.php: the class must only be loadable
// through the namespace alias registered by ModuleManager::register(). The module
// id ("auth-mismatch") deliberately differs from the namespace, so an alias derived
// from the id alone cannot resolve the module's classes.
return [
    'id' => 'auth-mismatch',
    'class' => 'Some\Name\Space\mismatch\Module',
    'namespace' => 'Some\Name\Space\mismatch',
];
