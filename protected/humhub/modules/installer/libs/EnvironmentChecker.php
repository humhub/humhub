<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\libs;

use Yii;

/**
 * Class EnvironmentChecker
 * @package humhub\modules\installer\libs
 */
class EnvironmentChecker
{
    /**
     * Performs some essential tests on Humhub installations that are not yet fully installed.
     */
    public static function preInstallChecks()
    {
        $assetsPath = realpath(Yii::getAlias(Yii::$app->assetManager->basePath));
        if (!is_writable($assetsPath)) {
            print "Error: The assets directory is not writable by the PHP process.";
            exit(1);
        }

        if (!is_writable(realpath(Yii::getAlias("@runtime")))) {
            print "Error: The runtime directory is not writable by the PHP process.";
            exit(1);
        }

        // The installer writes the database credentials here. Without this the failure surfaces
        // halfway through the wizard as an exception, and the directory it needs moved in 1.20.
        $configPath = dirname(Yii::getAlias(Yii::$app->params['dynamicConfigFile']));
        if (!is_dir($configPath) || !is_writable($configPath)) {
            print "Error: The configuration directory " . $configPath . " is not writable by the PHP process.";
            exit(1);
        }
    }
}
