<?php

namespace humhub\models;

use Yii;
use humhub\libs\DynamicConfig;

/**
 * This is the model class for table "setting".
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 * @property string $value_text
 * @property string $module_id
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
        return 'setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['value_text'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['name', 'module_id'], 'string', 'max' => 100],
            [['value'], 'string', 'max' => 255]
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
            'value_text' => 'Value Text',
            'module_id' => 'Module ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        DynamicConfig::onSettingChange($this);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        DynamicConfig::onSettingChange($this);
    }

    /**
     * Returns a record by name and module id.
     * The result is cached.
     *
     * @param type $name
     * @param type $moduleId
     * @return \humhub\models\HSetting
     */
    private static function GetRecord($name, $moduleId = "")
    {
        $cacheId = 'HSetting_' . $name . '_' . $moduleId;

        #$record = Yii::$app->cache->get($cacheId);
        $record = false;

        if ($record === false) {
            $query = self::find();
            $query->andWhere(['name' => $name]);
            if ($moduleId != "") {
                $query->andWhere(['module_id' => $moduleId]);
            } else {
                $query->andWhere("module_id IS NULL or module_id = ''");
            }

            $record = $query->one();

            // Create empty record
            if ($record === null) {
                $record = new self;
                $record->name = $name;
                $record->module_id = $moduleId;
            }

            Yii::$app->cache->set($cacheId, $record);
        }

        return $record;
    }

    public static function Get($name, $moduleId = "")
    {
        if (self::IsFixed($name, $moduleId)) {
            return self::GetFixedValue($name, $moduleId);
        }

        $configValue = DynamicConfig::getSettingValue($name, $moduleId);
        if ($configValue !== null) {
            return $configValue;
        }

        $record = self::GetRecord($name, $moduleId);
        return $record->value;
    }

    /**
     * Sets a standard Text (max. 255 Characters) entry to the registry
     *
     * @param type $name
     * @param type $value
     * @param type $moduleId
     */
    public static function Set($name, $value, $moduleId = "")
    {
        $record = self::GetRecord($name, $moduleId);

        // Do not store fixed settings
        if (self::IsFixed($name, $moduleId)) {
            $value = self::GetFixedValue($name, $moduleId);
        }

        $record->name = $name;
        $record->value = (string) $value;

        if ($moduleId != "") {
            $record->module_id = $moduleId;
        }

        if ($value == "" && !$record->isNewRecord) {
            $record->delete();
        } else {
            $record->save();
        }
    }

    /**
     * Sets a Text (more than 255 Characters) into the HSetting
     *
     * @param type $name
     * @param type $value
     * @param type $moduleId
     */
    public static function SetText($name, $value, $moduleId = "")
    {
        $record = self::GetRecord($name, $moduleId);

        $record->name = $name;
        $record->value_text = (string) $value;
        if ($moduleId != "")
            $record->module_id = $moduleId;

        $record->save();
    }

    /**
     * Returns a text entry from the registry table
     *
     * @param type $name
     * @param type $moduleId
     * @return type
     */
    public static function GetText($name, $moduleId = "")
    {

        if (DynamicConfig::getSettingValue($name, $moduleId) !== null) {
            return DynamicConfig::getSettingValue($name, $moduleId);
        }

        $record = self::GetRecord($name, $moduleId);
        return $record->value_text;
    }

    /**
     * Determines whether the setting value is fixed in the configuration
     * file or can be changed at runtime.
     * 
     * @param string $name name of setting
     * @param string $moduleId optional module id 
     * @return boolean can be modified via admin cp
     */
    public static function IsFixed($name, $moduleId = "")
    {
        if ($moduleId == "") {
            if (isset(Yii::$app->params['fixed-settings'][$name])) {
                return true;
            }
        } else {
            if (isset(Yii::$app->params['fixed-settings'][$moduleId][$name])) {
                return true;
            }
        }
        return false;
    }

    public static function GetFixedValue($name, $moduleId = "")
    {
        if ($moduleId == "") {
            if (isset(Yii::$app->params['fixed-settings'][$name])) {
                return Yii::$app->params['fixed-settings'][$name];
            }
        } else {
            if (isset(Yii::$app->params['fixed-settings'][$moduleId][$name])) {
                return Yii::$app->params['fixed-settings'][$moduleId][$name];
            }
        }
        return "";
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

}
