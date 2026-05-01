<?php

namespace humhub\modules\ldap\authclient;

use DateTime;
use Exception;
use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\ldap\services\LdapService;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\authclient\interfaces\ApprovalBypass;
use humhub\modules\user\authclient\interfaces\AutoSyncUsers;
use humhub\modules\user\authclient\interfaces\PrimaryClient;
use humhub\modules\user\authclient\interfaces\SyncAttributes;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\services\AuthClientService;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * LDAP Authentication
 *
 * @since 1.1
 */
class LdapAuth extends BaseFormAuth implements AutoSyncUsers, SyncAttributes, ApprovalBypass, PrimaryClient
{
    /**
     * @var string the auth client id
     */
    public $clientId = 'ldap';

    /**
     * The hostname of LDAP server that these options represent. This option is required.
     *
     * @var string
     */
    public $hostname;

    /**
     * The port on which the LDAP server is listening.
     *
     * @var int 389
     */
    public $port;

    /**
     * Whether or not the LDAP client should use SSL encrypted transport.
     * The useSsl and useStartTls options are mutually exclusive, but useStartTls should be favored
     * if the server and LDAP client library support it.
     *
     * @var bool
     */
    public $useSsl = false;

    /**
     * Whether or not the LDAP client should use TLS (aka SSLv2) encrypted transport.
     * A value of TRUE is strongly favored in production environments to prevent passwords from be transmitted in clear text.
     *
     * The default value is FALSE, as servers frequently require that a certificate be installed separately after installation.
     * The useSsl and useStartTls options are mutually exclusive.
     * The useStartTls option should be favored over useSsl but not all servers support this newer mechanism.
     *
     * @var bool
     */
    public $useStartTls = false;

    /**
     * Disables Certificate Checking
     * A value of FALSE is strongly favored in production environments.
     *
     * The default value is FALSE, as production servers should use a valid certificate chain.
     *
     * @var bool
     */
    public $disableCertificateChecking = false;

    /**
     * The DN of the account used to perform account DN lookups.
     * LDAP servers that require the username to be in DN form when performing the “bind” require this option.
     *
     * @var string
     */
    public $bindUsername;

    /**
     * The password of the account used to perform account DN lookups.
     *
     * @var string
     */
    public $bindPassword;

    /**
     * ID attribute to uniquely identify user.
     * If set to null, automatically a value email or objectguid will be used if available.
     *
     * @var string attribute name to identify node
     */
    public $idAttribute = null;

    /**
     * @var string the email attribute
     */
    public $emailAttribute = null;

    /**
     * @var string the ldap username attribute
     */
    public $usernameAttribute = null;

    public $languageAttribute = 'preferredLanguage';

    /**
     * @var string the ldap base dn
     */
    public $baseDn = null;

    /**
     * @var string the ldap query to find humhub users
     */
    public $userFilter = null;

    /**
     * Automatically refresh user profiles on cron run
     *
     * @var bool|null
     */
    public $autoRefreshUsers = null;

    /**
     * @inheritdoc
     */
    public $byPassApproval = true;

    /**
     * @var array of attributes which are synced with the user table
     */
    public $syncUserTableAttributes = ['username', 'email'];

    /**
     * @var int The value for network timeout when connect to the LDAP server.
     */
    public $networkTimeout = 30;

    /**
     * @var string[] a list of ignored DNs (lowercase)
     * @since 1.9
     */
    public $ignoredDNs = [];

