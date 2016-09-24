<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * AuthenticationLdapSettingsForm
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
    public $encryptionTypes = [
        '' => 'None',
        'tls' => 'TLS (aka SSLV2)',
        'ssl' => 'SSL',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->getModule('user')->settings;

        // Load Defaults
        $this->enabled = $settingsManager->get('auth.ldap.enabled');
        $this->refreshUsers = $settingsManager->get('auth.ldap.refreshUsers');
        $this->username = $settingsManager->get('auth.ldap.username');
        $this->password = $settingsManager->get('auth.ldap.password');
        $this->hostname = $settingsManager->get('auth.ldap.hostname');
        $this->port = $settingsManager->get('auth.ldap.port');
        $this->encryption = $settingsManager->get('auth.ldap.encryption');
        $this->baseDn = $settingsManager->get('auth.ldap.baseDn');
        $this->loginFilter = $settingsManager->get('auth.ldap.loginFilter');
        $this->userFilter = $settingsManager->get('auth.ldap.userFilter');
        $this->usernameAttribute = $settingsManager->get('auth.ldap.usernameAttribute');
        $this->emailAttribute = $settingsManager->get('auth.ldap.emailAttribute');

        if ($this->password != '')
            $this->password = '---hidden---';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array(['enabled', 'refreshUsers', 'usernameAttribute', 'emailAttribute', 'username', 'password', 'hostname', 'port', 'baseDn', 'loginFilter', 'userFilter'], 'string', 'max' => 255),
            array(['usernameAttribute', 'username', 'password', 'hostname', 'port', 'baseDn', 'loginFilter', 'userFilter'], 'required'),
            array('encryption', 'in', 'range' => array('', 'ssl', 'tls')),
        );
    }

    /**
     * @inheritdoc
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

    /**
     * Saves the form
     * 
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->getModule('user')->settings;

        $settingsManager->set('auth.ldap.enabled', $this->enabled);
        $settingsManager->set('auth.ldap.refreshUsers', $this->refreshUsers);
        $settingsManager->set('auth.ldap.hostname', $this->hostname);
        $settingsManager->set('auth.ldap.port', $this->port);
        $settingsManager->set('auth.ldap.encryption', $this->encryption);
        $settingsManager->set('auth.ldap.username', $this->username);
        if ($this->password != '---hidden---')
            $settingsManager->set('auth.ldap.password', $this->password);
        $settingsManager->set('auth.ldap.baseDn', $this->baseDn);
        $settingsManager->set('auth.ldap.loginFilter', $this->loginFilter);
        $settingsManager->set('auth.ldap.userFilter', $this->userFilter);
        $settingsManager->set('auth.ldap.usernameAttribute', $this->usernameAttribute);
        $settingsManager->set('auth.ldap.emailAttribute', $this->emailAttribute);

        return true;
    }

}
