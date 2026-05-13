<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\models;

use humhub\components\SettingsManager;
use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\user\authclient\Collection;
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
    public const PASSWORD_FIELD_DUMMY = '---HIDDEN---';

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var bool
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
     * @var bool
     */
    public $disableCertificateChecking;

    /**
     * @var string
     */
    public $baseDn;

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
    public $ignoredDNs;

    /**
     * @var string
     */
    public $idAttribute;

    /**
     * @var string[] auth clients allowed for LDAP-sourced users. Defaults to
     * `['ldap']` for fresh installs — i.e. LDAP password login is enabled out
     * of the box. Unchecking 'ldap' disables direct LDAP password login and
     * forces users to sign in via one of the other selected methods (SSO).
     */
    public $allowedAuthClientIds = [];

    /**
     * @var array
     */
    public $encryptionTypes = [
        '' => 'None',
        'tls' => 'StartTLS',
        'ssl' => 'SSL/TLS',
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
            [['enabled', 'refreshUsers', 'usernameAttribute', 'emailAttribute', 'username', 'passwordField', 'hostname', 'port', 'idAttribute', 'disableCertificateChecking'], 'string', 'max' => 255],
            [['baseDn', 'userFilter', 'ignoredDNs'], 'string'],
            [['usernameAttribute', 'username', 'passwordField', 'hostname', 'port', 'baseDn', 'userFilter', 'idAttribute'], 'required'],
            ['encryption', 'in', 'range' => ['', 'ssl', 'tls']],
            ['allowedAuthClientIds', 'filter', 'filter' => fn($v) => is_array($v) && $v !== [] ? $v : ['ldap']],
            ['allowedAuthClientIds', 'each', 'rule' => ['in', 'range' => array_keys($this->getAuthClientOptions())]],
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
            'disableCertificateChecking' => Yii::t('LdapModule.base', 'Disable Certificate Checking'),
            'hostname' => Yii::t('LdapModule.base', 'Hostname'),
            'port' => Yii::t('LdapModule.base', 'Port'),
            'baseDn' => Yii::t('LdapModule.base', 'Base DN'),
            'userFilter' => Yii::t('LdapModule.base', 'User Filter'),
            'usernameAttribute' => Yii::t('LdapModule.base', 'Username Attribute'),
            'emailAttribute' => Yii::t('LdapModule.base', 'E-Mail Address Attribute'),
            'idAttribute' => Yii::t('LdapModule.base', 'ID Attribute'),
            'ignoredDNs' => Yii::t('LdapModule.base', 'Ignored LDAP entries'),
            'allowedAuthClientIds' => Yii::t('LdapModule.base', 'Allowed Authentication Methods'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'username' => Yii::t('LdapModule.base', 'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.'),
            'passwordField' => Yii::t('LdapModule.base', 'The default credentials password (used only with username above).'),
            'baseDn' => Yii::t('LdapModule.base', 'The default base DN used for searching for accounts.'),
            'usernameAttribute' => Yii::t('LdapModule.base', 'LDAP Attribute for Username. Example: &quot;uid&quot; or &quot;sAMAccountName&quot;'),
            'emailAttribute' => Yii::t('LdapModule.base', 'LDAP Attribute for E-Mail Address. Default: &quot;mail&quot;'),
            'idAttribute' => Yii::t('LdapModule.base', 'Not changeable LDAP attribute to unambiguously identify the user in the directory. If empty the user will be determined automatically by e-mail address or username. Examples: objectguid (ActiveDirectory) or uidNumber (OpenLDAP)'),
            'userFilter' => Yii::t('LdapModule.base', 'Limit access to users meeting this criteria. Example: &quot;(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;'),
            'ignoredDNs' => Yii::t('LdapModule.base', 'One DN per line which should not be imported automatically.'),
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
        $this->disableCertificateChecking = $settings->get('disableCertificateChecking');
        $this->baseDn = $settings->get('baseDn');

        $this->userFilter = $settings->get('userFilter');

        $this->usernameAttribute = $settings->get('usernameAttribute');
        $this->emailAttribute = $settings->get('emailAttribute');
        $this->idAttribute = $settings->get('idAttribute');

        $this->ignoredDNs = $settings->get('ignoredDNs');
        $this->refreshUsers = $settings->get('refreshUsers');

        // Fresh installs get LDAP password login pre-enabled. Existing installs
        // upgrade transparently because the previous save() always wrote
        // 'ldap' into the stored list. An empty stored list is also normalised
        // back to ['ldap'] so the system always has at least one usable login
        // path for LDAP users.
        $saved = $settings->get('allowedAuthClientIds');
        $decoded = $saved !== null ? (json_decode($saved, true) ?? []) : [];
        $this->allowedAuthClientIds = $decoded !== [] ? $decoded : ['ldap'];
    }


    /**
     * Saves the form
     *
     * @return bool
     */
    public function save()
    {
        /** @var SettingsManager $settings */
        $settings = Yii::$app->getModule('ldap')->settings;

        $settings->set('enabled', $this->enabled);
        $settings->set('hostname', $this->hostname);
        $settings->set('port', $this->port);
        $settings->set('encryption', $this->encryption);
        $settings->set('disableCertificateChecking', $this->disableCertificateChecking);
        $settings->set('username', $this->username);
        if ($this->passwordField !== static::PASSWORD_FIELD_DUMMY) {
            $settings->set('password', $this->passwordField);
        }
        $settings->set('baseDn', $this->baseDn);
        $settings->set('userFilter', $this->userFilter);
        $settings->set('usernameAttribute', $this->usernameAttribute);
        $settings->set('emailAttribute', $this->emailAttribute);
        $settings->set('ignoredDNs', $this->ignoredDNs);
        $settings->set('idAttribute', $this->idAttribute);
        $settings->set('refreshUsers', $this->refreshUsers);
        $settings->set('allowedAuthClientIds', json_encode(
            array_values(array_unique($this->allowedAuthClientIds)),
        ));

        return true;
    }


    /**
     * Returns an LdapConnectionConfig populated from the saved settings.
     * The default 'ldap' connection in {@see LdapConnectionRegistry} is built
     * from this config.
     *
     * @since 1.19
     */
    public function getConnectionConfig(): LdapConnectionConfig
    {
        $ignoredDNs = explode("\n", strtolower(str_replace("\r", '', $this->ignoredDNs ?? '')));

        return new LdapConnectionConfig([
            'title' => 'LDAP',
            'hostname' => (string)$this->hostname,
            'port' => (int)$this->port,
            'useSsl' => ($this->encryption === 'ssl'),
            'useStartTls' => ($this->encryption === 'tls'),
            'disableCertificateChecking' => (bool)$this->disableCertificateChecking,
            'bindUsername' => (string)$this->username,
            'bindPassword' => (string)$this->password,
            'baseDn' => (string)$this->baseDn,
            'userFilter' => (string)$this->userFilter,
            'autoRefreshUsers' => (bool)$this->refreshUsers,
            'emailAttribute' => $this->emailAttribute ?: 'mail',
            'usernameAttribute' => $this->usernameAttribute ?: 'samaccountname',
            'idAttribute' => $this->idAttribute ?: null,
            'ignoredDNs' => $ignoredDNs,
        ]);
    }

    /**
     * Returns auth client options available for LDAP users. Includes 'ldap'
     * (LDAP password login) as a first-class checkbox entry. Excludes 'local'
     * — local password login for LDAP users is not exposed here.
     * Keys are client IDs, values are display titles.
     *
     * @return array<string, string>
     */
    public function getAuthClientOptions(): array
    {
        /** @var Collection $collection */
        $collection = Yii::$app->authClientCollection;
        $options = [];
        foreach ($collection->getClients() as $id => $client) {
            if ($id !== 'local') {
                $options[$id] = $client->getTitle();
            }
        }
        return $options;
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
