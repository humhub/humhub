<?php

namespace humhub\modules\user\models\forms;

use humhub\modules\user\assets\UserAsset;
use humhub\modules\user\authclient\BaseClient;
use humhub\modules\user\authclient\BaseFormAuth;
use Yii;
use yii\base\Model;


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
     * @var BaseClient auth client used to authenticate
     */
    public $authClient = null;

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
        // Loop over enabled authclients
        $authClientDelayed = null;
        foreach (Yii::$app->authClientCollection->getClients() as $authClient) {
            if ($authClient instanceof BaseFormAuth) {
                $authClient->login = $this;

                if ($authClient->isDelayedLoginAction()) {
                    // Don't even try to do authorization if user is delayed currently
                    $authClientDelayed = $authClient;
                    break;
                }

                if ($authClient->auth()) {
                    $this->authClient = $authClient;

                    // Delete password after successful auth
                    $this->password = '';

                    return;
                }

                // User may be delayed during authorization attempt above,
                // so we need this additional check in order to delay the login form immediately
                if ($authClient->isDelayedLoginAction()) {
                    $authClientDelayed = $authClient;
                }

            }
        }

        if ($authClientDelayed) {
            UserAsset::register(Yii::$app->view);
            Yii::$app->view->registerJs(
                'humhub.require("user.login").delayLoginAction('
                . $authClientDelayed->getDelayedLoginTime() . ',
                "' . Yii::t('UserModule.auth', 'Please wait') . '",
                "#login-button")'
            );
        }

        $this->addError('password', Yii::t('UserModule.auth', 'User or Password incorrect.'));

        // Delete current password value
        $this->password = '';

        parent::afterValidate();
    }

}
