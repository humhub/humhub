<?php

/**
 * WebUser represents the persistent state for a Web application user.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class WebUser extends CWebUser {

    /**
     * Stores user model to not repeat the database query
     *
     * @var User
     */
    private $_model;

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
        if ($user != null)
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
     * Checks if the can approve new users
     * @todo Add caching support
     *
     * @return boolean
     */
    function canApproveUsers() {

        if ($this->isAdmin())
            return true;

        $user = $this->loadUser(Yii::app()->user->id);

        $adminGroups = GroupAdmin::model()->countByAttributes(array('user_id' => $user->id));
        if ($adminGroups != 0) {
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

}
