<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 *
 * @package humhub.modules_core.user.forms
 * @since 0.5
 * @author Luke
 */
class AccountLoginForm extends CFormModel
{

    public $username;
    public $password;
    public $rememberMe;
    private $_identity;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(
// username and password are required
            array('username, password', 'required'),
            // rememberMe needs to be a boolean
            array('rememberMe', 'boolean'),
            // password needs to be authenticated
            array('password', 'authenticate'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'rememberMe' => Yii::t('UserModule.forms_AccountLoginForm', 'Remember me next time'),
        );
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute, $params)
    {
        if (!$this->hasErrors()) {

            $this->_identity = new UserIdentity($this->username, $this->password);
            if (!$this->_identity->authenticate())
                if ($this->_identity->errorCode === UserIdentity::ERROR_NOT_APPROVED) {
                    $this->addError('username', Yii::t('UserModule.forms_AccountLoginForm', 'Your account has not been activated by our staff yet.'));
                } elseif ($this->_identity->errorCode === UserIdentity::ERROR_SUSPENDED) {
                    $this->addError('username', Yii::t('UserModule.forms_AccountLoginForm', 'Your account is suspended.'));
                } else {
                    $this->addError('password', Yii::t('UserModule.forms_AccountLoginForm', 'Incorrect username/email or password.'));
                }
        }
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login()
    {
        if ($this->_identity === null) {
            $this->_identity = new UserIdentity($this->username, $this->password);
            $this->_identity->authenticate();
        }
        if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
            Yii::app()->user->login($this->_identity, $duration);
            return true;
        } else
            return false;
    }

}
