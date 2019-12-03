<?php

namespace humhub\modules\admin\models\forms;

use humhub\libs\DynamicConfig;
use humhub\modules\admin\events\FetchReloadableScriptsEvent;
use humhub\modules\admin\Module;
use Yii;
use yii\base\Model;

/**
 * CachingForm
 *
 * @since 0.5
 */
class CacheSettingsForm extends Model
{
    const EVENT_FETCH_RELOADABLE_SCRIPTS = 'fetchReloadableScripts';

    public $type;
    public $expireTime;
    public $reloadableScripts;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $this->type = $settingsManager->get('cache.class');
        $this->expireTime = $settingsManager->get('cache.expireTime');
        $this->reloadableScripts = $settingsManager->get('cache.reloadableScripts');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'expireTime'], 'required'],
            ['reloadableScripts', 'string'],
            ['type', 'checkCacheType'],
            ['expireTime', 'integer'],
            ['type', 'in', 'range' => array_keys($this->getTypes())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => \Yii::t('AdminModule.settings', 'Cache Backend'),
            'expireTime' => \Yii::t('AdminModule.settings', 'Default Expire Time (in seconds)'),
            'reloadableScripts' => \Yii::t('AdminModule.settings', 'Prevent client caching of following scripts'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTypes()
    {
        $cacheTypes = [
            'yii\caching\DummyCache' => \Yii::t('AdminModule.settings', 'No caching'),
            'yii\caching\FileCache' => \Yii::t('AdminModule.settings', 'File'),
            'yii\caching\ApcCache' => \Yii::t('AdminModule.settings', 'APC(u)'),
        ];

        if (isset(Yii::$app->redis)) {
            $cacheTypes['yii\redis\Cache'] = \Yii::t('AdminModule.settings', 'Redis');
        }

        return $cacheTypes;
    }

    /**
     * @inheritdoc
     */
    public function checkCacheType($attribute, $params)
    {
        if ($this->type == 'yii\caching\ApcCache' && !function_exists('apc_add') && !function_exists('apcu_add')) {
            $this->addError($attribute, \Yii::t('AdminModule.settings', "PHP APC(u) Extension missing - Type not available!"));
        }
    }

    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;

        $settingsManager->set('cache.class', $this->type);
        $settingsManager->set('cache.expireTime', $this->expireTime);
        $settingsManager->set('cache.reloadableScripts', $this->reloadableScripts);

        DynamicConfig::rewrite();

        return true;
    }

    public static function getReloadableScriptUrls()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('admin');
        $instance = new static();
        $urls = array_merge($instance->getReloadableScriptsAsArray(), $module->defaultReloadableScripts);
        $event = new FetchReloadableScriptsEvent(['urls' => $urls]);
        $instance->trigger(static::EVENT_FETCH_RELOADABLE_SCRIPTS, $event);
        return $event->urls;
    }

    public function getReloadableScriptsAsArray()
    {
        if(is_string($this->reloadableScripts) && !empty($this->reloadableScripts)) {
            return array_map('trim', explode("\n", $this->reloadableScripts));
        }

        return [];
    }

}
