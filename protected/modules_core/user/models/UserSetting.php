<?php

/**
 * UserSettings allows permanent storage of user specific variables.
 * 
 * This is the model class for table "user_setting".
 *
 * The followings are the available columns in table 'user_setting':
 * @property integer $id
 * @property integer $user_id
 * @property string $module_id
 * @property string $name
 * @property string $value
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * 
 * @package humhub.modules_core.user.models
 * @since 0.5
 * @author Luke
 */
class UserSetting extends HActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserSetting the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'user_setting';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('user_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('module_id, name', 'length', 'max' => 100),
            array('value', 'length', 'max' => 255),
            array('created_at, updated_at', 'safe'),
        );
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

    public function beforeSave()
    {
        Yii::app()->cache->delete($this->getCacheId());
        RuntimeCache::Remove($this->getCacheId());

        return parent::beforeSave();
    }

    public function beforeDelete()
    {
        Yii::app()->cache->delete($this->getCacheId());
        RuntimeCache::Remove($this->getCacheId());

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
            $userId = Yii::app()->user->id;
        }
        if ($moduleId == "") {
            $moduleId = "core";
        }

        $record = self::GetRecord($userId, $name, $moduleId);
        $record->value = $value;
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
            $userId = Yii::app()->user->id;
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

        // Check if stored in Runtime Cache
        if (RuntimeCache::Get($cacheId) !== false) {
            return RuntimeCache::Get($cacheId);
        }

        // Check if stored in Cache
        $cacheValue = Yii::app()->cache->get($cacheId);
        if ($cacheValue !== false) {
            return $cacheValue;
        }

        $condition = "";
        $params = array('name' => $name, 'user_id' => $userId);
        if ($moduleId != "") {
            $params['module_id'] = $moduleId;
        } else {
            $condition = "module_id IS NULL";
        }

        $record = UserSetting::model()->findByAttributes($params, $condition);

        if ($record == null) {
            $record = new UserSetting;
            $record->user_id = $userId;
            $record->module_id = $moduleId;
            $record->name = $name;
        } else {
            $expireTime = 3600;
            if ($record->name != 'expireTime' && $record->module_id != "cache")
                $expireTime = HSetting::Get('expireTime', 'cache');

            Yii::app()->cache->set($cacheId, $record, $expireTime);
            RuntimeCache::Set($cacheId, $record);
        }

        return $record;
    }

}
