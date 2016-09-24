<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;

/**
 * CachingForm
 * 
 * @since 0.5
 */
class CacheSettingsForm extends Model
{

    public $type;
    public $expireTime;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $this->type = $settingsManager->get('cache.class');
        $this->expireTime = $settingsManager->get('cache.expireTime');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array(['type', 'expireTime'], 'required'),
            array('type', 'checkCacheType'),
            array('expireTime', 'integer'),
            array('type', 'in', 'range' => array_keys($this->getTypes())),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'type' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'Cache Backend'),
            'expireTime' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'Default Expire Time (in seconds)'),
        );
    }

    /**
     * @inheritdoc
     */
    public function getTypes()
    {
        return array(
            'yii\caching\DummyCache' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'No caching (Testing only!)'),
            'yii\caching\FileCache' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'File'),
            'yii\caching\ApcCache' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'APC'),
        );
    }

    /**
     * @inheritdoc
     */
    public function checkCacheType($attribute, $params)
    {
        if ($this->type == 'yii\caching\ApcCache' && !function_exists('apc_add')) {
            $this->addError($attribute, \Yii::t('AdminModule.forms_CacheSettingsForm', "PHP APC Extension missing - Type not available!"));
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

        \humhub\libs\DynamicConfig::rewrite();
        return true;
    }

}
