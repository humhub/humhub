<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 *
 */
class UserIdentity extends CUserIdentity {

    /**
     * @var Intger Users id
     */
    private $_id;

    /**
     * Returns users id
     *
     * @return Integer
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Authenticates a user based on {@link username} and {@link password}.
     *
     * @return boolean whether authentication succeeds.
     */
    public function authenticate() {

        // Find User
        $criteria = new CDbCriteria;
        $criteria->condition = 'username=:userName OR email=:email';
        $criteria->params = array(':userName' => $this->username, ':email' => $this->username);
        $user = User::model()->find($criteria);

        // If user not found in db and ldap is enabled, do ldap lookup and create it when found
        if ($user === null && HSetting::Get('enabled', 'authentication_ldap')) {
            try {
                $usernameDn = HLdap::getInstance()->ldap->getCanonicalAccountName($this->username, Zend_Ldap::ACCTNAME_FORM_DN);
                HLdap::getInstance()->handleLdapUser(HLdap::getInstance()->ldap->getNode($usernameDn));
                $user = User::model()->findByAttributes(array('username' => $this->username));
            } catch (Exception $ex) {
                ;
            }
        }

        // Check State
        if ($user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else {

            if ($user->auth_mode == User::AUTH_MODE_LOCAL) {
                // Authenticate via Local DB 
                if ($user->currentPassword != null && $user->currentPassword->validatePassword($this->password)) {
                    $this->errorCode = self::ERROR_NONE;
                } else {
                    $this->errorCode = self::ERROR_PASSWORD_INVALID;
                }
            } elseif ($user->auth_mode == User::AUTH_MODE_LDAP) {
                // Authenticate via LDAP 
                if (HLdap::getInstance()->authenticate($user->username, $this->password)) {
                    $this->errorCode = self::ERROR_NONE;
                } else {
                    $this->errorCode = self::ERROR_PASSWORD_INVALID;
                }
            }

            if ($this->errorCode == self::ERROR_NONE) {
                $this->onSuccessfulAuthenticate($user);
            }
        }

        return !$this->errorCode;
    }

    /**
     * Authenticates a given username without validating its password.
     *
     * @return boolean whether authentication succeeds.
     */
    public function fakeAuthenticate() {

        $criteria = new CDbCriteria;
        $criteria->condition = 'username=:userName OR email=:email';
        $criteria->params = array(':userName' => $this->username, ':email' => $this->username);
        $user = User::model()->find($criteria);

        if ($user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else {
            $this->errorCode = self::ERROR_NONE;
            $this->onSuccessfulAuthenticate($user);
        }

        return !$this->errorCode;
    }

    /**
     * Executed after successful authenticating a user
     * 
     * @param User $user
     */
    private function onSuccessfulAuthenticate($user) {
        
        $user->last_login = new CDbExpression('NOW()');
        $user->save();
        
        $this->_id = $user->id;
        $this->setState('title', $user->profile->title);
    }

}
