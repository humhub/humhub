<?php

namespace humhub\modules\user\models\forms;

use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;
use humhub\modules\user\authclient\BaseFormAuth;


/**
 * LoginForm is the model behind the login form.
 */
class Login extends Model
{

    /**
     * @var string user's username or email address
     */
    public $username;

    /**
     * @var string password
     */
    public $password;

    /**
     * @var boolean remember user
     */
    public $rememberMe = false;

    /**
     * @var \yii\authclient\BaseClient auth client used to authenticate
     */
    public $authClient = null;

    /**
     * @var User User of the auth client
     */
    protected $authUser = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->rememberMe = Yii::$app->getModule('user')->loginRememberMeDefault;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('UserModule.auth', 'username or email'),
            'password' => Yii::t('UserModule.auth', 'password'),
            'rememberMe' => Yii::t('UserModule.auth', 'Remember me'),
        ];
    }

    /**
     * Validation
     */
    public function afterValidate()
    {
        $user = null;

        // Loop over enabled authclients
        foreach (Yii::$app->authClientCollection->getClients() as $authClient) {
            if ($authClient instanceof BaseFormAuth) {
                $authClient->login = $this;
                if ($authClient->auth()) {
                    $this->authClient = $authClient;

                    // Delete password after successful auth
                    $this->password = '';

                    if ($this->isDelayed()) {
                        $this->addError('password', Yii::t('UserModule.base', 'Your account is delayed because of failed login attempt, please try later.'));
                        break;
                    }

                    return;
                }
            }
        }

        if ($user === null) {
            $this->addError('password', Yii::t('UserModule.auth', 'User or Password incorrect.'));
        }

        // Delete current password value
        $this->password = '';

        parent::afterValidate();
    }

    /**
     * @since 1.8
     * @return User
     */
    public function getUser()
    {
        if (!$this->authUser) {
            $this->authUser = $this->authClient ? $this->authClient->getUser() : null;
        }
        return $this->authUser;
    }

    /**
     * @since 1.8
     * @return integer
     */
    public function getDelayedTime()
    {
        return $this->getUser() ? $this->getUser()->getDelayedLoginTime() : 0;
    }

    /**
     * @since 1.8
     * @return boolean
     */
    public function isDelayed()
    {
        return $this->getUser() ? $this->getUser()->isDelayedLoginAction() : false;
    }

}
