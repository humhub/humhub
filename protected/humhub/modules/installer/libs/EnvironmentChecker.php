<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\libs;

use humhub\libs\DynamicConfig;
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
        $assetsPath = Yii::getAlias(Yii::$app->assetManager->basePath);
        if (!is_writable($assetsPath)) {
            print "Error: The assets directory is not writable by the PHP process.";
            exit(1);
        }

        if (!is_writable(Yii::getAlias("@runtime"))) {
            print "Error: The runtime directory is not writable by the PHP process.";
            exit(1);
        }

        $dynamicConfigFile = DynamicConfig::getConfigFilePath();
        if (file_exists($dynamicConfigFile) && !is_writable($dynamicConfigFile)) {
            print "Error: The dynamic configuration (config/dynamic.php) is not writable by the PHP process.";
            exit(1);
        } elseif (!is_writable(dirname($dynamicConfigFile))) {
            print "Error: The dynamic configuration (config/dynamic.php) cannot be created by the PHP process.";
            exit(1);
        }
    }
}
