<?php

class ConfigureForm extends CFormModel {

    public $password;
    public $ipFilters;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('password', 'required'),
            array('ipFilters', 'safe'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'password' => Yii::t('YiiGiiModule.base', 'Gii Password'),
            'ipFilters' => Yii::t('YiiGiiModule.base', 'Limit IPs (Comma Separated)'),
        );
    }

}
