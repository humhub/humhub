<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\models\User;

/**
 * Standard password authentication client.
 *
 * Lookup of the authenticated User is owned by {@see authenticate()} (direct
 * return value) and {@see \humhub\modules\user\services\AuthClientService::getUser()}
 * (attribute-based lookup, used after session rehydration).
 *
 * @since 1.1
 */
class Password extends BaseFormClient implements interfaces\PrimaryClient
{
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return 'local';
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'password';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Password';
    }

    /**
     * @inheritdoc
     */
    public function authenticate(string $username, string $password): ?User
    {
        $user = $this->getUserByLogin();

        if ($user !== null && $user->currentPassword !== null && $user->currentPassword->validatePassword($password)) {
            $this->setUserAttributes(['id' => $user->id]);
            return $user;
        }

        $this->countFailedLoginAttempts();
        return null;
    }
}
