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
use humhub\modules\user\source\HasUserSource;
use humhub\modules\user\source\LocalUserSource;
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
     * Returns the user object which is linked against given authClient
     *
     * @return User|null the user model or null if not found
     */
    public function getUser(): ?User
    {
        $attributes = $this->authClient->getUserAttributes();

        if (isset($attributes['id'])) {
            $auth = Auth::find()->where(['source' => $this->authClient->getId(), 'source_id' => $attributes['id']])->one();
            if ($auth !== null) {
                return $auth->user;
            }
        }

        // Fallback for HasUserSource auth clients (e.g. LDAP without idAttribute configured):
        // try email + user_source match. On success, opportunistically create user_auth entry.
        if ($this->authClient instanceof HasUserSource && isset($attributes['email'])) {
            $user = User::findOne([
                'email' => $attributes['email'],
                'user_source' => $this->authClient->getId(),
            ]);
            if ($user !== null && isset($attributes['id'])) {
                (new AuthClientUserService($user))->add($this->authClient);
            }
            return $user;
        }

        // Fallback for source-owning clients that don't use user_auth (e.g. Password):
        // these store the HumHub user.id as attributes['id'] directly.
        if (isset($attributes['id']) && !($this->authClient instanceof HasUserSource)
            && UserSourceService::getCollection()->hasUserSource($this->authClient->getId())) {
            return User::findOne(['id' => $attributes['id'], 'user_source' => $this->authClient->getId()]);
        }

        return null;
    }

    /**
     * Updates a user in HumHub using the AuthClient's attributes.
     * Called after login or by source-specific sync mechanisms.
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

        return UserSourceService::getForUser($user)->updateUser($this->authClient->getUserAttributes());
    }

    /**
     * Creates a user via the appropriate UserSource.
     *
     * If the AuthClient implements HasUserSource, its source is used.
     * Otherwise LocalUserSource is used as the default.
     *
     * Returns null if creation failed — the caller (AuthController) decides
     * whether to redirect to the registration form or show an error.
     */
    public function createUser(): ?User
    {
        $attributes = $this->authClient->getUserAttributes();

        $userSource = $this->authClient instanceof HasUserSource
            ? $this->authClient->getUserSource()
            : Yii::$app->userSourceCollection->getLocalUserSource();

        $user = $userSource instanceof LocalUserSource
            ? $userSource->createUser($attributes, $this->authClient)
            : $userSource->createUser($attributes);

        if ($user !== null) {
            UserSourceService::triggerAfterCreate($user);
        }

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

        if (UserSourceService::getCollection()->hasUserSource($this->authClient->getId())) {
            $query->where(['user.user_source' => $this->authClient->getId()]);
        } else {
            $query->leftJoin('user_auth', 'user_auth.user_id = user.id')
                ->where(['user_auth.source' => $this->authClient->getId()]);
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

        // Check if e-mail is already in use with another auth method
        if ($this->getUser() === null && isset($attributes['email'])) {
            $user = User::findOne(['email' => $attributes['email']]);
            if ($user !== null) {
                // Map current auth method to user with same e-mail address
                (new AuthClientUserService($user))->add($this->authClient);
            }
        }
    }

    /**
     * @return bool
     * @since 1.15
     */
    public function allowSelfRegistration(): bool
    {
        // Always also AuthClients like LDAP to automatic registration
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
