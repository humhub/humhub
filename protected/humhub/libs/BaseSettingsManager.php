<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\components\SettingActiveRecord;
use humhub\exceptions\InvalidArgumentTypeException;
use Stringable;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
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
    public string $moduleId;

    /**
     * @var array|null of loaded settings
     */
    protected ?array $_loaded = null;

    /**
     * @var string settings model class name
     */
    public string $modelClass = 'humhub\models\Setting';

    /**
     * @inheritdoc
     */
    public function init()
    {
        try {
            if ($this->moduleId === '') {
                throw new InvalidConfigException('Empty module id!', 1);
            }
        } catch (InvalidConfigException $t) {
            throw $t;
        } catch (\Throwable $t) {
            throw new InvalidConfigException('Module id not set!', 2);
        }

        if (Yii::$app->isDatabaseInstalled()) {
            $this->loadValues();
        }

        parent::init();
    }

    /**
     * Sets a settings value
     *
     * @param string $name
     * @param string|int|bool $value
     *
     * @return void
     */
    public function set(string $name, $value)
    {
        if ($name === '') {
            throw new InvalidArgumentException(
                sprintf('Argument #1 ($name) passed to %s may not be an empty string!', __METHOD__),
            );
        }

        if ($value === null) {
            $this->delete($name);
            return;
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
     * @param string $name
     * @param mixed $value array or object
     */
    public function setSerialized(string $name, $value)
    {
        $this->set($name, Json::encode($value));
    }

    /**
     * Receives a value which was saved as serialized value.
     *
     * @param string $name
     * @param mixed $default the setting value or null when not exists
     * @param bool $asArray whether to return objects in terms of associative arrays.
     * @param bool $throwException if true then throw an exception upon error, rather than returning the serialized string
     *
     * @return mixed|string|null
     */
    public function getSerialized(string $name, $default = null, bool $asArray = true, bool $throwException = false)
    {
        $value = $this->get($name, $default);
        if (is_string($value)) {
            try {
                $value = Json::decode($value, $asArray);
            } catch (InvalidArgumentException $ex) {
                Yii::error($ex->getMessage());

                if ($throwException) {
                    throw $ex;
                }
            }
        }
        return $value;
    }

    /**
     * Returns value of setting
     *
     * @param string|int $name the name of setting
     *
     * @return string|mixed|null the setting value or null when not exists
     */
    public function get(string $name, $default = null)
    {
        $value = $this->_loaded[$name] ?? null;

        // make sure it is an int, if it is possible
        return filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? $value ?? $default;
    }

    /**
     * Returns the value of setting without any caching
     *
     * @param string $name the name of setting
     *
     * @return string the setting value or null when not exists
     */
    public function getUncached(string $name, $default = null): ?string
    {
        $record = $this->find()->andWhere(['name' => $name])->one();
        return ($record !== null) ? $record->value : $default;
    }

    /**
     * Deletes setting
     *
     * @param string $name
     */
    public function delete(string $name)
    {
        $record = $this->find()->andWhere(['name' => $name])->one();
        if ($record !== null) {
            try {
                $record->delete();
            } catch (StaleObjectException $e) {
                Yii::error('Could not delete setting "' . $name . '".  Error: ' . $e->getMessage(), 'base');
            } catch (\Throwable $e) {
                Yii::error('Could not delete setting "' . $name . '".  Error: ' . $e->getMessage(), 'base');
            }
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

            array_map(static function ($record) use (&$settings) {
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
    protected function getCacheKey(): string
    {
        /** @var SettingActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        return $modelClass::getCacheKey($this->moduleId);
    }

    /**
     * Returns settings active record instance
     */
    protected function createRecord()
    {
        $model = new $this->modelClass();
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
     * @param string|array|Stringable|null $prefix if set, only delete settings with given name prefix (e.g. "theme.")
     *     Versions before 1.15 used the `$prefix` parameter as a full wildcard (`'%pattern%'`) and not actually as a prefix. Use
     *     `$prefix = '%pattern%'` to get the old behaviour. Or use `$parameter = '%suffix'` if you want to match
     *     against the end of the names.
     */
    public function deleteAll($prefix = null)
    {
        $query = $this->find();

        if ($prefix !== null) {
            if (StringHelper::isStringable($prefix)) {
                if (false === strpos($prefix, "%")) {
                    $prefix .= "%";
                }
            } elseif (!is_array($prefix)) {
                throw new InvalidArgumentTypeException(
                    '$prefix',
                    ['string', 'int', 'null', \Stringable::class],
                    $prefix,
                );
            }

            $query->andWhere(['LIKE', 'name', $prefix, false]);
        }

        $settings = $query->all();
        array_walk($settings, static fn($setting, $i, $self) => $self->delete($setting->name), $this);
    }

    /**
     * Checks if settings table exists or application is not installed yet
     *
     * @since 1.3
     * @deprecated since 1.16
     */
    public static function isDatabaseInstalled(): bool
    {
        return Yii::$app->isDatabaseInstalled(true);
    }
}
