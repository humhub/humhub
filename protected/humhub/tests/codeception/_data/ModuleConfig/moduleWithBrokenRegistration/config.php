<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

// Registration of this module always fails: with 'strict' enabled the invalid
// 'events' value makes ModuleManager::register() throw an InvalidConfigException.
// Used to verify that a module which cannot be registered (e.g. a stale module
// during a core upgrade) has its migrations skipped instead of failing the run.
return [
    'id' => 'broken-registration',
    'class' => 'humhub\components\Module',
    'strict' => true,
    'events' => 'not-a-valid-events-configuration',
];
