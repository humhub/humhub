<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models;

use Yii;

/**
 * This is the model class for table "module_enabled".
 *
 * @property string $module_id
 */
class ModuleEnabled extends \yii\db\ActiveRecord
{
    public const CACHE_ID_ALL_IDS = 'enabledModuleIds';

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
            [['module_id'], 'string', 'max' => 100],
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

    public function afterDelete()
    {
        Yii::$app->cache->delete(self::CACHE_ID_ALL_IDS);

        return parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->cache->delete(self::CACHE_ID_ALL_IDS);

        return parent::afterSave($insert, $changedAttributes);
    }

    public static function getEnabledIds()
    {
        $cache = Yii::$app->cache;
        $enabledModules = $cache->get(self::CACHE_ID_ALL_IDS);
        if ($enabledModules === false) {
            $enabledModules = self::find()->select('module_id')->createCommand()->queryColumn();
            $cache->set(self::CACHE_ID_ALL_IDS, $enabledModules);
        }

        return $enabledModules;
    }
}
