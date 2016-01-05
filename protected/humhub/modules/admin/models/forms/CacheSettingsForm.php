<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class CacheSettingsForm extends \yii\base\Model
{

    public $type;
    public $expireTime;

    /**
     * Declares the validation rules.
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
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'type' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'Cache Backend'),
            'expireTime' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'Default Expire Time (in seconds)'),
        );
    }

    public function getTypes()
    {
        return array(
            'yii\caching\DummyCache' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'No caching (Testing only!)'),
            'yii\caching\FileCache' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'File'),
            'yii\caching\ApcCache' => \Yii::t('AdminModule.forms_CacheSettingsForm', 'APC'),
        );
    }

    public function checkCacheType($attribute, $params)
    {
        if ($this->type == 'yii\caching\ApcCache' && !function_exists('apc_add')) {
            $this->addError($attribute, \Yii::t('AdminModule.forms_CacheSettingsForm', "PHP APC Extension missing - Type not available!"));
        }
    }

}
