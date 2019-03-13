<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Exception;

/**
 * This is the model class for table "setting".
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 * @property string $module_id
 */
class Setting extends ActiveRecord
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
    public static function get($name, $moduleId = '')
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
    public static function set($name, $value, $moduleId = '')
    {
        list ($name, $moduleId) = self::fixModuleIdAndName($name, $moduleId);
        return self::getModule($moduleId)->settings->set($name, $value);
    }

    /**
     * @deprecated since version 1.1
     */
    public static function setText($name, $value, $moduleId = '')
    {
        self::Set($name, $value, $moduleId);
    }

    /**
     * @deprecated since version 1.1
     */
    public static function getText($name, $moduleId = '')
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
    public static function isFixed($name, $moduleId = '')
    {
        return self::getModule($moduleId)->settings->isFixed($name);
    }

    /**
     * Checks if Humhub is installed
     *
     * @return boolean
     */
    public static function isInstalled()
    {
        return isset(Yii::$app->params['installed']) && Yii::$app->params['installed'] == true;
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
            return ['allowGuestAccess', 'user'];
        } elseif ($name == 'defaultUserGroup' && $moduleId == 'authentication_internal') {
            return ['auth.allowGuestAccess', 'user'];
        } elseif ($name == 'systemEmailAddress' && $moduleId == 'mailing') {
            return ['mailer.systemEmailAddress', 'user'];
        } elseif ($name == 'systemEmailName' && $moduleId == 'mailing') {
            return ['mailer.systemEmailName', 'user'];
        } elseif ($name == 'enabled' && $moduleId == 'proxy') {
            return ['proxy.enabled', 'base'];
        } elseif ($name == 'server' && $moduleId == 'proxy') {
            return ['proxy.server', 'base'];
        } elseif ($name == 'port' && $moduleId == 'proxy') {
            return ['proxy.port', 'base'];
        } elseif ($name == 'user' && $moduleId == 'proxy') {
            return ['proxy.user', 'base'];
        } elseif ($name == 'pass' && $moduleId == 'proxy') {
            return ['proxy.password', 'base'];
        } elseif ($name == 'noproxy' && $moduleId == 'proxy') {
            return ['proxy.noproxy', 'base'];
        }

        return [$name, $moduleId];
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
            throw new Exception('Could not find module: ' . $moduleId);
        }

        return $module;
    }

}
