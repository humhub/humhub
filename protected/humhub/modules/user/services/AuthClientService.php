<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\authclient\BaseClient;
use humhub\modules\user\authclient\Collection;
use humhub\modules\user\authclient\interfaces\ApprovalBypass;
use humhub\modules\user\authclient\interfaces\PrimaryClient;
use humhub\modules\user\authclient\interfaces\SyncAttributes;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\VarDumper;
use yii\web\UserEvent;

/**
 * AuthClientService
 *
 * @since 1.14
 */
class AuthClientService
{
    public ClientInterface $authClient;

    public function __construct(ClientInterface $authClient)
    {
        $this->authClient = $authClient;
    }

    /**
     * Returns the user object which is linked against given authClient
     *
     * @return User|null the user model or null if not found
     */
    public function getUser(): ?User
    {
        $attributes = $this->authClient->getUserAttributes();

        if ($this->authClient instanceof PrimaryClient) {
            return $this->authClient->getUser();
        }

        if (isset($attributes['id'])) {
            $auth = Auth::find()->where(['source' => $this->authClient->getId(), 'source_id' => $attributes['id']])->one();
            if ($auth !== null) {
                return $auth->user;
            }
        }

        return null;
    }


    /**
     * Updates (or creates) a user in HumHub using AuthClients Attributes
     * This method will be called after login or by cron sync.
     *
     * @param User|null $user
     * @return bool succeed
     */
    public function updateUser(User $user = null): bool
    {
        if ($user === null) {
            $user = $this->getUser();
            if ($user === null) {
                return false;
            }
        }

        $this->authClient->trigger(BaseClient::EVENT_UPDATE_USER, new UserEvent(['identity' => $user]));

        if ($this->authClient instanceof SyncAttributes) {
            $attributes = $this->authClient->getUserAttributes();

            foreach ($this->authClient->getSyncAttributes() as $attributeName) {
                if (isset($attributes[$attributeName])) {
                    if ($user->hasAttribute($attributeName) && !in_array($attributeName, ['id', 'guid', 'status', 'contentcontainer_id', 'auth_mode'])) {
                        $user->setAttribute($attributeName, $attributes[$attributeName]);
                    } elseif ($user->profile->hasAttribute($attributeName)) {
                        $user->profile->setAttribute($attributeName, $attributes[$attributeName]);
                    }
                } else {
                    if ($user->profile->hasAttribute($attributeName)) {
                        $user->profile->setAttribute($attributeName, '');
                    }
                }
            }

            if (count($user->getDirtyAttributes()) !== 0 && !$user->save()) {
                Yii::warning('Could not update user (' . $user->id . '). Error: '
                    . VarDumper::dumpAsString($user->getErrors()), 'user');

                return false;
            }

            if (count($user->profile->getDirtyAttributes()) !== 0 && !$user->profile->save()) {
                Yii::warning('Could not update user profile (' . $user->id . '). Error: '
                    . VarDumper::dumpAsString($user->profile->getErrors()), 'user');

                return false;
            }
        }

        return true;
    }

    public function createRegistration(): ?Registration
    {
        $attributes = $this->authClient->getUserAttributes();

        if (!isset($attributes['id'])) {
            return null;
        }

        $registration = new Registration(enableEmailField: true, enablePasswordForm: false);

        if ($this->authClient instanceof ApprovalBypass) {
            $registration->enableUserApproval = false;
        }

        // remove potentially unsafe attributes
        unset(
            $attributes['id'],
            $attributes['guid'],
            $attributes['contentcontainer_id'],
            $attributes['auth_mode'],
            $attributes['status'],
        );

        $attributes['username'] = AuthHelper::generateUsernameByAttributes($attributes);

        $registration->getUser()->setAttributes($attributes, false);
        $registration->getProfile()->setAttributes($attributes, false);
        $registration->getGroupUser()->setAttributes($attributes, false);

        return $registration;
    }

    /**
     * Automatically creates user by auth client attributes
     *
     * @return User|null the created user
     */
    public function createUser(): ?User
    {
        $registration = static::createRegistration();
        if ($registration !== null && $registration->validate() && $registration->register($this->authClient)) {
            return $registration->getUser();
        }

        return null;
    }

    /**
     * Returns all users which are using an given authclient
     *
     * @return ActiveQueryUser
     */
    public function getUsersQuery(): ActiveQueryUser
    {
        $query = User::find();

        if ($this->authClient instanceof PrimaryClient) {
            $query->where([
                'auth_mode' => $this->authClient->getId(),
            ]);
        } else {
            $query->where(['user_auth.source' => $this->authClient->getId()]);
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
