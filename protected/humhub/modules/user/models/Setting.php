<?php

namespace humhub\modules\user\models;

use Yii;

/**
 * This is the model class for table "user_setting".
 *
 * @property integer $id
 * @property integer $user_id
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
        return 'user_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['module_id', 'name'], 'string', 'max' => 100],
            [['value'], 'string', 'max' => 255],
            [['user_id', 'module_id', 'name'], 'unique', 'targetAttribute' => ['user_id', 'module_id', 'name'], 'message' => 'The combination of User ID, Module ID and Name has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'module_id' => 'Module ID',
            'name' => 'Name',
            'value' => 'Value',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Returns the Cache ID for this UserSetting Entry
     *
     * @return String
     */
    public function getCacheId()
    {
        return "UserSetting_" . $this->user_id . "_" . $this->name . "_" . $this->module_id;
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
     * Add or update an User setting
     *
     * @param type $userId
     * @param type $name
     * @param type $value
     * @param type $moduleId
     */
    public static function Set($userId, $name, $value, $moduleId = "core")
    {
        if ($userId == "") {
            $userId = Yii::$app->user->id;
        }
        if ($moduleId == "") {
            $moduleId = "core";
        }

        $record = self::GetRecord($userId, $name, $moduleId);
        $record->value = (string) $value;
        $record->name = $name;
        $record->module_id = $moduleId;

        if ($moduleId != "") {
            $record->module_id = $moduleId;
        }

        if ($value == "") { {
                if (!$record->isNewRecord) {
                    $record->delete();
                }
            }
        } else {
            $record->save();
        }
    }

    /**
     * Returns an User Setting
     *
     * @param type $userId
     * @param type $name
     * @param type $moduleId
     * @return type
     */
    public static function Get($userId, $name, $moduleId = "core", $defaultValue = "")
    {
        if ($userId == "") {
            $userId = Yii::$app->user->id;
        }

        $record = self::GetRecord($userId, $name, $moduleId);

        if ($record->isNewRecord) {
            return $defaultValue;
        }

        return $record->value;
    }

    /**
     * Returns a settings record by Name and Module Id
     * The result is cached.
     *
     * @param type $userId
     * @param type $name
     * @param type $moduleId
     * @return \HSetting
     */
    private static function GetRecord($userId, $name, $moduleId = "core")
    {

        if ($moduleId == "") {
            $moduleId = "core";
        }

        $cacheId = 'UserSetting_' . $userId . '_' . $name . '_' . $moduleId;


        // Check if stored in Cache
        $cacheValue = Yii::$app->cache->get($cacheId);
        if ($cacheValue !== false) {
            return $cacheValue;
        }

        $record = self::findOne(['name' => $name, 'user_id' => $userId, 'module_id' => $moduleId]);
        if ($record == null) {
            $record = new self;
            $record->user_id = $userId;
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
