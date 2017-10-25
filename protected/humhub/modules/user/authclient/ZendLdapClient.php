<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace humhub\modules\user\authclient;

use Yii;
use Zend\Ldap\Ldap;
use Zend\Ldap\Node;
use Zend\Ldap\Exception\LdapException;
use humhub\modules\user\models\User;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\libs\LdapHelper;

/**
 * LDAP Authentication
 *
 * @todo create base ldap authentication, to bypass ApprovalByPass Interface
 * @since 1.1
 */
class ZendLdapClient extends BaseFormAuth implements interfaces\AutoSyncUsers, interfaces\SyncAttributes, interfaces\ApprovalBypass, interfaces\PrimaryClient
{

    /**
     * @var \Zend\Ldap\Ldap
     */
    private $_ldap = null;

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
     * @var boolean|null
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settings = Yii::$app->getModule('user')->settings;

        if ($this->idAttribute === null) {
            $idAttribute = $settings->get('auth.ldap.idAttribute');
            if (!empty($idAttribute)) {
                $this->idAttribute = strtolower($idAttribute);
            }
        }

        if ($this->usernameAttribute === null) {
            $usernameAttribute = $settings->get('auth.ldap.usernameAttribute');
            if (!empty($usernameAttribute)) {
                $this->usernameAttribute = strtolower($usernameAttribute);
            } else {
                $this->usernameAttribute = 'samaccountname';
            }
        }

        if ($this->emailAttribute === null) {
            $emailAttribute = $settings->get('auth.ldap.emailAttribute');
            if (!empty($emailAttribute)) {
                $this->emailAttribute = strtolower($emailAttribute);
            } else {
                $this->emailAttribute = 'mail';
            }
        }

        if ($this->userFilter === null) {
            $this->userFilter = Yii::$app->getModule('user')->settings->get('auth.ldap.userFilter');
        }

        if ($this->baseDn === null) {
            $this->baseDn = Yii::$app->getModule('user')->settings->get('auth.ldap.baseDn');
        }

