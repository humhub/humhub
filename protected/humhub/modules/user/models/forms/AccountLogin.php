<?php

namespace humhub\modules\user\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\user\models\User;
use humhub\modules\user\libs\Ldap;
use humhub\models\Setting;
use yii\db\Expression;

/**
 * LoginForm is the model behind the login form.
 */
class AccountLogin extends Model
{

    /**
     * @var string user's username or email address
     */
    public $username;
    public $password;
    public $rememberMe = true;
    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            ['username', 'validateUser'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user !== null) {
                if ($user->auth_mode === User::AUTH_MODE_LOCAL && $user->currentPassword !== null && $user->currentPassword->validatePassword($this->password)) {
                    return;
                } elseif ($user->auth_mode === User::AUTH_MODE_LDAP && Ldap::isAvailable() && Ldap::getInstance()->authenticate($user->username, $this->password)) {
                    return;
                }
            }
            $this->addError($attribute, 'Incorrect username or password.');
        }
    }

    public function validateUser($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user !== null) {
                if ($user->status == User::STATUS_DISABLED) {
                    $this->addError($attribute, Yii::t('UserModule.views_auth_login','Your account is disabled!'));
                }
                if ($user->status == User::STATUS_NEED_APPROVAL) {
                    $this->addError($attribute, Yii::t('UserModule.views_auth_login','Your account is not approved yet!'));
                }
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate() && Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0)) {
            $this->_user->last_login = new Expression('NOW()');
            $this->_user->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::find()->where(['username' => $this->username])->orWhere(['email' => $this->username])->one();

            // Could not found user -> lookup in LDAP
            if ($this->_user === null && Ldap::isAvailable() && Setting::Get('enabled', 'authentication_ldap')) {

                try {
                    // Try load/create LDAP user
                    $usernameDn = Ldap::getInstance()->ldap->getCanonicalAccountName($this->username, \Zend\Ldap\Ldap::ACCTNAME_FORM_DN);
                    Ldap::getInstance()->handleLdapUser(Ldap::getInstance()->ldap->getNode($usernameDn));

                    // Check if user is availble now
                    $this->_user = User::find()->where(['username' => $this->username])->orWhere(['email' => $this->username])->one();
                } catch (\Zend\Ldap\Exception\LdapException $ex) {
                    // User not found
                }
            }
        }

        return $this->_user;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'username' => Yii::t('UserModule.views_auth_login', 'username or email'),
            'password' => Yii::t('UserModule.views_auth_login', 'password'),
            'rememberMe' => Yii::t('UserModule.views_auth_login', 'Remember me'),
        );
    }

}
