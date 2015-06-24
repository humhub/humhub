<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * This is the model class for table "settings" and is responsible for system
 * wide settings of modules.
 *
 * Only use this for settings and not for general value storage proposes. 
 *
 * Also modules can use this to store e.g. configuration options.
 *
 * Settings in configuration file at "params -> HSettingsFixed" are not 
 * changeable.
 * 
 * The followings are the available columns in table 'registry':
 * @property int $id
 * @property string $name
 * @property string $value
 * @property string $value_text
 * @property string $module_id
 *
 * @package humhub.models
 * @since 0.5
 */
class HSetting extends HActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return HSetting the static model class
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
        return 'setting';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'required'),
            array('value', 'length', 'max' => 100),
            array('value_text', 'safe'),
            array('name, module_id', 'length', 'max' => 100),
        );
    }

    /**
     * Returns a registry record by Name and Module Id
     * The result is cached.
     *
     * @param type $name
     * @param type $moduleId
     * @return \HSetting
     */
    private static function GetRecord($name, $moduleId = "")
    {

        $cacheId = 'HSetting_' . $name . '_' . $moduleId;

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
        $params = array('name' => $name);

        if ($moduleId != "") {
            $params['module_id'] = $moduleId;
        } else {
            $condition = "module_id IS NULL or module_id = ''";
        }

        $record = HSetting::model()->findByAttributes($params, $condition);

        if ($record == null) {
            $record = new HSetting;
            $record->name = $name;
            $record->module_id = $moduleId;
        }

        $expireTime = 3600;
        if ($record->name != 'expireTime' && $record->module_id != "cache")
            $expireTime = HSetting::Get('expireTime', 'cache');

        Yii::app()->cache->set($cacheId, $record, $expireTime);
        RuntimeCache::Set($cacheId, $record);

        return $record;
    }

    /**
     * Returns a standard registry entry (max. 255 characters) from database
     *
     * @param type $name
     * @param type $moduleId
     * @return type
     */
    public static function Get($name, $moduleId = "")
    {

        if (self::IsFixed($name, $moduleId)) {
            if ($moduleId == "") {
                return Yii::app()->params['HSettingFixed'][$name];
            } else {
                return Yii::app()->params['HSettingFixed'][$moduleId][$name];
            }
        }

        $record = self::GetRecord($name, $moduleId);
        return $record->value;
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

        $record = self::GetRecord($name, $moduleId);
        return $record->value_text;
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

        if (self::IsFixed($name, $moduleId)) {
            $value = self::Get($name, $moduleId);
        }

        $record->name = $name;
        $record->value = $value;
        if ($moduleId != "")
            $record->module_id = $moduleId;

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
        $record->value_text = $value;
        if ($moduleId != "")
            $record->module_id = $moduleId;

        $record->save();
    }

    /**
     * Determines whether the setting value is fixed in the configuration
     * file or can be changed at runtime.
     * 
     * @param type $name
     * @return boolean
     */
    public static function IsFixed($name, $moduleId = "")
    {

        if ($moduleId == "") {
            if (isset(Yii::app()->params['HSettingFixed'][$name])) {
                return true;
            }
        } else {
            if (isset(Yii::app()->params['HSettingFixed'][$moduleId][$name])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the Cache ID for this HSetting Entry
     *
     * @return String
     */
    public function getCacheId()
    {
        return "HSetting_" . $this->name . "_" . $this->module_id;
    }

    /**
     * clears cache
     * @return void
     */
    public function clearCache()
    {
        Yii::app()->cache->delete($this->getCacheId());
        RuntimeCache::Remove($this->getCacheId());
    }

    public function beforeSave()
    {

        $this->clearCache();

        if ($this->module_id === "") {
            $this->module_id = new CDbExpression('NULL');
        }

        if ($this->hasAttribute('created_by') && empty($this->created_by))
            $this->created_by = 0;

        if ($this->hasAttribute('updated_by') && empty($this->updated_by))
            $this->updated_by = 0;

        if ($this->hasAttribute('updated_at') && empty($this->updated_at))
            $this->updated_at = new CDbExpression('NOW()');

        return parent::beforeSave();
    }

    /**
     * After delete check if its required to rewrite configuration file
     */
    public function afterDelete()
    {
        $this->clearCache();

        parent::afterDelete();

        // Only rewrite static configuration file when necessary
        if ($this->module_id != 'mailing' &&
                $this->module_id != 'cache' &&
                $this->name != 'name' &&
                $this->name != 'theme' && 
                $this->name != 'authentication_internal'
        ) {
            return;
        }

        self::rewriteConfiguration();
    }

    /**
     * After saving check if its required to rewrite the configuration file.
     * 
     * @todo Find better way to detect when we need to rewrite the local config
     */
    public function afterSave()
    {

        parent::afterSave();

        // Only rewrite static configuration file when necessary
        if ($this->module_id != 'mailing' &&
                $this->module_id != 'cache' &&
                $this->name != 'name' &&
                $this->name != 'defaultLanguage' &&
                $this->name != 'theme' &&
                $this->module_id != 'authentication_internal'
        ) {
            return;
        }

        self::rewriteConfiguration();
    }

    /**
     * Rewrites the configuration file
     */
    public static function rewriteConfiguration()
    {

        // Get Current Configuration
        $config = HSetting::getConfiguration();

        // Add Application Name to Configuration
        $config['name'] = HSetting::Get('name');

        // Add Default language
        $defaultLanguage = HSetting::Get('defaultLanguage');
        if ($defaultLanguage !== null && $defaultLanguage != "") {
            $config['language'] = HSetting::Get('defaultLanguage');
        } else {
            $config['language'] = Yii::app()->getLanguage();
        }

        // Add Caching
        $cacheClass = HSetting::Get('type', 'cache');
        if (!$cacheClass) {
            $cacheClass = "CDummyCache";
        }
        $config['components']['cache'] = array(
            'class' => $cacheClass,
        );
		
        // Add User settings
        $config['components']['user'] = array( );
        if (HSetting::Get('defaultUserIdleTimeoutSec', 'authentication_internal')) {
        	$config['components']['user']['authTimeout'] = HSetting::Get('defaultUserIdleTimeoutSec', 'authentication_internal');
        }
        
        // Install Mail Component
        $mail = array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => HSetting::Get('transportType', 'mailing'),
            'viewPath' => 'application.views.mail',
            'logging' => true,
            'dryRun' => false,
        );
        if (HSetting::Get('transportType', 'mailing') == 'smtp') {

            $mail['transportOptions'] = array();

            if (HSetting::Get('hostname', 'mailing'))
                $mail['transportOptions']['host'] = HSetting::Get('hostname', 'mailing');

            if (HSetting::Get('username', 'mailing'))
                $mail['transportOptions']['username'] = HSetting::Get('username', 'mailing');

            if (HSetting::Get('password', 'mailing'))
                $mail['transportOptions']['password'] = HSetting::Get('password', 'mailing');

            if (HSetting::Get('encryption', 'mailing'))
                $mail['transportOptions']['encryption'] = HSetting::Get('encryption', 'mailing');

            if (HSetting::Get('port', 'mailing'))
            	$mail['transportOptions']['port'] = HSetting::Get('port', 'mailing');
            
            if (HSetting::Get('allowSelfSignedCerts', 'mailing')) {
            	$mail['transportOptions']['options']['ssl']['allow_self_signed'] = true;
            	$mail['transportOptions']['options']['ssl']['verify_peer'] = false;
            }
        }
        $config['components']['mail'] = $mail;

        // Add Theme
        $theme = HSetting::Get('theme');
        if ($theme && $theme != "") {
            $config['theme'] = $theme;
        } else {
            unset($config['theme']);
        }

        HSetting::setConfiguration($config);
    }

    /**
     * Returns the dynamic configuration file as array
     *
     * @return Array Configuration file
     */
    public static function getConfiguration()
    {

        $configFile = Yii::app()->params['dynamicConfigFile'];
        $config = require($configFile);

        if (!is_array($config))
            return array();

        return $config;
    }

    /**
     * Writes a new configuration file array
     *
     * @param type $config
     */
    public static function setConfiguration($config = array())
    {

        $configFile = Yii::app()->params['dynamicConfigFile'];

        $content = "<" . "?php return ";
        $content .= var_export($config, true);
        $content .= "; ?" . ">";

        file_put_contents($configFile, $content);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($configFile);
        }
        
        if (function_exists('apc_compile_file')) {
            apc_compile_file($configFile);
        }
        
    }

    /**
     * Checks if initial data like settings, groups are installed.
     * 
     * @return Boolean Is Installed
     */
    public static function isInstalled()
    {

        if (isset(Yii::app()->params['installed']) && Yii::app()->params['installed']) {
            return true;
        }

        return false;
    }

}
