<?php

/**
 * SpaceSettings allows permanent storage of space specific variables.
 * 
 * This is the model class for table "space_setting".
 *
 * The followings are the available columns in table 'space_setting':
 * @property integer $id
 * @property integer $space_id
 * @property string $module_id
 * @property string $name
 * @property string $value
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * 
 * @package humhub.modules_core.space.models
 * @since 0.5
 * @author Luke
 */
class SpaceSetting extends HActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return SpaceSetting the static model class
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
        return 'space_setting';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('space_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('module_id, name', 'length', 'max' => 100),
            array('value', 'length', 'max' => 255),
            array('created_at, updated_at', 'safe'),
        );
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
        $record->value = $value;
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
        $params = array('name' => $name, 'space_id' => $spaceId);

        if ($moduleId != "") {
            $params['module_id'] = $moduleId;
        } else {
            $condition = "module_id IS NULL";
        }

        $record = SpaceSetting::model()->findByAttributes($params, $condition);

        if ($record == null) {
            $record = new SpaceSetting;
            $record->space_id = $spaceId;
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
