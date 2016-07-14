<?php

namespace humhub\modules\space\models;

use Yii;

/**
 * This is the model class for table "space_setting".
 *
 * @property integer $id
 * @property integer $space_id
 * @property string $module_id
 * @property string $name
 * @property string $value
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Setting extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['space_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['module_id', 'name'], 'string', 'max' => 100],
            [['value'], 'string', 'max' => 255],
            [['space_id', 'module_id', 'name'], 'unique', 'targetAttribute' => ['space_id', 'module_id', 'name'], 'message' => 'The combination of Space ID, Module ID and Name has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'space_id' => 'Space ID',
            'module_id' => 'Module ID',
            'name' => Yii::t('SpaceModule.models_Setting', 'Name'),
            'value' => Yii::t('SpaceModule.models_Setting', 'Value'),
            'created_at' => Yii::t('SpaceModule.models_Setting', 'Created At'),
            'created_by' => Yii::t('SpaceModule.models_Setting', 'Created By'),
            'updated_at' => Yii::t('SpaceModule.models_Setting', 'Updated At'),
            'updated_by' => Yii::t('SpaceModule.models_Setting', 'Updated by'),
        ];
    }

    /**
     * Returns the Cache ID for this SpaceSetting Entry
     *
     * @return String
     */
    public function getCacheId()
    {
        return "SpaceSetting_" . $this->space_id . "_" . $this->name . "_" . $this->module_id;
    }

    public function beforeSave($insert)
    {
        Yii::$app->cache->delete($this->getCacheId());
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        Yii::$app->cache->delete($this->getCacheId());
        return parent::beforeDelete();
    }

    /**
     * Add or update an Space setting
     *
     * @param type $spaceId
     * @param type $name
     * @param type $value
     * @param type $moduleId
     */
    public static function Set($spaceId, $name, $value, $moduleId = "core")
    {

        if ($moduleId == "") {
            $moduleId = "core";
        }

        $record = self::GetRecord($spaceId, $name, $moduleId);
        $record->value = (string) $value;
        $record->name = $name;
        $record->module_id = $moduleId;
        $record->module_id = $moduleId;

        if ($value == "") {
            if (!$record->isNewRecord) {
                $record->delete();
            }
        } else {
            $record->save();
        }
    }

    /**
     * Returns an Space Setting
     *
     * @param Stringn $spaceId
     * @param Strign $name
     * @param Strign $moduleId
     * @param String $defaultValue
     *
     * @return type
     */
    public static function Get($spaceId, $name, $moduleId = "core", $defaultValue = "")
    {
        $record = self::GetRecord($spaceId, $name, $moduleId);

        if ($record->isNewRecord) {
            return $defaultValue;
        }

        return $record->value;
    }

    /**
     * Returns a settings record by Name and Module Id
     * The result is cached.
     *
     * @param type $spaceId
     * @param type $name
     * @param type $moduleId
     * @return \HSetting
     */
    private static function GetRecord($spaceId, $name, $moduleId = "core")
    {

        if ($moduleId == "") {
            $moduleId = "core";
        }

        $cacheId = 'SpaceSetting_' . $spaceId . '_' . $name . '_' . $moduleId;

        // Check if stored in Cache
        $cacheValue = Yii::$app->cache->get($cacheId);
        if ($cacheValue !== false) {
            return $cacheValue;
        }

        $record = self::findOne([
                    'name' => $name,
                    'space_id' => $spaceId,
                    'module_id' => $moduleId
        ]);

        if ($record == null) {
            $record = new self;
            $record->space_id = $spaceId;
            $record->module_id = $moduleId;
            $record->name = $name;
        } else {
            $expireTime = 3600;
            if ($record->name != 'expireTime' && $record->module_id != "cache")
                $expireTime = \humhub\models\Setting::Get('expireTime', 'cache');

            Yii::$app->cache->set($cacheId, $record, $expireTime);
        }

        return $record;
    }

}
