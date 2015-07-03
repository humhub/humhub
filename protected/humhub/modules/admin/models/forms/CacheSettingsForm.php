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
            'type' => \Yii::t('app', 'Cache Backend'),
            'expireTime' => \Yii::t('app', 'Default Expire Time (in seconds)'),
        );
    }

    public function getTypes()
    {
        return array(
            'yii\caching\DummyCache' => \Yii::t('app', 'No caching (Testing only!)'),
            'yii\caching\FileCache' => \Yii::t('app', 'File'),
            'yii\caching\ApcCache' => \Yii::t('app', 'APC'),
        );
    }

    public function checkCacheType($attribute, $params)
    {
        if ($this->type == 'CApcCache' && !function_exists('apc_add')) {
            $this->addError($attribute, \Yii::t('app', "PHP APC Extension missing - Type not available!"));
        }

        if ($this->type == 'CDbCache' && !class_exists('SQLite3')) {
            $this->addError($attribute, \Yii::t('app', "PHP SQLite3 Extension missing - Type not available!"));
        }
    }

}