    public ?LdapService $ldapService = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->idAttribute)) {
            $this->idAttribute = null;
        } else {
            $this->idAttribute = strtolower($this->idAttribute);
        }

        if (empty($this->usernameAttribute)) {
            $this->usernameAttribute = 'samaccountname';
        }
        $this->usernameAttribute = strtolower($this->usernameAttribute);

        if (empty($this->emailAttribute)) {
            $this->emailAttribute = 'mail';
        }
        $this->emailAttribute = strtolower($this->emailAttribute);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->clientId;
    }

    public function getLdapService(): LdapService
    {
        if ($this->ldapService === null) {
            $this->ldapService = new LdapService($this);
        }
        return $this->ldapService;
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return $this->clientId;
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'LDAP (' . $this->clientId . ')';
    }

    /**
     * @inheritdoc
     */
    public function getIdAttribute()
    {
        return $this->idAttribute;
    }

    /**
     * Find user based on ldap attributes
     *
     * @inheritdoc
     * @return User the user
     * @see PrimaryClient
     */
    public function getUser()
    {
        $attributes = $this->getUserAttributes();

        // Try to load user by ldap id attribute
        if ($this->idAttribute !== null && isset($attributes['authclient_id'])) {
            $user = User::findOne(['authclient_id' => $attributes['authclient_id'], 'auth_mode' => $this->getId()]);
            if ($user !== null) {
                return $user;
            }
        }

        return $this->getUserAuto();
    }

    /**
     * Try to find the user if authclient_id mapping is not set yet (legency)
     * or idAttribute is not specified.
     *
     * @return User
     */
    protected function getUserAuto()
    {
        $attributes = $this->getUserAttributes();

        // Try to find user user if authclient_id is null based on ldap fields objectguid and e-mail
        $query = User::find();
        $query->where(['auth_mode' => $this->getId()]);

        if ($this->idAttribute !== null) {
            $query->andWhere(['IS', 'authclient_id', new Expression('NULL')]);
        }

        $conditions = ['OR'];
        if (isset($attributes['email']) && !empty($attributes['email'])) {
            $conditions[] = ['email' => $attributes['email']];
        }
        if (isset($attributes['objectguid']) && !empty($attributes['objectguid'])) {
            $conditions[] = ['guid' => $attributes['objectguid']];
        }
        if (isset($attributes['uid']) && !empty($attributes['uid'])) {
            $conditions[] = ['username' => $attributes['uid']];
        }
        if ($conditions) {
            $query->andWhere($conditions);
        }

        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public function auth()
    {
        try {
            $ldapService = $this->getLdapService();
            $dn = $ldapService->attemptAuth($this->login->username, $this->login->password);
        } catch (\Exception $e) {
            Yii::error('LDAP authentication error: ' . $e->getMessage(), 'ldap');
            return false;
        }

        // Login failed
        if ($dn === null) {
            if ($this->login instanceof Login) {
                $this->countFailedLoginAttempts();
            }
            return false;
        }

        $this->setUserAttributes($ldapService->getEntry($dn));
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        $map = [];
        $map['username'] = $this->usernameAttribute;
        $map['email'] = $this->emailAttribute;
        $map['language'] = $this->languageAttribute;

        // Profile Field Mapping
        foreach (ProfileField::find()->andWhere(['!=', 'ldap_attribute', ''])->all() as $profileField) {
            $map[$profileField->internal_name] = strtolower($profileField->ldap_attribute);
        }

        return $map;
    }

    /**
     * @inheritdoc
     */
    protected function normalizeUserAttributes($attributes)
    {
        $normalized = LdapHelper::dropMultiValues($attributes, ['memberof', 'ismemberof']);

        // Handle date fields (formats are specified in config)
        foreach ($normalized as $name => $value) {
            if (isset(Yii::$app->params['ldap']['dateFields'][$name]) && $value != '') {
                $dateFormat = Yii::$app->params['ldap']['dateFields'][$name];
                $date = DateTime::createFromFormat($dateFormat, $value ?? '');

                if ($date !== false) {
                    $normalized[$name] = $date->format('Y-m-d');
                } else {
                    $normalized[$name] = '';
                }
            }
        }

        if ($this->idAttribute !== null && isset($normalized[$this->idAttribute])) {
            $normalized['authclient_id'] = $normalized[$this->idAttribute];
        }

        $normalized['id'] = 'unused';

        return parent::normalizeUserAttributes($normalized);
    }

    /**
     * @return array list of user attributes
     */
    public function getUserAttributes()
    {
        $attributes = parent::getUserAttributes();

        // Make sure id attributes sits on id attribute key
        if (isset($attributes[$this->getIdAttribute()])) {
            $attributes['id'] = $attributes[$this->getIdAttribute()];
        }

        return $attributes;
    }


    /**
     * @inheritdoc
     */
    public function getSyncAttributes()
    {
        $attributes = $this->syncUserTableAttributes;
        $attributes[] = 'authclient_id';

        foreach (ProfileField::find()->andWhere(['!=', 'ldap_attribute', ''])->all() as $profileField) {
            $attributes[] = $profileField->internal_name;
        }

        return $attributes;
    }

    /**
     * Refresh ldap users
     *
     * New users (found in ldap) will be automatically created if all required fiélds are set.
     * Profile fields which are bind to LDAP will automatically updated.
     */
    public function syncUsers()
    {
        if ($this->autoRefreshUsers !== true) {
            return;
        }

        try {
            $ids = [];
            foreach ($this->getLdapService()->getAuthClients() as $dn => $authClient) {
                $user = (new AuthClientService($authClient))->getUser();
                if ($user === null) {
                    $registration = (new AuthClientService($authClient))->createRegistration();
                    if ($registration === null) {
                        Yii::warning('Could not automatically create LDAP user  - No ID attribute!', 'ldap');
                        continue;
                    }

                    if (!$registration->register($authClient)) {
                        Yii::warning(
                            'Could not create LDAP user (' . $dn . '). Error: '
                            . VarDumper::dumpAsString($registration->getErrors()),
                            'ldap'
                        );
                    }
                } else {
                    (new AuthClientService($authClient))->updateUser($user);
                }

                $attributes = $authClient->getUserAttributes();
                if (isset($attributes['authclient_id'])) {
                    $ids[] = $attributes['authclient_id'];
                }
            }

            // Disable or Reenable Users based on collected $ids Arrays
            // This is only possible if a unique id attribute is specified.
            if ($this->idAttribute !== null) {
                foreach ((new AuthClientService($this))->getUsersQuery()->each() as $user) {
                    $foundInLdap = in_array($user->authclient_id, $ids);
                    if ($foundInLdap && $user->status === User::STATUS_DISABLED) {
                        // Enable disabled users that have been found in ldap
                        $user->status = User::STATUS_ENABLED;
                        $user->save();
                        Yii::info('Enabled user: ' . $user->username . ' (' . $user->id . ') - Found in LDAP!', 'ldap');
                    } elseif (!$foundInLdap && $user->status == User::STATUS_ENABLED) {
                        // Disable users that were not found in ldap
                        $user->status = User::STATUS_DISABLED;
                        $user->save();
                        Yii::warning(
                            'Disabled user: ' . $user->username . ' (' . $user->id . ') - Not found in LDAP!',
                            'ldap'
                        );
                    }
                }
            }
        } catch (Exception $ex) {
            Yii::error('An error occurred while user sync: ' . $ex->getMessage(), 'ldap');
        }
    }

    /**
     * @param array $normalizeUserAttributeMap normalize user attribute map.
     */
    public function setNormalizeUserAttributeMap($normalizeUserAttributeMap)
    {
        // This method is called if an additional attribute mapping is specified in the configuration file
        // So automatically merge HumHub auto mapping with the given one
        $this->init(); // defaultNormalizeAttributeMap is available after init
        parent::setNormalizeUserAttributeMap(
            ArrayHelper::merge($this->defaultNormalizeUserAttributeMap(), $normalizeUserAttributeMap)
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSerialize(): void
    {
        // Make sure we normalized user attributes before put it in session (anonymous functions)
        $this->setNormalizeUserAttributeMap([]);
        // LDAP\Connection handles cannot be serialized; drop the service so it is re-created on next use
        $this->ldapService = null;
    }
}
