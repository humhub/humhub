<?php

/**
 * @package humhub.modules_core.admin.forms
 */
class ProxySettingsForm extends CFormModel {

    public $enabled;
    public $server;
    public $port;
    public $user;
    public $password;
    public $noproxy;

    /**
     * Declares the validation rules.
     */
    public function rules() {

        return array(
            array('enabled, server, user, password, noproxy', 'length', 'max'=>255),
            array('port', 'numerical', 'integerOnly' => true, 'max'=>65535, 'min'=>1),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'enabled' => Yii::t('AdminModule.forms_ProxySettingsForm', 'Enabled'),
            'server' => Yii::t('AdminModule.forms_ProxySettingsForm', 'Server'),
            'port' => Yii::t('AdminModule.forms_ProxySettingsForm', 'Port'),
            'user' => Yii::t('AdminModule.forms_ProxySettingsForm', 'User'),
            'password' => Yii::t('AdminModule.forms_ProxySettingsForm', 'Password'),
            'noproxy' => Yii::t('AdminModule.forms_ProxySettingsForm', 'No Proxy Hosts'),
        );
    }

}