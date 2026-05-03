<?php

namespace humhub\modules\ldap\authclient;

use DateTime;
use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\ldap\services\LdapService;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\authclient\interfaces\ApprovalBypass;
use humhub\modules\user\authclient\interfaces\SerializableAuthClient;
use humhub\modules\user\source\HasUserSource;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * LDAP Authentication
 *
 * @since 1.1
 */
class LdapAuth extends BaseFormAuth implements HasUserSource, ApprovalBypass, SerializableAuthClient
{
    /**
     * @var string the auth client id
     */
    public $clientId = 'ldap';

    /**
     * Auth client IDs that LDAP users are allowed to use.
     * Defaults to only LDAP itself — extend to e.g. ['ldap', 'local'] to allow password login.
     *
     * @var string[]
     */
    public array $allowedAuthClientIds = ['ldap'];

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
     * LDAP servers that require the username to be in DN form when performing the "bind" require this option.
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

    private ?LdapUserSource $_userSource = null;

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

    public function getUserSource(): LdapUserSource
    {
        if ($this->_userSource === null) {
            $this->_userSource = new LdapUserSource($this);
        }
        return $this->_userSource;
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
     * Find user based on LDAP attributes.
     *
     * Primary lookup uses the user_auth table (source + source_id).
     * Falls back to matching by user_source + email/objectguid/username for
     * users without an idAttribute or not yet migrated.
     */
    public function getUser(): ?User
    {
        $attributes = $this->getUserAttributes();

        // Primary lookup: user_auth table
        if (isset($attributes['id'])) {
            $auth = Auth::find()
                ->where(['source' => $this->getId(), 'source_id' => (string)$attributes['id']])
                ->one();
            if ($auth !== null) {
                return $auth->user;
            }
        }

        // Fallback: match by user_source + email/objectguid/username
        return $this->getUserFallback($attributes);
    }

    /**
     * Fallback user lookup for users without a unique id attribute or legacy installs
     * where user_auth entries may not yet exist.
     */
    private function getUserFallback(array $attributes): ?User
    {
        $query = User::find()->where(['user_source' => $this->getId()]);

        $conditions = ['OR'];
        if (!empty($attributes['email'])) {
            $conditions[] = ['email' => $attributes['email']];
        }
        if (!empty($attributes['objectguid'])) {
            $conditions[] = ['guid' => $attributes['objectguid']];
        }
        if (!empty($attributes['uid'])) {
            $conditions[] = ['username' => $attributes['uid']];
        }

        if (count($conditions) <= 1) {
            return null;
        }

        return $query->andWhere($conditions)->one();
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
            $normalized['id'] = $normalized[$this->idAttribute];
        }

        return parent::normalizeUserAttributes($normalized);
    }

    /**
     * @return array list of user attributes
     */
    public function getUserAttributes()
    {
        $attributes = parent::getUserAttributes();

        // Make sure id attribute sits on id attribute key
        if ($this->getIdAttribute() !== null && isset($attributes[$this->getIdAttribute()])) {
            $attributes['id'] = $attributes[$this->getIdAttribute()];
        }

        return $attributes;
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
            ArrayHelper::merge($this->defaultNormalizeUserAttributeMap(), $normalizeUserAttributeMap),
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
        $this->_userSource = null;
    }
}
