<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\authclient\Collection;
use humhub\modules\user\authclient\interfaces\ApprovalBypass;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\authclient\ClientInterface;

/**
 * AuthClientService
 *
 * @since 1.14
 */
class AuthClientService
{
    public function __construct(public ClientInterface $authClient)
    {
    }

    /**
     * Returns the user object linked to the current AuthClient.
     *
     * Two lookup paths:
     *  - via the user_auth table (source + source_id) for clients that record
     *    an external identity (OAuth, SAML, LDAP)
     *  - via user.user_source matching the AuthClient ID (Password and any
     *    other source-owning client whose attributes['id'] is the user's PK)
     */
    public function getUser(): ?User
    {
        $attributes = $this->authClient->getUserAttributes();
        $clientId = $this->authClient->getId();

        if (isset($attributes['id'])) {
            $auth = Auth::find()->where(['source' => $clientId, 'source_id' => $attributes['id']])->one();
            if ($auth !== null) {
                return $auth->user;
            }
        }

        // Source-owning clients (e.g. Password) store the HumHub user.id as attributes['id']
        // and don't use the user_auth table.
        $source = UserSourceService::getCollection()->findUserSourceForAuthClient($clientId);
        if ($source->getId() === $clientId && isset($attributes['id'])) {
            return User::findOne(['id' => $attributes['id'], 'user_source' => $clientId]);
        }

        return null;
    }

    /**
     * Updates a user with attributes provided by the AuthClient.
     *
     * The user's UserSource must allow this AuthClient to push attributes
     * (via `getAllowedAuthClientIds()`); otherwise the call is a no-op so
     * that AuthClients used purely for authentication cannot overwrite data
     * managed by the user's source.
     *
     * A failure here must NOT block login — the caller logs and proceeds.
     */
    public function updateUser(?User $user = null): bool
    {
        if ($user === null) {
            $user = $this->getUser();
            if ($user === null) {
                return false;
            }
        }

        $service = UserSourceService::getForUser($user);
        if (!$service->isAuthClientAllowed($this->authClient->getId())) {
            return true;
        }

        return $service->updateUser($this->authClient->getUserAttributes());
    }

    /**
     * Creates a user via the UserSource that claims this AuthClient.
     *
     * Dispatch: any UserSource whose `getAllowedAuthClientIds()` lists the
     * current client wins. If none claim it (e.g. a vanilla Yii2 OAuth client
     * without a dedicated source), LocalUserSource is the fallback.
     *
     * Returns null if creation failed — the caller (AuthController) decides
     * whether to redirect to the registration form or show an error.
     */
    public function createUser(): ?User
    {
        $attributes = $this->authClient->getUserAttributes();
        $userSource = UserSourceService::getCollection()
            ->findUserSourceForAuthClient($this->authClient->getId());

        $user = $userSource->createUser($attributes);
        if ($user === null) {
            return null;
        }

        // Record the AuthClient as a login method (no-op for source-owning clients
        // such as Password or LdapAuth — those manage identity via user.user_source).
        (new AuthClientUserService($user))->add($this->authClient);

        return $user;
    }

    /**
     * Returns all users which are using an given authclient
     *
     * @return ActiveQueryUser
     */
    public function getUsersQuery(): ActiveQueryUser
    {
        $query = User::find();
        $clientId = $this->authClient->getId();
        $source = UserSourceService::getCollection()->findUserSourceForAuthClient($clientId);

        if ($source->getId() === $clientId) {
            $query->where(['user.user_source' => $clientId]);
        } else {
            $query->leftJoin('user_auth', 'user_auth.user_id = user.id')
                ->where(['user_auth.source' => $clientId]);
        }

        return $query;
    }

    public static function getCollection(): Collection
    {
        /** @var Collection $authClientCollection */
        $authClientCollection = Yii::$app->authClientCollection;

        return $authClientCollection;
    }

    public function autoMapToExistingUser(): void
    {
        $attributes = $this->authClient->getUserAttributes();

        if ($this->getUser() !== null || !isset($attributes['email'])) {
            return;
        }

        $user = User::findOne(['email' => $attributes['email']]);
        if ($user === null) {
            return;
        }

        // Respect the target user's source policy: only link if its source
        // allows this auth client and email auto-link is not disabled.
        $service = UserSourceService::getForUser($user);
        if (!$service->getUserSource()->allowEmailAutoLink()) {
            return;
        }
        if (!$service->isAuthClientAllowed($this->authClient->getId())) {
            return;
        }

        (new AuthClientUserService($user))->add($this->authClient);
    }

    /**
     * @return bool
     * @since 1.15
     */
    public function allowSelfRegistration(): bool
    {
        // Auth clients explicitly claimed by a UserSource (i.e. listed in its
        // allowedAuthClientIds) are considered trusted providers and may always
        // auto-register users, independent of the anonymousRegistration setting.
        if (UserSourceService::getCollection()->isAuthClientClaimed($this->authClient->getId())) {
            return true;
        }

        // Backwards-compatibility: legacy auth clients still implementing
        // ApprovalBypass are trusted. Deprecated since 1.19.
        if ($this->authClient instanceof ApprovalBypass) {
            return true;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        // Anonymous Registration is enabled
        if ($module->settings->get('auth.anonymousRegistration')) {
            return true;
        }

        return false;
    }
}
