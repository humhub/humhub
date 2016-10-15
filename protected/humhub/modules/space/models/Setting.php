<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\models;

use Yii;


/**
 * Space settings compatiblity layer class
 * 
 * @deprecated since version 1.1
 * @see \humhub\modules\content\components\ContentContainerSettingsManager
 */
class Setting
{

    /**
     * Sets a space setting
     * 
     * @see \humhub\modules\content\components\ContentContainerSettingsManager::set
     * @param type $spaceId
     * @param type $name
     * @param type $value
     * @param type $moduleId
     */
    public static function Set($spaceId, $name, $value, $moduleId = "")
    {
        $user = Space::findOne(['id' => $spaceId]);
        self::getModule($moduleId)->settings->contentContainer($user)->set($name, $value);
    }

    /**
     * Gets a space setting
     * 
     * @see \humhub\modules\content\components\ContentContainerSettingsManager::get
     * @param int $spaceId
     * @param string $name
     * @param string $moduleId
     * @param string $defaultValue
     * @return string
     */
    public static function Get($space, $name, $moduleId = "", $defaultValue = "")
    {
        $user = Space::findOne(['id' => $space]);
        $value = self::getModule($moduleId)->settings->contentContainer($user)->get($name);
        if ($value === null) {
            return $defaultValue;
        }
        return $value;
    }

    /**
     * Gets correct SettingsManager by module id
     * 
     * @param string $moduleId
     * @return \yii\base\Module
     */
    private static function getModule($moduleId)
    {
        $app = null;
        if ($moduleId === '' || $moduleId === 'base' || $moduleId === 'core') {
            $app = Yii::$app;
        } else {
            $app = Yii::$app->getModule($moduleId);
        }
        return $app;
    }

}
