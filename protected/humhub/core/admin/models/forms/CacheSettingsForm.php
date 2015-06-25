<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class CacheSettingsForm extends CFormModel {

    public $type;
    public $expireTime;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('type, expireTime', 'required'),
            array('type', 'checkCacheType'),
            array('expireTime', 'numerical', 'integerOnly' => true),
            array('type', 'in', 'range'=>array('CDummyCache','CFileCache', 'CDbCache', 'CApcCache')),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'type' => Yii::t('AdminModule.forms_CacheSettingsForm', 'Cache Backend'),
            'expireTime' => Yii::t('AdminModule.forms_CacheSettingsForm', 'Default Expire Time (in seconds)'),
        );
    }

    public function checkCacheType($attribute, $params) {
        if ($this->type == 'CApcCache' && !function_exists('apc_add')) {
            $this->addError($attribute, Yii::t('AdminModule.forms_CacheSettingsForm', "PHP APC Extension missing - Type not available!"));
        }

        if ($this->type == 'CDbCache' && !class_exists('SQLite3')) {
            $this->addError($attribute, Yii::t('AdminModule.forms_CacheSettingsForm', "PHP SQLite3 Extension missing - Type not available!"));
        }

    }

}