<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\console;

/**
 * Description of Application
 *
 * @author luke
 */
class Application extends \yii\console\Application
{

    /**
     * Returns the configuration of the built-in commands.
     * @return array the configuration of the built-in commands.
     */
    public function coreCommands()
    {
        return [
            'message' => 'yii\console\controllers\MessageController',
            'help' => 'yii\console\controllers\HelpController',
            'cache' => 'yii\console\controllers\CacheController',
            'asset' => 'yii\console\controllers\AssetController',
            'fixture' => 'yii\console\controllers\FixtureController',
        ];
    }

}
