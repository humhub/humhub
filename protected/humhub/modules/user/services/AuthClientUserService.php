<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\authclient\Collection;
use humhub\modules\user\authclient\interfaces\AutoSyncUsers;
use humhub\modules\user\authclient\interfaces\PrimaryClient;
use humhub\modules\user\authclient\interfaces\SyncAttributes;
use humhub\modules\user\authclient\Password;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

/**
 * AuthClient handling for users
 *
 * @since 1.14
 */
class AuthClientUserService
{
    public User $user;

    /**
     * @var ClientInterface[]
     */
    private ?array $_authClients = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function add(ClientInterface $authClient): void
    {
        $attributes = $authClient->getUserAttributes();

        if ($authClient instanceof PrimaryClient) {
            $this->user->auth_mode = $authClient->getId();
            $this->user->save();
        } elseif (!empty($attributes['id'])) {
            $auth = Auth::findOne(['source' => $authClient->getId(), 'source_id' => $attributes['id']]);

            /**
             * Make sure authClient is not double assigned
             */
            if ($auth !== null && $auth->user_id != $this->user->id) {
                $auth->delete();
                $auth = null;
            }

            if ($auth === null) {
                $auth = new Auth([
                    'user_id' => $this->user->id,
                    'source' => (string)$authClient->getId(),
                    'source_id' => (string)$attributes['id'],
                ]);

                $auth->save();
            }
        } else {
            Yii::error(
                'Could not store auth client without given ID attribute. User: ' . $this->user->displayName . ' (' . $this->user->id . ')', 'user');
        }
    }

    public function remove(ClientInterface $authClient): void
    {
        Auth::deleteAll([
            'user_id' => $this->user->id,
            'source' => (string)$authClient->getId()
        ]);
    }


    public function canChangeUsername(): bool
    {
        foreach ($this->getClients() as $authClient) {
            if (get_class($authClient) == Password::class) {
                return true;
            }
        }

        return false;
    }

    public function canChangeEmail(): bool
    {
        if (in_array('email', $this->getSyncAttributes())) {
            return false;
        }

        return true;
    }

    public function canDeleteAccount(): bool
    {
        foreach ($this->getClients() as $authClient) {
            if ($authClient instanceof AutoSyncUsers) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines if this user is able to change the password.
     *
     * @return boolean
     */
    public function canChangePassword(): bool
    {
        $primaryAuthClient = $this->getPrimaryClient();
        if ($primaryAuthClient && get_class($primaryAuthClient) === Password::class) {
            return true;
        }

        return false;
    }

    /**
     * Returns a list of attributes synced and handled by an AuthClient
     * which is assigned to the user.
     *
     * @return string[]
     */
    public function getSyncAttributes(): array
    {
        $attributes = [];
        foreach ($this->getClients() as $authClient) {
            if ($authClient instanceof SyncAttributes) {
                $attributes = array_merge($attributes, $authClient->getSyncAttributes());
            }
        }
        return $attributes;
    }

    /**
     * @return ClientInterface[]
     */
    public function getClients(): array
    {
        if ($this->_authClients === null) {
            $this->_authClients = [];

            foreach (AuthClientService::getCollection()->getClients() as $client) {
                // Add primary authClient
                if ($this->user->auth_mode == $client->getId()) {
                    $this->_authClients[] = $client;
                }

                // Add additional authClient (OAuth 2.0)
                foreach ($this->user->auths as $auth) {
                    if ($auth->source == $client->getId()) {
                        $this->_authClients[] = $client;
                    }
                }
            }
        }

        return $this->_authClients;
    }

    private function getPrimaryClient(): ?ClientInterface
    {
        try {
            return AuthClientService::getCollection()->getClient($this->user->auth_mode);
        } catch (InvalidArgumentException $e) {
            Yii::error('Could not get primary auth client for user: ' . $this->user->id, 'user');
        } catch (InvalidConfigException $e) {
            Yii::error($e, 'user');
        }
        return null;
    }
}
