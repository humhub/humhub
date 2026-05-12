<?php

namespace humhub\modules\user\models\forms;

use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;
use Yii;
use yii\base\Model;

/**
 * Step 1 of the interactive login flow — collects only the username/email.
 *
 * Shares the `Login` form name so browsers / password managers see Step 1 and
 * Step 2 as a single login form.
 *
 * @since 1.19
 */
class LoginIdentity extends Model
{
    /**
     * @var string user's username or email address
     */
    public $username;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('UserModule.auth', 'Username or Email'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'Login';
    }

    /**
     * Look up an existing user by the submitted username/email.
     *
     * Used to decide whether to redirect to an IdP. Returns null when no match
     * is found — callers must treat this case identically to a matched user
     * with form-based auth (account-enumeration protection).
     */
    public function findUser(): ?User
    {
        if ((string)$this->username === '') {
            return null;
        }

        return User::find()
            ->where(['username' => $this->username])
            ->orWhere(['email' => $this->username])
            ->one();
    }

    /**
     * Decide the next step after a successful Step-1 validation.
     *
     * Returns a redirect target (array form) when the user should be sent to
     * an external IdP instead of seeing the password screen:
     *  - Their UserSource permits no {@see BaseFormAuth} client
     *    (e.g. SAML/OIDC-only sources); or
     *  - They have no local password and a linked OAuth/SAML provider, in
     *    which case we redirect to the most recently linked one so they
     *    don't land on a useless password screen.
     *
     * Returns null when the password screen should be rendered — including
     * the unknown-user case (account-enumeration protection: an unknown user
     * looks identical to a password user from outside).
     *
     * @return array|null redirect target for {@see \yii\web\Controller::redirect()}, or null
     */
    public function getStep1Redirect(): ?array
    {
        $user = $this->findUser();
        if ($user === null) {
            return null;
        }

        $collection = Yii::$app->authClientCollection;
        $allowedIds = UserSourceService::getForUser($user)->getUserSource()->getAllowedAuthClientIds();

        // UserSource permits no form-based auth → first allowed (assumed-IdP) wins.
        if (!empty($allowedIds)) {
            $hasFormAuth = false;
            foreach ($allowedIds as $clientId) {
                if ($collection->hasClient($clientId)
                    && $collection->getClient($clientId) instanceof BaseFormAuth) {
                    $hasFormAuth = true;
                    break;
                }
            }
            if (!$hasFormAuth) {
                $firstClientId = reset($allowedIds);
                if ($collection->hasClient($firstClientId)) {
                    return ['/user/auth/external', 'authclient' => $firstClientId];
                }
            }
        }

        // User has no local password but a linked OAuth/SAML provider → send
        // them straight to the latest linked one. Iterate desc-by-id so the
        // most recently linked OAuth wins; skip rows that point at form-based
        // clients (e.g. LDAP since 1.19 also writes user_auth rows for DN
        // tracking) or at clients no longer registered.
        if ($user->currentPassword === null) {
            $auths = Auth::find()
                ->where(['user_id' => $user->id])
                ->orderBy(['id' => SORT_DESC])
                ->all();
            foreach ($auths as $auth) {
                if (!$collection->hasClient($auth->source)) {
                    continue;
                }
                if (!$collection->getClient($auth->source) instanceof BaseFormAuth) {
                    return ['/user/auth/external', 'authclient' => $auth->source];
                }
            }
        }

        return null;
    }
}
