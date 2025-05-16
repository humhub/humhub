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
    }
}
