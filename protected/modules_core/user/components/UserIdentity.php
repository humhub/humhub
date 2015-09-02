<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.user.components
 * @since 0.5
 *
 */
class UserIdentity extends CUserIdentity
{

    const ERROR_NOT_APPROVED = 10;
    const ERROR_SUSPENDED = 11;

    /**
     * @var Integer Users id
     */
    private $_id;

    /**
     * Returns users id
     *
     * @return Integer
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Authenticates a user based on {@link username} and {@link password}.
     *
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        $user = $this->getUser();

        if ($user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } elseif (!$this->validatePassword($user)) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } elseif ($user->status == User::STATUS_DISABLED) {
            $this->errorCode = self::ERROR_SUSPENDED;
        } elseif ($user->status == User::STATUS_NEED_APPROVAL) {
            $this->errorCode = self::ERROR_NOT_APPROVED;
        } else {
            $this->onSuccessfulAuthenticate($user);
        }

        return !$this->errorCode;
    }

    /**
     * Authenticates a given username without validating its password and status.
     *
     * @return boolean whether authentication succeeds.
     */
    public function fakeAuthenticate()
    {
        $user = $this->getUser();

        if ($user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else {
            $this->onSuccessfulAuthenticate($user);
        }

        return !$this->errorCode;
    }

    /**
     * Returns user records
     *
     * @return User
     */
    private function getUser()
    {
        // Find User
        $criteria = new CDbCriteria;
        $criteria->condition = 'username=:userName OR email=:email';
        $criteria->params = array(':userName' => $this->username, ':email' => $this->username);
        $user = User::model()->resetScope()->find($criteria);

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

        return $user;
    }

    /**
     * Validates password of user against internal database or ldap
     *
     * @param User $user
     * @return Succeess
     * @throws CException
     */
    private function validatePassword(User $user)
    {
        if ($user->auth_mode == User::AUTH_MODE_LOCAL) {
            // Authenticate via Local DB
            if ($user->currentPassword != null && $user->currentPassword->validatePassword($this->password)) {
                return true;
            }
        } elseif ($user->auth_mode == User::AUTH_MODE_LDAP) {
            // Authenticate via LDAP
            if (HLdap::getInstance()->authenticate($user->username, $this->password)) {

                // Reload user object - because we may updated the user while LDAP authentication
                $user = $this->getUser();
                
                return true;
            }
        } else {
            throw new CException("Invalid user authentication mode!");
        }

        return false;
    }

    /**
     * Executed after successful authenticating a user
     *
     * @param User $user
     */
    private function onSuccessfulAuthenticate($user)
    {
        $this->errorCode = self::ERROR_NONE;

        $user->last_login = new CDbExpression('NOW()');
        $user->save();

        $this->_id = $user->id;
        $this->setState('title', $user->profile->title);
    }

}