        if ($this->autoRefreshUsers === null) {
            $this->autoRefreshUsers = (boolean) Yii::$app->getModule('user')->settings->get('auth.ldap.refreshUsers');
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return 'ldap';
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'ldap';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'LDAP';
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
     * @see interfaces\PrimaryClient
     * @return User the user
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
     * @return type
     */
    protected function getUserAuto()
    {
        $attributes = $this->getUserAttributes();

        // Try to find user user if authclient_id is null based on ldap fields objectguid and e-mail
        $query = User::find();
        $query->where(['auth_mode' => $this->getId()]);

        if ($this->idAttribute !== null) {
            $query->andWhere(['IS', 'authclient_id', new \yii\db\Expression('NULL')]);
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
        if ($conditions)
            $query->andWhere($conditions);

        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public function auth()
    {
        $node = $this->getUserNode();
        if ($node !== null) {
            $this->setUserAttributes($node->getAttributes());
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        $map = [];
        $map['username'] = $this->usernameAttribute;
        $map['email'] = $this->emailAttribute;

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
        $normalized = [];

        // Fix LDAP Attributes
        foreach ($attributes as $name => $value) {
            if (is_array($value) && count($value) == 1 && $name != 'memberof') {
                $normalized[$name] = $value[0];
            } else {
                $normalized[$name] = $value;
            }
        }

        if (isset($normalized['objectguid'])) {
            $normalized['objectguid'] = \humhub\libs\StringHelper::binaryToGuid($normalized['objectguid']);
        }

        // Handle date fields (formats are specified in config)
        foreach ($normalized as $name => $value) {
            if (isset(Yii::$app->params['ldap']['dateFields'][$name]) && $value != '') {
                $dateFormat = Yii::$app->params['ldap']['dateFields'][$name];
                $date = \DateTime::createFromFormat($dateFormat, $value);

                if ($date !== false) {
                    $normalized[$name] = $date->format('Y-m-d 00:00:00');
                } else {
                    $normalized[$name] = "";
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
     * Returns Users LDAP Node
     *
     * @return Node the users ldap node
     */
    protected function getUserNode()
    {
        $dn = $this->getUserDn();
        if ($dn !== '') {
            return $this->getLdap()->getNode($dn);
        }

        return null;
    }

    /**
     * Returns the users LDAP DN
     *
     * @return string the user dn if found
     */
    protected function getUserDn()
    {
        $userName = $this->login->username;

        // Translate given e-mail to username
        if (strpos($userName, '@') !== false) {
            $user = User::findOne(['email' => $userName]);
            if ($user !== null) {
                $userName = $user->username;
            }
        }

        try {
            $this->getLdap()->bind($userName, $this->login->password);
            $dn = $this->getLdap()->getCanonicalAccountName($userName, Ldap::ACCTNAME_FORM_DN);
            
            // Rebind with administrative DN
            $this->getLdap()->bind();

            return $dn;
        } catch (LdapException $ex) {
            // User not found in LDAP
        }
        return '';
    }

    /**
     * Returns Zend LDAP
     *
     * @return \Zend\Ldap\Ldap
     */
    public function getLdap()
    {
        if ($this->_ldap === null) {
            $this->_ldap = LdapHelper::getLdapConnection();
        }

        return $this->_ldap;
    }

    /**
     * Sets an Zend LDAP Instance
     *
     * @param \Zend\Ldap\Ldap $ldap
     */
    public function setLdap(\Zend\Ldap\Ldap $ldap)
    {
        $this->_ldap = $ldap;
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
        if (!LdapHelper::isLdapEnabled() || !$this->autoRefreshUsers) {
            return;
        }

        try {
            $ldap = $this->getLdap();

            $userCollection = $ldap->search($this->userFilter, $this->baseDn, Ldap::SEARCH_SCOPE_SUB);

            $authClient = null;
            $ids = [];
            foreach ($userCollection as $attributes) {
                $authClient = clone $this;
                $authClient->init();
                $authClient->setUserAttributes($attributes);
                $attributes = $authClient->getUserAttributes();

                $user = AuthClientHelpers::getUserByAuthClient($authClient);
                if ($user === null) {
                    if (!AuthClientHelpers::createUser($authClient)) {
                        Yii::warning('Could not automatically create LDAP user  - check required attributes! (' . print_r($attributes, 1) . ')');
                    }
                } else {
                    AuthClientHelpers::updateUser($authClient, $user);
                }

                if (isset($attributes['authclient_id'])) {
                    $ids[] = $attributes['authclient_id'];
                }
            }

            // Disable or Reenable Users based on collected $ids Arrays
            // This is only possible if a unique id attribute is specified.
            if ($this->idAttribute !== null) {
                foreach (AuthClientHelpers::getUsersByAuthClient($this)->each() as $user) {
                    $foundInLdap = in_array($user->authclient_id, $ids);
                    if ($foundInLdap && $user->status === User::STATUS_DISABLED) {
                        // Enable disabled users that have been found in ldap
                        $user->status = User::STATUS_ENABLED;
                        $user->save();
                        Yii::info('Enabled user' . $user->username . ' (' . $user->id . ') - found in LDAP!');
                    } elseif (!$foundInLdap && $user->status !== User::STATUS_DISABLED) {
                        // Disable users that were not found in ldap
                        $user->status = User::STATUS_DISABLED;
                        $user->save();
                        Yii::warning('Disabled user' . $user->username . ' (' . $user->id . ') - not found in LDAP!');
                    }
                }
            }
        } catch (\Zend\Ldap\Exception\LdapException $ex) {
            Yii::error('Could not connect to LDAP instance: ' . $ex->getMessage());
        } catch (\Exception $ex) {
            Yii::error('An error occurred while user sync: ' . $ex->getMessage());
        }
    }

    /**
     * Checks if LDAP is supported
     * 
     * @deprecated since version 1.2.3
     * @return boolean is LDAP supported (drivers, modules)
     */
    public static function isLdapAvailable()
    {
        return LdapHelper::isLdapAvailable();
    }

}
