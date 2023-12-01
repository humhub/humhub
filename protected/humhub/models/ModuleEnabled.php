<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models;

use humhub\libs\BaseSettingsManager;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "module_enabled".
 *
 * @property-read \yii\db\ActiveQuery $tableClassMap
 * @property string $module_id
 */
class ModuleEnabled extends \yii\db\ActiveRecord
{
    public const CACHE_ID_ALL_IDS    = 'enabledModuleInfo';
    public const FAKE_CORE_MODULE_ID = '_CORE_';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'module_enabled';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_id'], 'required'],
            [['module_id'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'module_id' => 'Module ID',
        ];
    }

    public function getTableClassMap(): ActiveQuery
    {
        return $this->hasMany(ClassMap::class, ['module_id' => 'module_id']);
    }

    public function afterDelete()
    {
        Yii::$app->cache->delete(self::CACHE_ID_ALL_IDS);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->cache->delete(self::CACHE_ID_ALL_IDS);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return parent::afterSave($insert, $changedAttributes);
    }

    public static function getRegisteredModuleInfo(string $moduleId): ?object
    {
        return static::getRegisteredModuleOverview()->modules[$moduleId] ?? null;
    }

    public static function getRegisteredModuleOverview(): object
    {
        $registeredModuleInfo = Yii::$app->cache->get(self::CACHE_ID_ALL_IDS);

        if ($registeredModuleInfo !== false) {
            return $registeredModuleInfo;
        }

        if (!BaseSettingsManager::isInstalled()) {
            return (object) [
                'modules' => [],
                'enabled' => [],
                'disabled' => [],
            ];
        }

        $registeredModules = self::find()->all();
        $registeredModules = array_column($registeredModules, null, 'module_id');

        array_walk($registeredModules, static function (&$module) use (&$classMap) {

            $module = (object)[
                'moduleId'       => $module->module_id,
                'isPaused'       => (bool)$module->is_paused,
            ];
        });

        $registeredModuleInfo = (object) [
            'modules' => $registeredModules,
            'enabled' => array_column(array_filter($registeredModules, static fn($module) => !$module->isPaused), 'moduleId'),
            'disabled' => array_column(array_filter($registeredModules, static fn($module) => $module->isPaused), 'moduleId'),
        ];

        Yii::$app->cache->set(self::CACHE_ID_ALL_IDS, (object) get_object_vars($registeredModuleInfo));


        return $registeredModuleInfo;
    }

    public static function getEnabledIds()
    {
        return static::getRegisteredModuleOverview()->enabled;
    }
}
