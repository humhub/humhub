<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components\behaviors;

use Yii;
use yii\base\Behavior;

/**
 * Settings is a helper for deprecated methods getSetting/setSetting of Space/User Model
 *
 * @deprecated since version 1.1
 * @since 0.6
 * @author luke
 */
class SettingsBehavior extends Behavior
{

    /**
     * Get an content container setting value
     *
     * @deprecated since version 1.1
     * @param String $name of setting
     * @param String $moduleId of setting
     * @param String $default value when no setting exists
     * @return String
     */
    public function getSetting($name, $moduleId = "core", $default = "")
    {
        $value = $this->getModule($moduleId)->settings->contentContainer($this->owner)->get($name);
        if ($value === null) {
            return $default;
        }
        return $value;
    }

    /**
     * set an content container setting value
     *
     * @deprecated since version 1.1
     * @param String $name
     * @param String $value
     * @param String $moduleId
     */
    public function setSetting($name, $value, $moduleId = "")
    {
        $this->getModule($moduleId)->settings->contentContainer($this->owner)->set($name, $value);
    }

    /**
     * Gets correct SettingsManager by module id
     * 
     * @param string $moduleId
     * @return \yii\base\Module
     */
    private function getModule($moduleId)
    {
        $app = null;
        if ($moduleId === '' || $moduleId === 'base') {
            $app = Yii::$app;
        } else {
            $app = Yii::$app->getModule($moduleId);
        }

        if ($app === null) {
            throw new \Exception('Could not find module for setting manager: ' . $moduleId);
        }

        return $app;
    }

}
