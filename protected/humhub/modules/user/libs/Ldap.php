<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\libs;

use Exception;
use Yii;
use yii\base\Object;
use humhub\models\Setting;
use humhub\libs\ParameterEvent;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\ProfileField;
use humhub\modules\space\models\Space;

/**
 * LDAP Connector
 *
 * @since 0.5
 */
class Ldap extends \yii\base\Component
{

    /**
     * @event event when a ldap user is updated
     */
    const EVENT_UPDATE_USER = 'update_user';

    /**
     * @var Zend_Ldap instance
     */
    public $ldap = null;

    /**
     * @var SILDAP instance
     */
    static private $instance = null;

    /**
     * Returns the current HLdap Instance.
     *
     * @return HLdap
     */
    static public function getInstance()
    {

        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @var User currently handled user
     */
    public $currentUser = null;

    /**
     * Creates singleton HLdap Instance which configured Zend_Ldap Class
     */
    public function __construct()
    {

        try {
            $options = array(
                'host' => Setting::Get('hostname', 'authentication_ldap'),
                'port' => Setting::Get('port', 'authentication_ldap'),
                'username' => Setting::Get('username', 'authentication_ldap'),
                'password' => Setting::Get('password', 'authentication_ldap'),
                'useStartTls' => (Setting::Get('encryption', 'authentication_ldap') == 'tls'),
                'useSsl' => (Setting::Get('encryption', 'authentication_ldap') == 'ssl'),
                'bindRequiresDn' => true,
                'baseDn' => Setting::Get('baseDn', 'authentication_ldap'),
                'accountFilterFormat' => Setting::Get('loginFilter', 'authentication_ldap'),
            );

            $this->ldap = new \Zend\Ldap\Ldap($options);
            $this->ldap->bind();
        } catch (\Zend\Ldap\Exception\LdapException $ex) {
            Yii::error('Cound not bind to LDAP Server. Error: ' . $ex->getMessage());
        } catch (Exception $ex) {
            Yii::error('Cound not bind to LDAP Server. Error: ' . $ex->getMessage());
        }
    }

    /**
     * Authenticates user against LDAP Backend
     * 
     * @param type $username
     * @param type $password
     * @return boolean
     */
    public function authenticate($username, $password)
    {
        try {
            $username = $this->ldap->getCanonicalAccountName($username, \Zend\Ldap\Ldap::ACCTNAME_FORM_DN);
            // check Password
            $this->ldap->bind($username, $password);
            // disconnect id needed here because otherwise binding/ldap connection again can cause errors.
            $this->ldap->disconnect();

            // Update Users Data
            $node = $this->ldap->getNode($username);
            $this->handleLdapUser($node);
            return true;
        } catch (\Zend\Ldap\Exception\LdapException $ex) {
            // log errors other than invalid credentials
            if ($ex->getCode() !== \Zend\Ldap\Exception\LdapException::LDAP_INVALID_CREDENTIALS) {
                Yii::error('LDAP Error: ' . $ex->getMessage());
            }
            return false;
        } catch (Exception $ex) {
            Yii::error('LDAP Error: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * Reads out all users from configured ldap backend and creates or update
     * existing users.
     * 
     * Also disabling deleted ldap users in humhub
     */
    public function refreshUsers()
    {

        $ldapUserIds = array();

        try {
            $items = $this->ldap->search(Setting::Get('userFilter', 'authentication_ldap'), Setting::Get('baseDn', 'authentication_ldap'), \Zend\Ldap\Ldap::SEARCH_SCOPE_SUB);
            foreach ($items as $item) {
                $node = \Zend\Ldap\Node::fromArray($item);
                $user = $this->handleLdapUser($node);

                if ($user != null)
                    $ldapUserIds[] = $user->id;
            }


            foreach (User::find()->where(['auth_mode' => User::AUTH_MODE_LDAP])->each() as $user) {
                if (!in_array($user->id, $ldapUserIds)) {
                    if ($user->status != User::STATUS_DISABLED) {
                        // User no longer available in ldap
                        $user->status = User::STATUS_DISABLED;
                        \humhub\modules\user\models\Setting::Set($user->id, 'disabled_by_ldap', true, 'user');
                        $user->save();
                        Yii::warning('Disabled user ' . $user->username . ' (' . $user->id . ') - Not found in LDAP!');
                    }
                } else {
                    if ($user->status == User::STATUS_DISABLED && \humhub\modules\user\models\Setting::Get($user->id, 'disabled_by_ldap', 'user', false)) {
                        // User no longer available in ldap
                        $user->status = User::STATUS_ENABLED;
                        \humhub\modules\user\models\Setting::Set($user->id, 'disabled_by_ldap', '', 'user');
                        $user->save();
                        Yii::info('Reenabled disabled user ' . $user->username . ' (' . $user->id . ') - Found again in LDAP!');
                    }
                }
            }
        } catch (Exception $ex) {
            Yii::error($ex->getMessage());
        }
    }

    /**
     * Updates or creates user by given ldap node
     * 
     * @param Zend_Ldap_Node $node
     * @return User User Object
     */
    public function handleLdapUser($node)
    {

        $usernameAttribute = Setting::Get('usernameAttribute', 'authentication_ldap');
        if ($usernameAttribute == '') {
            $usernameAttribute = 'sAMAccountName';
        }

        $emailAttribute = Setting::Get('emailAttribute', 'authentication_ldap');
        if ($emailAttribute == '') {
            $emailAttribute = 'mail';
        }

        $username = $node->getAttribute($usernameAttribute, 0);
        $email = $node->getAttribute($emailAttribute, 0);
        $guid = $this->binToStrGuid($node->getAttribute('objectGUID', 0));

        // Try to load User:
        $userChanged = false;
        $user = null;
        if ($guid != "") {
            $user = User::findOne(array('guid' => $guid, 'auth_mode' => User::AUTH_MODE_LDAP));
        } else {
            // Fallback use e-mail
            $user = User::findOne(array('email' => $email, 'auth_mode' => User::AUTH_MODE_LDAP));
        }

        if ($user === null) {
            $user = new User();
            if ($guid != "") {
                $user->guid = $guid;
            }
            $user->status = User::STATUS_ENABLED;
            $user->auth_mode = User::AUTH_MODE_LDAP;
            $user->group_id = 1;

            Yii::info('Create ldap user ' . $username . '!');
        }

        // Update Users Field
        if ($user->username != $username) {
            $userChanged = true;
            $user->username = $username;
        }
        if ($user->email != $email) {
            $userChanged = true;
            $user->email = $email;
        }

        if ($user->validate()) {

            // Only Save user when something is changed
            if ($userChanged || $user->isNewRecord) {
                $user->save();
            }

            // Update Profile Fields
            foreach (ProfileField::find()->andWhere(['!=', 'ldap_attribute', ''])->all() as $profileField) {
                $ldapAttribute = $profileField->ldap_attribute;
                $ldapValue = $node->getAttribute($ldapAttribute, 0);
                $profileFieldName = $profileField->internal_name;

                // Handle date fields (formats are specified in config)
                if (isset(Yii::$app->params['ldap']['dateFields'][$ldapAttribute]) && $ldapValue != '') {
                    $dateFormat = Yii::$app->params['ldap']['dateFields'][$ldapAttribute];
                    $date = \DateTime::createFromFormat($dateFormat, $ldapValue);
                    if ($date !== false) {
                        $ldapValue = $date->format('Y-m-d');
                    } else {
                        $ldapValue = "";
                    }
                }

                $user->profile->$profileFieldName = $ldapValue;
            }

            if ($user->profile->validate() && $user->profile->save()) {
                $this->trigger(self::EVENT_UPDATE_USER, new ParameterEvent(['user' => $user, 'node' => $node]));
            } else {
                Yii::error('Could not create or update ldap user profil for user ' . $user->username . '! (' . print_r($user->profile->getErrors(), true) . ")");
            }
        } else {
            Yii::error('Could not create or update ldap user: ' . $user->username . '! (' . print_r($user->getErrors(), true) . ")");
        }

        return $user;
    }

    /**
     * Converts LDAP Binary GUID to Ascii
     * 
     * @param type $object_guid
     * @return type
     */
    private function binToStrGuid($object_guid)
    {
        $hex_guid = bin2hex($object_guid);

        if ($hex_guid == "")
            return "";

        $hex_guid_to_guid_str = '';
        for ($k = 1; $k <= 4; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 8 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-';
        for ($k = 1; $k <= 2; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 12 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-';
        for ($k = 1; $k <= 2; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 16 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-' . substr($hex_guid, 16, 4);
        $hex_guid_to_guid_str .= '-' . substr($hex_guid, 20);

        return strtolower($hex_guid_to_guid_str);
    }

    /**
     * Checks if LDAP is supported
     */
    public static function isAvailable()
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

?>
