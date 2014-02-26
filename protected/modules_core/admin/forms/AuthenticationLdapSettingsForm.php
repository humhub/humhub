<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class AuthenticationLdapSettingsForm extends CFormModel {

    public $enabled;
    public $username;
    public $password;
    public $hostname;
    public $port;
    public $encryption;
    

    public $baseDn;
    public $loginFilter;
    public $userFilter;
    public $usernameAttribute;
    
   public $encryptionTypes = array(
        '' => 'None',
        'tls' => 'TLS (aka SSLV2)',
        'ssl' => 'SSL',
    );

    /**
     * Declares the validation rules.
     */
    public function rules() {

        return array(
            array('enabled, usernameAttribute, username, password, hostname, port, baseDn, loginFilter, userFilter',  'length', 'max' => 255),
            array('encryption', 'in', 'range'=>array('', 'ssl', 'tls')),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'enabled' => Yii::t('AdminModule.authentication', 'Enable LDAP Support'),
            'username' => Yii::t('AdminModule.authentication', 'Username'),
            'password' => Yii::t('AdminModule.authentication', 'Password'),
            'encryption' => Yii::t('AdminModule.authentication', 'Encryption'),
            'hostname' => Yii::t('AdminModule.authentication', 'Hostname'),
            'port' => Yii::t('AdminModule.authentication', 'Port'),
            'baseDn' => Yii::t('AdminModule.authentication', 'Base DN'),
            'loginFilter' => Yii::t('AdminModule.authentication', 'Login Filter'),
            'userFilter' => Yii::t('AdminModule.authentication', 'User Filer'),
            'usernameAttribute' => Yii::t('AdminModule.authentication', 'Username Attribute'),
        );
    }

}