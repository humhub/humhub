<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use humhub\modules\user\source\HasUserSource;
use Yii;
use yii\authclient\ClientInterface;

/**
 * AuthClient handling for users
 *
 * @since 1.14
 */
class AuthClientUserService
{
    /**
     * @var ClientInterface[]
     */
    private ?array $_authClients = null;

    public function __construct(public User $user)
    {
    }

    public function add(ClientInterface $authClient): void
    {
        if (!($authClient instanceof HasUserSource)
            && UserSourceService::getCollection()->hasUserSource($authClient->getId())) {
            Yii::warning(sprintf(
                "add() called with source-owning client '%s' for user %d — this client manages user identity directly and does not use user_auth.",
                $authClient->getId(),
                $this->user->id,
            ), 'user');
            return;
        }

        $attributes = $authClient->getUserAttributes();

        if (empty($attributes['id'])) {
            Yii::error(
                'Could not store auth client without given ID attribute. User: ' . $this->user->displayName . ' (' . $this->user->id . ')',
                'user',
            );
            return;
        }

        $auth = Auth::findOne(['source' => $authClient->getId(), 'source_id' => $attributes['id']]);

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
    }

    public function remove(ClientInterface $authClient): void
    {
        Auth::deleteAll([
            'user_id' => $this->user->id,
            'source' => (string)$authClient->getId(),
        ]);
    }

    /**
     * Returns all auth clients associated with this user.
     *
     * @return ClientInterface[]
     */
    public function getClients(): array
    {
        if ($this->_authClients === null) {
            $this->_authClients = [];

            foreach (AuthClientService::getCollection()->getClients() as $client) {
                // Add auth client whose ID matches user_source (the "primary" client)
                if ($this->user->user_source == $client->getId()) {
                    $this->_authClients[] = $client;
                }

                // Add additional auth clients (OAuth, SAML, etc.) via user_auth table
                foreach ($this->user->auths as $auth) {
                    if ($auth->source == $client->getId()) {
                        $this->_authClients[] = $client;
                    }
                }
            }
        }

        return $this->_authClients;
    }
}
