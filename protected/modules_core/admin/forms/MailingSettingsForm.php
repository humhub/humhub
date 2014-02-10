<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class MailingSettingsForm extends CFormModel {

    public $systemEmailAddress;
    public $systemEmailName;

    public $transportType;
    public $hostname;
    public $username;
    public $password;
    public $port;
    public $encryption;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('transportType, systemEmailAddress, systemEmailName', 'required'),
            array('transportType', 'in', 'range'=>array('php', 'smtp')),
            array('encryption', 'in', 'range'=>array('', 'ssl')),
            array('systemEmailAddress', 'email'),
            array('port', 'numerical', 'integerOnly' => true, 'min'=>1, 'max'=>65535),
            array('transportType, hostname, username, password, port, encryption, systemEmailAddress, systemEmailName', 'length', 'max' => 255),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'systemEmailAddress' => Yii::t('AdminModule.base', 'E-Mail sender address'),
            'systemEmailName' => Yii::t('AdminModule.base', 'E-Mail sender name'),
            'transportType' => Yii::t('AdminModule.base', 'Mail Transport Type'),
            'username' => Yii::t('AdminModule.base', 'Username'),
            'password' => Yii::t('AdminModule.base', 'Password'),
            'port' => Yii::t('AdminModule.base', 'Port number'),
            'encryption' => Yii::t('AdminModule.base', 'Encryption'),
        );
    }

}