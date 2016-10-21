<?php

namespace humhub\modules\user\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\user\authclient\BaseFormAuth;

use humhub\modules\user\libs\Ldap;


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
        return array(
            'username' => Yii::t('UserModule.views_auth_login', 'username or email'),
            'password' => Yii::t('UserModule.views_auth_login', 'password'),
            'rememberMe' => Yii::t('UserModule.views_auth_login', 'Remember me'),
        );
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
                    $this->password = "";

                    return;
                }
            }
        }

        if ($user === null) {
            $this->addError('password', 'User or Password incorrect.');
        }

        // Delete current password value
        $this->password = "";

        parent::afterValidate();
    }

}
