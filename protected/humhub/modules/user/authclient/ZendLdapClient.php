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
use humhub\models\Setting;
use humhub\modules\user\models\User;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\authclient\AuthClientHelpers;

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
     * ID attribute to uniquely identify user
     * If set to null, automatically a value email or objectguid will be used if available.
     * 
     * @var string attribute name to identify node 
     */
    public $idAttribute = null;

    /**
     * @var string attribute name to user record
     */
    public $userTableIdAttribute = 'guid';

    /**
     * @inheritdoc
     */
    public $byPassApproval = true;

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
     * @inheritdoc
     */
    public function getUserTableIdAttribute()
    {
        return $this->userTableIdAttribute;
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

        // Username field 
        $usernameAttribute = Setting::Get('auth.ldap.usernameAttribute', 'user');
        if ($usernameAttribute == '') {
            $usernameAttribute = 'sAMAccountName';
        }
        $map['username'] = strtolower($usernameAttribute);

        // E-Mail field 
        $emailAttribute = Setting::Get('auth.ldap.emailAttribute', 'user');
        if ($emailAttribute == '') {
            $emailAttribute = 'mail';
        }
        $map['email'] = strtolower($emailAttribute);

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
            if (is_array($value) && count($value) == 1) {
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


        return parent::normalizeUserAttributes($normalized);
    }

    /**
     * @return array list of user attributes
     */
    public function getUserAttributes()
    {
        $attributes = parent::getUserAttributes();

        // Try to automatically set id and usertable id attribute by available attributes
        if ($this->getIdAttribute() === null || $this->getUserTableIdAttribute() === null) {
            if (isset($attributes['objectguid'])) {
                $this->idAttribute = 'objectguid';
                $this->userTableIdAttribute = 'guid';
            } elseif (isset($attributes['mail'])) {
                $this->idAttribute = 'mail';
                $this->userTableIdAttribute = 'email';
            } else {
                throw new \yii\base\Exception("Could not automatically determine unique user id from ldap node!");
            }
        }

        // Make sure id attributes sits on id attribute key
        if (isset($attributes[$this->getIdAttribute()])) {
            $attributes['id'] = $attributes[$this->getIdAttribute()];
        }

        // Map usertable id attribute against ldap id attribute
        $attributes[$this->getUserTableIdAttribute()] = $attributes[$this->getIdAttribute()];

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
        // ToDo: Search user even by e-mail address
        try {
            $this->getLdap()->bind($this->login->username, $this->login->password);
            return $this->getLdap()->getCanonicalAccountName($this->login->username, Ldap::ACCTNAME_FORM_DN);
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
            $options = array(
                'host' => Setting::Get('auth.ldap.hostname', 'user'),
                'port' => Setting::Get('auth.ldap.port', 'user'),
                'username' => Setting::Get('auth.ldap.username', 'user'),
                'password' => Setting::Get('auth.ldap.password', 'user'),
                'useStartTls' => (Setting::Get('auth.ldap.encryption', 'user') == 'tls'),
                'useSsl' => (Setting::Get('auth.ldap.encryption', 'user') == 'ssl'),
                'bindRequiresDn' => true,
                'baseDn' => Setting::Get('auth.ldap.baseDn', 'user'),
                'accountFilterFormat' => Setting::Get('auth.ldap.loginFilter', 'user'),
            );

            $this->_ldap = new \Zend\Ldap\Ldap($options);
            $this->_ldap->bind();
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
        $attributes = ['username', 'email'];

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
        if (!Setting::Get('auth.ldap.enabled', 'user') || !Setting::Get('auth.ldap.refreshUsers', 'user')) {
            return;
        }

        $userFilter = Setting::Get('auth.ldap.userFilter', 'user');
        $baseDn = Setting::Get('auth.ldap.baseDn', 'user');
        $userCollection = $this->getLdap()->search($userFilter, $baseDn, Ldap::SEARCH_SCOPE_SUB);

        $ids = [];
        foreach ($userCollection as $attributes) {
            $authClient = new static;
            $authClient->setUserAttributes($attributes);
            $attributes = $authClient->getUserAttributes();

            $user = AuthClientHelpers::getUserByAuthClient($authClient);
            if ($user === null) {
                if (!AuthClientHelpers::createUser($authClient)) {
                    Yii::warning('Could not automatically create LDAP user ' . $attributes['email'] . ' - check required attributes!');
                }
            } else {
                AuthClientHelpers::updateUser($authClient, $user);
            }

            $ids[] = $attributes['id'];
        }

        /**
         * Since userTableAttribute can be automatically set on user attributes
         * try to take it from initialized authclient instance.
         */
        $userTableIdAttribute = $this->getUserTableIdAttribute();
        if ($authClient !== null) {
            $userTableIdAttribute = $authClient->getUserTableIdAttribute();
        }

        // Disable not longer existing users
        foreach (AuthClientHelpers::getUsersByAuthClient($this)->each() as $user) {
            if ($user->status !== User::STATUS_DISABLED && !in_array($user->getAttribute($userTableIdAttribute), $ids)) {
                $user->status = User::STATUS_DISABLED;
                $user->save();
                Yii::warning('Disabled user ' . $user->username . ' (' . $user->id . ') - Not found in LDAP!');
            }
        }
    }

    /**
     * Checks if LDAP is supported
     */
    public static function isLdapAvailable()
    {
        if (!class_exists('Zend\Ldap\Ldap')) {
            return false;
        }

        if (!function_exists('ldap_bind')) {
            return false;
        }

        return true;
    }

}
