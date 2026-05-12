<?php

namespace humhub\modules\user\models\forms;

use humhub\helpers\DeviceDetectorHelper;
use humhub\modules\user\assets\UserAsset;
use humhub\modules\user\authclient\BaseFormAuth;
use Yii;
use yii\base\Model;

/**
 * Single-shot login form — username + password validated together.
 *
 * Used by:
 *  - {@see LoginPassword} (Step 2 of the interactive web flow, via inheritance)
 *  - third-party integrations that authenticate a user from username + password
 *    in one call (e.g. CalDAV `HttpBasicAuth` backend, REST `auth/AuthController`)
 *
 * The interactive web flow does not instantiate this class directly — it uses
 * {@see LoginIdentity} (Step 1) and {@see LoginPassword} (Step 2).
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
     * @var bool remember user
     */
    public $rememberMe = false;

    /**
     * @var bool hide "Remember me" form field in the view
     */
    public $hideRememberMe = false;

    /**
     * @var bool whether to remember the entered username/email in a cookie so
     * the user lands directly on Step 2 on the next visit. Only applies to the
     * Step-2 form (LoginPassword); the base BC-Login does not surface it.
     * @since 1.19
     */
    public $rememberUsername = false;

    /**
     * @var BaseFormAuth auth client used to authenticate
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
        $this->hideRememberMe = DeviceDetectorHelper::isAppRequest();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('UserModule.auth', 'Username or Email'),
            'password' => Yii::t('UserModule.auth', 'Password'),
            'rememberMe' => Yii::t('UserModule.auth', 'Keep me signed in'),
            'rememberUsername' => Yii::t('UserModule.auth', 'Remember username'),
        ];
    }

    /**
     * Iterates over the configured form-based auth clients and tries each one.
     * Sets {@see $authClient} on the first successful auth. Adds a generic
     * "User or Password incorrect" error otherwise — never reveals which side
     * (username vs password) failed.
     */
    public function afterValidate()
    {
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
                "#login-button")',
            );
        }

        $this->addError('password', Yii::t('UserModule.auth', 'User or Password incorrect.'));

        // Delete current password value
        $this->password = '';

        parent::afterValidate();
    }
}
