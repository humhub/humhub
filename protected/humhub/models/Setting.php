<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models;

use humhub\components\SettingActiveRecord;
use humhub\libs\BaseSettingsManager;
use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "setting".
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property string $module_id
 */
class Setting extends SettingActiveRecord
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
            ['value', 'safe'],
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
     *
     * @param string $name
     * @param string $moduleId
     *
     * @return string the settings value
     */
    public static function get($name, $moduleId = '')
    {
        list($name, $moduleId) = self::fixModuleIdAndName($name, $moduleId);
        return self::getModule($moduleId)->settings->get($name);
    }

    /**
     * Sets settings value
     *
     * @deprecated since version 1.1
     *
     * @param string $name
     * @param string $value
     * @param string $moduleId
     */
    public static function set($name, $value, $moduleId = '')
    {
        list($name, $moduleId) = self::fixModuleIdAndName($name, $moduleId);
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
     *
     * @param string $name
     * @param string $moduleId
     *
     * @return bool
     */
    public static function isFixed($name, $moduleId = '')
    {
        return self::getModule($moduleId)->settings->isFixed($name);
    }

    /**
     * Checks if Humhub is installed
     *
     * @return bool
     * @deprecated since v1.16; use Yii::$app->isInstalled()
     * @see Yii::$app->isInstalled()
     */
    public static function isInstalled()
    {
        return Yii::$app->isInstalled();
    }

    /**
     * Temporary for 1.1 migration
     *
     * @deprecated since version 1.1
     *
     * @param string $name
     * @param string $moduleId
     */
    public static function fixModuleIdAndName($name, $moduleId)
    {
        static $translation = [
            'authentication_internal' => [
                'allowGuestAccess' => ['allowGuestAccess', 'user'],
                'defaultUserGroup' => ['auth.allowGuestAccess', 'user'],
            ],
            'mailing' => [
                'systemEmailAddress' => ['mailer.systemEmailAddress', 'user'],
                'mailing' => ['mailer.systemEmailName', 'user'],
                'systemEmailReplyTo' => ['mailer.systemEmailReplyTo', 'user'],
            ],
            'proxy' => [
                'enabled' => ['proxy.enabled', 'base'],
                'server' => ['proxy.server', 'base'],
                'port' => ['proxy.port', 'base'],
                'user' => ['proxy.user', 'base'],
                'pass' => ['proxy.password', 'base'],
                'noproxy' => ['proxy.noproxy', 'base'],
            ],
        ];

        return $translation[$moduleId][$name] ?? [$name, $moduleId];
    }

    /**
     * Temporary for 1.1 migration
     *
     * @deprecated since version 1.1
     *
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
