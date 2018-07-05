<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\db\conditions\LikeCondition;
use yii\helpers\Json;

/**
 * Description of SettingManager
 *
 * @since 1.1
 * @author Luke
 */
abstract class BaseSettingsManager extends Component
{

    /**
     * @var string module id this settings manager belongs to.
     */
    public $moduleId = null;

    /**
     * @var array|null of loaded settings
     */
    protected $_loaded = null;

    /**
     * @var string settings model class name
     */
    public $modelClass = 'humhub\models\Setting';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->moduleId === null) {
            throw new Exception('Could not determine module id');
        }

        $this->loadValues();

        parent::init();
    }

    /**
     * Sets a settings value
     *
     * @param string $name
     * @param string $value
     */
    public function set($name, $value)
    {
        if ($value === null) {
            return $this->delete($name);
        }

        // Update database setting record
        $record = $this->find()->andWhere(['name' => $name])->one();
        if ($record === null) {
            $record = $this->createRecord();
            $record->name = $name;
        }

        if (is_bool($value)) {
            $value = (int)$value;
        }

        $record->value = (string)$value;
        if (!$record->save()) {
            Yii::error('Could not store setting: ' . $name);
        }

        // Store to runtime
        $this->_loaded[$name] = $value;

        $this->invalidateCache();
    }

    /**
     * Can be used to set object/arrays as a serialized values.
     *
     *
     * @param string $name
     * @param mixed $value array or object
     */
    public function setSerialized($name, $value)
    {
        $this->set($name, Json::encode($value));
    }

    /**
     * Receives a value which was saved as serialized value.
     *
     * @param string $name
     * @param mixed $default the setting value or null when not exists
     */
    public function getSerialized($name, $default = null)
    {
        $value = $this->get($name, $default);
        if (is_string($value)) {
            $value = Json::decode($value);
        }
        return $value;
    }

    /**
     * Returns value of setting
     *
     * @param string $name the name of setting
     * @return string the setting value or null when not exists
     */
    public function get($name, $default = null)
    {
        return isset($this->_loaded[$name]) ? $this->_loaded[$name] : $default;
    }

    /**
     * Returns the value of setting without any caching
     *
     * @param string $name the name of setting
     * @return string the setting value or null when not exists
     */
    public function getUncached($name, $default = null)
    {
        $record = $this->find()->andWhere(['name' => $name])->one();
        return ($record !== null) ? $record->value : $default;
    }

    /**
     * Deletes setting
     *
     * @param string $name
     */
    public function delete($name)
    {
        $record = $this->find()->andWhere(['name' => $name])->one();
        if ($record !== null) {
            $record->delete();
        }

        if (isset($this->_loaded[$name])) {
            unset($this->_loaded[$name]);
        }

        $this->invalidateCache();
    }

    /**
     * Loads values from database
     */
    protected function loadValues()
    {
        $cached = Yii::$app->cache->get($this->getCacheKey());
        if ($cached === false) {
            $this->_loaded = [];
            $settings = &$this->_loaded;

            array_map(function ($record) use (&$settings) {
                $settings[$record->name] = $record->value;
            }, $this->find()->all());

            Yii::$app->cache->set($this->getCacheKey(), $this->_loaded);
        } else {
            $this->_loaded = $cached;
        }
    }

    /**
     * Reloads all values from database
     */
    public function reload()
    {
        $this->invalidateCache();
        $this->loadValues();
    }

    /**
     * Invalidates settings cache
     */
    protected function invalidateCache()
    {
        Yii::$app->cache->delete($this->getCacheKey());
    }

    /**
     * Returns settings managers cache key
     *
     * @return string the cache key
     */
    protected function getCacheKey()
    {
        return 'settings-' . $this->moduleId;
    }

    /**
     * Returns settings active record instance
     */
    protected function createRecord()
    {
        $model = new $this->modelClass;
        $model->module_id = $this->moduleId;

        return $model;
    }

    /**
     * Returns ActiveQuery to find settings
     *
     * @return \yii\db\ActiveQuery
     */
    protected function find()
    {
        $modelClass = $this->modelClass;
        return $modelClass::find()->andWhere(['module_id' => $this->moduleId]);
    }

    /**
     * Deletes all stored settings
     *
     * @param string|null $prefix if set only delete settings with given name prefix (e.g. theme.)
     */
    public function deleteAll($prefix = null)
    {
        $query = $this->find();
        if ($prefix !== null) {
            $query->andWhere(new LikeCondition('name', 'LIKE', $prefix));
        }

        foreach ($query->all() as $setting) {
            $this->delete($setting->name);
        }
    }

    /**
     * Checks if settings table exists or application is not installed yet
     *
     * @since 1.3
     * @return bool
     */
    public static function isDatabaseInstalled()
    {
        try {
            if (in_array('setting', Yii::$app->db->schema->getTableNames())) {
                return true;
            }
        } catch (\Exception $ex) {
            return false;
        }

        return false;
    }
}
