<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\models;

use Yii;
use yii\base\Model;

/**
 * AuthenticationLdapSettingsForm
 *
 * @since 0.5
 */
class LdapSettings extends Model
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
    public $idAttribute;
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

        $settings = Yii::$app->getModule('user')->settings;

        // Load Defaults
        $this->enabled = $settings->get('auth.ldap.enabled');
        $this->refreshUsers = $settings->get('auth.ldap.refreshUsers');
        $this->username = $settings->get('auth.ldap.username');
        $this->password = $settings->get('auth.ldap.password');
        $this->hostname = $settings->get('auth.ldap.hostname');
        $this->port = $settings->get('auth.ldap.port');
        $this->encryption = $settings->get('auth.ldap.encryption');
        $this->baseDn = $settings->get('auth.ldap.baseDn');
        $this->loginFilter = $settings->get('auth.ldap.loginFilter');
        $this->userFilter = $settings->get('auth.ldap.userFilter');
        $this->usernameAttribute = $settings->get('auth.ldap.usernameAttribute');
        $this->emailAttribute = $settings->get('auth.ldap.emailAttribute');
        $this->idAttribute = $settings->get('auth.ldap.idAttribute');

        if ($this->password != '')
            $this->password = '---hidden---';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enabled', 'refreshUsers', 'usernameAttribute', 'emailAttribute', 'username', 'password', 'hostname', 'port', 'idAttribute'], 'string', 'max' => 255],
            [['baseDn', 'loginFilter', 'userFilter'], 'string'],
            [['usernameAttribute', 'username', 'password', 'hostname', 'port', 'baseDn', 'loginFilter', 'userFilter'], 'required'],
            ['encryption', 'in', 'range' => ['', 'ssl', 'tls']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'enabled' => Yii::t('LdapModule.base', 'Enable LDAP Support'),
            'refreshUsers' => Yii::t('LdapModule.base', 'Fetch/Update Users Automatically'),
            'username' => Yii::t('LdapModule.base', 'Username'),
            'password' => Yii::t('LdapModule.base', 'Password'),
            'encryption' => Yii::t('LdapModule.base', 'Encryption'),
            'hostname' => Yii::t('LdapModule.base', 'Hostname'),
            'port' => Yii::t('LdapModule.base', 'Port'),
            'baseDn' => Yii::t('LdapModule.base', 'Base DN'),
            'loginFilter' => Yii::t('LdapModule.base', 'Login Filter'),
            'userFilter' => Yii::t('LdapModule.base', 'User Filer'),
            'usernameAttribute' => Yii::t('LdapModule.base', 'Username Attribute'),
            'emailAttribute' => Yii::t('LdapModule.base', 'E-Mail Address Attribute'),
            'idAttribute' => Yii::t('LdapModule.base', 'ID Attribute'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'encryption' => Yii::t('LdapModule.base', 'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.'),
            'username' => Yii::t('LdapModule.base', 'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.'),
            'password' => Yii::t('LdapModule.base', 'The default credentials password (used only with username above).'),
            'baseDn' => Yii::t('LdapModule.base', 'The default base DN used for searching for accounts.'),
            'loginFilter' => Yii::t('LdapModule.base', 'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;'),
            'usernameAttribute' => Yii::t('LdapModule.base', 'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;'),
            'emailAttribute' => Yii::t('LdapModule.base', 'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;'),
            'idAttribute' => Yii::t('LdapModule.base', 'Not changeable LDAP attribute to unambiguously identify the user in the directory. If empty the user will be determined automatically by e-mail address or username. Examples: objectguid (ActiveDirectory) or uidNumber (OpenLDAP)'),
            'userFilter' => Yii::t('LdapModule.base', 'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;'),
            
        ];
    }

    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        $settings = Yii::$app->getModule('user')->settings;

        $settings->set('auth.ldap.enabled', $this->enabled);
        $settings->set('auth.ldap.refreshUsers', $this->refreshUsers);
        $settings->set('auth.ldap.hostname', $this->hostname);
        $settings->set('auth.ldap.port', $this->port);
        $settings->set('auth.ldap.encryption', $this->encryption);
        $settings->set('auth.ldap.username', $this->username);
        if ($this->password != '---hidden---')
            $settings->set('auth.ldap.password', $this->password);
        $settings->set('auth.ldap.baseDn', $this->baseDn);
        $settings->set('auth.ldap.loginFilter', $this->loginFilter);
        $settings->set('auth.ldap.userFilter', $this->userFilter);
        $settings->set('auth.ldap.usernameAttribute', $this->usernameAttribute);
        $settings->set('auth.ldap.emailAttribute', $this->emailAttribute);
        $settings->set('auth.ldap.idAttribute', $this->idAttribute);

        return true;
    }

}
