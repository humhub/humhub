<?php

namespace humhub\modules\user\models\forms;

use humhub\modules\user\authclient\BaseFormClient;
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
     * an external IdP instead of seeing the password screen — i.e. they have
     * no usable form-based auth:
     *  - Their UserSource's allow-list contains no {@see BaseFormClient} client
     *    (e.g. SAML/OIDC-only sources); or
     *  - Their source has no allow-list at all (LocalUserSource default) and
     *    they have no local password set — the password screen would be a
     *    dead end, so we send them to their most recently linked external
     *    provider instead.
     *
     * For LDAP-sourced users the allow-list always contains the LDAP form
     * client, so a linked OAuth provider does NOT shadow the password screen.
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

        if ($this->hasUsableFormAuth($user, $allowedIds)) {
            return null;
        }

        // No usable form-based auth — pick an external provider.
        // Preference 1: the first explicitly allowed client (the source's
        // declared IdP, e.g. SAML/OIDC for an SSO-only source).
        if (!empty($allowedIds)) {
            $firstClientId = reset($allowedIds);
            if ($collection->hasClient($firstClientId)) {
                return ['/user/auth/external', 'authclient' => $firstClientId];
            }
        }

        // Preference 2: the user's most recently linked external provider.
        // Iterate desc-by-id so the most recent OAuth/SAML wins; skip rows
        // pointing at form-based clients (LDAP also writes user_auth rows for
        // DN tracking since 1.19) or at clients no longer registered.
        $auths = Auth::find()
            ->where(['user_id' => $user->id])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        foreach ($auths as $auth) {
            if (!$collection->hasClient($auth->source)) {
                continue;
            }
            if (!$collection->getClient($auth->source) instanceof BaseFormClient) {
                return ['/user/auth/external', 'authclient' => $auth->source];
            }
        }

        return null;
    }

    /**
     * Whether the password screen is a meaningful next step for this user.
     *
     * - Source with an allow-list: any allowed BaseFormClient client (LDAP, Local)
     *   means the user can authenticate via form.
     * - Source without an allow-list (e.g. LocalUserSource default): the password
     *   screen is only useful if the user actually has a local password set.
     */
    private function hasUsableFormAuth(User $user, array $allowedIds): bool
    {
        if (!empty($allowedIds)) {
            $collection = Yii::$app->authClientCollection;
            foreach ($allowedIds as $clientId) {
                if ($collection->hasClient($clientId)
                    && $collection->getClient($clientId) instanceof BaseFormClient) {
                    return true;
                }
            }
            return false;
        }

        return $user->currentPassword !== null;
    }
}
