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
     * Authenticates a user based on {@link username} and {@link password}.
     *
     * @return boolean whether authentication succeeds.
     */
    public function authenticate() {

        $criteria = new CDbCriteria;
        $criteria->condition = 'username=:userName OR email=:email';
        $criteria->params = array(':userName' => $this->username, ':email' => $this->username);
        $record = User::model()->find($criteria);

        if ($record === null)
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        else if (!$record->validatePassword($this->password))
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        else {

            $this->_id = $record->id;

            $this->setState('title', $record->title);
            $this->errorCode = self::ERROR_NONE;
        }
        return !$this->errorCode;
    }

    /**
     * Authenticates a given username without validating its password.
     * This method is used by other authentication modules e.g. ldap auth.
     *
     * @return boolean whether authentication succeeds.
     */
    public function fakeAuthenticate() {

        $criteria = new CDbCriteria;
        $criteria->condition = 'username=:userName OR email=:email';
        $criteria->params = array(':userName' => $this->username, ':email' => $this->username);
        $record = User::model()->find($criteria);

        if ($record === null)
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        else {

            $this->_id = $record->id;

            $this->setState('title', $record->title);
            $this->errorCode = self::ERROR_NONE;
        }
        return !$this->errorCode;
    }

    /**
     * Returns users id
     *
     * @return Integer
     */
    public function getId() {
        return $this->_id;
    }

}