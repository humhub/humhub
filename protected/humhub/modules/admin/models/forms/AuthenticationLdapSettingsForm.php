<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class AuthenticationLdapSettingsForm extends \yii\base\Model
{

    public $enabled;
    public $refreshUsers;
    public $username;
    public $password;
    public $hostname;
    public $port;
    public $encryption;
    public $baseDn;
    public $loginFilter;
    public $userFilter;
    public $usernameAttribute;
    public $emailAttribute;
    public $encryptionTypes = array(
        '' => 'None',
        'tls' => 'TLS (aka SSLV2)',
        'ssl' => 'SSL',
    );

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array(['enabled', 'refreshUsers', 'usernameAttribute', 'emailAttribute', 'username', 'password', 'hostname', 'port', 'baseDn', 'loginFilter', 'userFilter'], 'string', 'max' => 255),
            array('encryption', 'in', 'range' => array('', 'ssl', 'tls')),
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
            'enabled' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Enable LDAP Support'),
            'refreshUsers' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Fetch/Update Users Automatically'),
            'username' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Username'),
            'password' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Password'),
            'encryption' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Encryption'),
            'hostname' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Hostname'),
            'port' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Port'),
            'baseDn' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Base DN'),
            'loginFilter' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Login Filter'),
            'userFilter' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'User Filer'),
            'usernameAttribute' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'Username Attribute'),
            'emailAttribute' => Yii::t('AdminModule.forms_AuthenticationLdapSettingsForm', 'E-Mail Address Attribute'),
        );
    }

}
