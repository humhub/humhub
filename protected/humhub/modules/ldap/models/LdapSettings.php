<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\models;

use humhub\components\SettingsManager;
use humhub\modules\ldap\authclient\LdapAuth;
use Yii;
use yii\base\Model;

/**
 * LdapSettings
 *
 * @see LdapAuth for more information
 * @since 0.5
 */
class LdapSettings extends Model
{

    const PASSWORD_FIELD_DUMMY = '---HIDDEN---';

    /**
     * @var boolean
     */
    public $enabled;

    /**
     * @var boolean
     */
    public $refreshUsers;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $passwordField;

    /**
     * @var string
     */
    public $hostname;

    /**
     * @var int
     */
    public $port;

    /**
     * @var string
     */
    public $encryption;

    /**
     * @var string
     */
    public $baseDn;

    /**
     * @var string
     */
    public $loginFilter;

    /**
     * @var string
     */
    public $userFilter;

    /**
     * @var string
     */
    public $usernameAttribute;

    /**
     * @var string
     */
    public $emailAttribute;

    /**
     * @var string
     */
    public $idAttribute;

    /**
     * @var array
     */
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
        $this->loadSaved();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enabled', 'refreshUsers', 'usernameAttribute', 'emailAttribute', 'username', 'passwordField', 'hostname', 'port', 'idAttribute'], 'string', 'max' => 255],
            [['baseDn', 'loginFilter', 'userFilter'], 'string'],
            [['usernameAttribute', 'username', 'passwordField', 'hostname', 'port', 'baseDn', 'loginFilter', 'userFilter'], 'required'],
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
            'passwordField' => Yii::t('LdapModule.base', 'Password'),
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
            'passwordField' => Yii::t('LdapModule.base', 'The default credentials password (used only with username above).'),
            'baseDn' => Yii::t('LdapModule.base', 'The default base DN used for searching for accounts.'),
            'loginFilter' => Yii::t('LdapModule.base', 'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;'),
            'usernameAttribute' => Yii::t('LdapModule.base', 'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;'),
            'emailAttribute' => Yii::t('LdapModule.base', 'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;'),
            'idAttribute' => Yii::t('LdapModule.base', 'Not changeable LDAP attribute to unambiguously identify the user in the directory. If empty the user will be determined automatically by e-mail address or username. Examples: objectguid (ActiveDirectory) or uidNumber (OpenLDAP)'),
            'userFilter' => Yii::t('LdapModule.base', 'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;'),

        ];
    }


    /**
     * Loads the saved settings
     *
     * @return bool|void
     */
    public function loadSaved()
    {
        /** @var SettingsManager $settings */
        $settings = Yii::$app->getModule('ldap')->settings;

        // Load Defaults
        $this->enabled = $settings->get('enabled');

        $this->username = $settings->get('username');
        $this->password = $settings->get('password');
        if (!empty($this->password)) {
            $this->passwordField = static::PASSWORD_FIELD_DUMMY;
        }

        $this->hostname = $settings->get('hostname');
        $this->port = $settings->get('port');
        $this->encryption = $settings->get('encryption');
        $this->baseDn = $settings->get('baseDn');

        $this->loginFilter = $settings->get('loginFilter');
        $this->userFilter = $settings->get('userFilter');

        $this->usernameAttribute = $settings->get('usernameAttribute');
        $this->emailAttribute = $settings->get('emailAttribute');
        $this->idAttribute = $settings->get('idAttribute');

        $this->refreshUsers = $settings->get('refreshUsers');
    }


    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        /** @var SettingsManager $settings */
        $settings = Yii::$app->getModule('ldap')->settings;

        $settings->set('enabled', $this->enabled);
        $settings->set('hostname', $this->hostname);
        $settings->set('port', $this->port);
        $settings->set('encryption', $this->encryption);
        $settings->set('username', $this->username);
        if ($this->passwordField !== static::PASSWORD_FIELD_DUMMY)
            $settings->set('password', $this->passwordField);
        $settings->set('baseDn', $this->baseDn);
        $settings->set('loginFilter', $this->loginFilter);
        $settings->set('userFilter', $this->userFilter);
        $settings->set('usernameAttribute', $this->usernameAttribute);
        $settings->set('emailAttribute', $this->emailAttribute);
        $settings->set('idAttribute', $this->idAttribute);
        $settings->set('refreshUsers', $this->refreshUsers);

        return true;
    }


    /**
     * Returns a configured LdapAuth class definition
     *
     * @return array the LDAP Auth definition
     */
    public function getLdapAuthDefinition()
    {
        return [
            'class' => LdapAuth::class,
            'hostname' => $this->hostname,
            'port' => $this->port,
            'bindUsername' => $this->username,
            'bindPassword' => $this->password,
            'useSsl' => ($this->encryption === 'ssl'),
            'useStartTls' => ($this->encryption === 'tls'),
            'baseDn' => $this->baseDn,
            'loginFilter' => $this->loginFilter,
            'userFilter' => $this->userFilter,
            'autoRefreshUsers' => (boolean) $this->refreshUsers,
            'emailAttribute' => $this->emailAttribute,
            'usernameAttribute' => $this->usernameAttribute,
            'idAttribute' => $this->idAttribute
        ];
    }

    /**
     * Checks whether LDAP is enabled or not.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        /** @var SettingsManager $settings */
        $settings = Yii::$app->getModule('ldap')->settings;

        return (bool)$settings->get('enabled');
    }

}
