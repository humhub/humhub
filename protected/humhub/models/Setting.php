<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models;

use Yii;

/**
 * This is the model class for table "setting".
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 * @property string $module_id
 */
class Setting extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'module_id'], 'required'],
            ['value', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'value' => 'Value',
            'module_id' => 'Module ID',
        ];
    }

    /**
     * Returns settings value
     *
     * @deprecated since version 1.1
     * @param string $name
     * @param string $moduleId
     * @return string the settings value
     */
    public static function Get($name, $moduleId = "")
    {
        list ($name, $moduleId) = self::fixModuleIdAndName($name, $moduleId);
        return self::getModule($moduleId)->settings->get($name);
    }

    /**
     * Sets settings value
     *
     * @deprecated since version 1.1
     * @param string $name
     * @param string $value
     * @param string $moduleId
     */
    public static function Set($name, $value, $moduleId = "")
    {
        list ($name, $moduleId) = self::fixModuleIdAndName($name, $moduleId);
        return self::getModule($moduleId)->settings->set($name, $value);
    }

    /**
     * @deprecated since version 1.1
     */
    public static function SetText($name, $value, $moduleId = "")
    {
        self::Set($name, $value, $moduleId);
    }

    /**
     * @deprecated since version 1.1
     */
    public static function GetText($name, $moduleId = "")
    {
        return self::Get($name, $moduleId);
    }

    /**
     * Checks this setting is fixed
     *
     * @deprecated since version 1.1
     * @see \humhub\libs\BaseSettingsManager::isFixed
     * @param string $name
     * @param string $moduleId
     * @return boolean
     */
    public static function IsFixed($name, $moduleId = "")
    {
        return self::getModule($moduleId)->settings->isFixed($name);
    }

    /**
     * Checks if initial data like settings, groups are installed.
     *
     * @return Boolean Is Installed
     */
    public static function isInstalled()
    {
        if (isset(Yii::$app->params['installed']) && Yii::$app->params['installed']) {
            return true;
        }

        return false;
    }

    /**
     * Temporary for 1.1 migration
     *
     * @deprecated since version 1.1
     * @param string $name
     * @param string $moduleId
     */
    public static function fixModuleIdAndName($name, $moduleId)
    {
        if ($name == 'allowGuestAccess' && $moduleId == 'authentication_internal') {
            return array('allowGuestAccess', 'user');
        } elseif ($name == 'defaultUserGroup' && $moduleId == 'authentication_internal') {
            return array('auth.allowGuestAccess', 'user');
        } elseif ($name == 'enabled' && $moduleId == 'authentication_ldap') {
            return array('auth.ldap.enabled', 'user');
        } elseif ($name == 'enabled' && $moduleId == 'authentication_ldap') {
            return array('auth.ldap.enabled', 'user');
        } elseif ($name == 'systemEmailAddress' && $moduleId == 'mailing') {
            return array('mailer.systemEmailAddress', 'user');
        } elseif ($name == 'systemEmailName' && $moduleId == 'mailing') {
            return array('mailer.systemEmailName', 'user');
        } elseif ($name == 'enabled' && $moduleId == 'proxy') {
            return array('proxy.enabled', 'base');
        } elseif ($name == 'server' && $moduleId == 'proxy') {
            return array('proxy.server', 'base');
        } elseif ($name == 'port' && $moduleId == 'proxy') {
            return array('proxy.port', 'base');
        } elseif ($name == 'user' && $moduleId == 'proxy') {
            return array('proxy.user', 'base');
        } elseif ($name == 'pass' && $moduleId == 'proxy') {
            return array('proxy.password', 'base');
        } elseif ($name == 'noproxy' && $moduleId == 'proxy') {
            return array('proxy.noproxy', 'base');
        }

        return array($name, $moduleId);
    }

    /**
     * Temporary for 1.1 migration
     *
     * @deprecated since version 1.1
     * @param string $name
     * @param string $moduleId
     */
    public static function getModule($moduleId)
    {
        $module = null;

        if ($moduleId === '' || $moduleId === 'base') {
            $module = Yii::$app;
        } else {
            $module = Yii::$app->getModule($moduleId);
        }
        if ($module === null) {
            throw new \yii\base\Exception("Could not find module: " . $moduleId);
        }

        return $module;
    }

}
