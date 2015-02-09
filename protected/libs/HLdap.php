<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * HLdap provides a interface to the Zend_Ldap Class.
 *
 * @package humhub.libs
 * @since 0.5
 */
class HLdap
{

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
     * Creates singleton HLdap Instance which configured Zend_Ldap Class
     */
    public function __construct()
    {

        try {
            $options = array(
                'host' => HSetting::Get('hostname', 'authentication_ldap'),
                'port' => HSetting::Get('port', 'authentication_ldap'),
                'username' => HSetting::Get('username', 'authentication_ldap'),
                'password' => HSetting::Get('password', 'authentication_ldap'),
                'useStartTls' => (HSetting::Get('encryption', 'authentication_ldap') == 'tls'),
                'useSsl' => (HSetting::Get('encryption', 'authentication_ldap') == 'ssl'),
                'bindRequiresDn' => true,
                'baseDn' => HSetting::Get('baseDn', 'authentication_ldap'),
                'accountFilterFormat' => HSetting::Get('loginFilter', 'authentication_ldap'),
            );

            $this->ldap = new Zend_Ldap($options);
            $this->ldap->bind();
        } catch (Exception $ex) {
            Yii::log('Cound not bind to LDAP Server. Error: '. $ex->getMessage(), CLogger::LEVEL_ERROR);
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
        $username = $this->ldap->getCanonicalAccountName($username, Zend_Ldap::ACCTNAME_FORM_DN);
        try {
            $this->ldap->bind($username, $password);

            // Update Users Data
            $node = $this->ldap->getNode($username);
            $this->handleLdapUser($node);

            return true;
        } catch (Exception $ex) {
            return false;
        }
        return false;
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
            $items = $this->ldap->search(HSetting::Get('userFilter', 'authentication_ldap'), HSetting::Get('baseDn', 'authentication_ldap'), Zend_Ldap::SEARCH_SCOPE_SUB);
            foreach ($items as $item) {
                $node = Zend_Ldap_Node::fromArray($item);
                $user = $this->handleLdapUser($node);

                if ($user != null)
                    $ldapUserIds[] = $user->id;
            }


            foreach (User::model()->findAllByAttributes(array('auth_mode' => User::AUTH_MODE_LDAP), 'status!=' . User::STATUS_DISABLED) as $user) {
                if (!in_array($user->id, $ldapUserIds)) {
                    // User no longer available in ldap
                    $user->status = User::STATUS_DISABLED;
                    $user->save();

                    Yii::log('Disabled user ' . $user->username . ' (' . $user->id . ') - Not found in LDAP!', CLogger::LEVEL_ERROR, 'authentication_ldap');
                }
            }
        } catch (Exception $ex) {
            Yii::log($ex->getMessage(), CLogger::LEVEL_ERROR, 'authentication_ldap');
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

        $username = $node->getAttribute(HSetting::Get('usernameAttribute', 'authentication_ldap'), 0);
        $email = $node->getAttribute('mail', 0);
        $guid = $this->binToStrGuid($node->getAttribute('objectGUID', 0));

        // Try to load User:
        $userChanged = false;
        $user = null;
        if ($guid != "") {
            $user = User::model()->findByAttributes(array('guid' => $guid, 'auth_mode' => User::AUTH_MODE_LDAP));
        } else {
            // Fallback use e-mail
            $user = User::model()->findByAttributes(array('email' => $email, 'auth_mode' => User::AUTH_MODE_LDAP));
        }

        if ($user === null) {
            $user = new User();
            if ($guid != "") {
                $user->guid = $guid;
            }
            $user->status = User::STATUS_ENABLED;
            $user->auth_mode = User::AUTH_MODE_LDAP;
            $user->group_id = 1;

            Yii::log('Create ldap user ' . $username . '!', CLogger::LEVEL_INFO, 'authentication_ldap');
        }

        // Update Group Mapping
        foreach (Group::model()->findAll('ldap_dn != ""') as $group) {
            if (in_array($group->ldap_dn, $node->getAttribute('memberOf'))) {
                if ($user->group_id != $group->id) {
                    $userChanged = true;
                    $user->group_id = $group->id;
                }
            }
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
            if ($userChanged || $user->isNewRecord)
                $user->save();

            // Update Profile Fields
            foreach (ProfileField::model()->findAll('ldap_attribute != ""') as $profileField) {
                $ldapAttribute = $profileField->ldap_attribute;
                $profileFieldName = $profileField->internal_name;
                $user->profile->$profileFieldName = $node->getAttribute($ldapAttribute, 0);
            }

            if ($user->profile->validate()) {
                $user->profile->save();

                // Update Space Mapping
                foreach (Space::model()->findAll('ldap_dn != ""') as $space) {
                    if (in_array($space->ldap_dn, $node->getAttribute('memberOf'))) {
                        $space->addMember($user->id);
                    }
                }
            } else {
                Yii::log('Could not create or update ldap user profile! (' . print_r($user->profile->getErrors(), true) . ")", CLogger::LEVEL_ERROR, 'authentication_ldap');
            }
        } else {
            Yii::log('Could not create or update ldap user! (' . print_r($user->getErrors(), true) . ")", CLogger::LEVEL_ERROR, 'authentication_ldap');
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

}

?>
