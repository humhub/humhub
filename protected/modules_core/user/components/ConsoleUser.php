<?php

class ConsoleUser extends CApplicationComponent implements IWebUser {

    /**
     * Stores user model to not repeat the database query
     * 
     * @var User 
     */
    private $_model;
    public $id;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->_model = null;
        $this->id = $id;
    }
    

    /**
     * Returns the users displayname
     * 
     * Access it by Yii::app()->user->displayname
     * 
     * @return String
     */
    function getDisplayName() {
        $user = $this->loadUser(Yii::app()->user->id);
        return $user->displayName;
    }

    /**
     * Returns the users e-mail address
     * 
     * @return String
     */
    function getEmail() {
        $user = $this->loadUser(Yii::app()->user->id);
        return $user->email;
    }

    /**
     * Returns the language code of the user model
     * 
     * @return String
     */
    function getLanguage() {
        $user = $this->loadUser(Yii::app()->user->id);
        return $user->language;
    }

    /**
     * Returns users guid
     * 
     * @return String
     */
    function getGuid() {
        $user = $this->loadUser(Yii::app()->user->id);
        return $user->guid;
    }

    /**
     * Returns users authentication mode
     * 
     * @return String
     */
    function getAuthMode() {
        $user = $this->loadUser(Yii::app()->user->id);
        return $user->auth_mode;
    }

    /**
     * Returns current user model
     * 
     * @return type
     * @throws CHttpException
     */
    function getModel() {
        $user = $this->loadUser(Yii::app()->user->id);

        if ($user == null)
            throw new CHttpException(500, 'Could not find logged in user!');

        return $user;
    }

    /**
     * Reloads the user cached model
     */
    function reload() {
        $this->_model = null;
    }

    /**
     * Checks if the user is admin
     * 
     * Access it by Yii::app()->user->isAdmin()
     * 
     * @return int
     */
    function isAdmin() {
        $user = $this->loadUser(Yii::app()->user->id);

        if ($user->super_admin == 1) {
            return true;
        }

        return false;
    }

    /**
     * Loads user model and store/cache it as class attribute
     * 
     * @param Int $id
     * @return User
     */
    protected function loadUser($id = null) {

        if ($this->_model === null) {
            if ($id !== null) {
                $this->_model = User::model()->findByPk($id);
            } else {
                // Create Blank user
                $this->_model = new User();
            }
        }
        return $this->_model;
    }

    public function getIsGuest() {
        return false;
    }

    public function checkAccess($operation, $params = array()) {
        return true;
    }

    function getName() {
        return $this->getDisplayName();
    }
    
    public function loginRequired() {
        return false;
    }

    function getAuthTimeout() {
    	return HSetting::Get('defaultUserIdleTimeoutSec', 'authentication_internal');
    }
    
    function setAuthTimeout() {
    	return true;
    }
}

?>