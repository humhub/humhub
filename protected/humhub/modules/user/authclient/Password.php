<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;

/**
 * Standard password authentication client.
 *
 * @since 1.1
 */
class Password extends BaseFormClient
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
     * Finds the user regardless of its user source; the user's source must allow
     * the password client, see {@see UserSourceService::isAuthClientAllowed()}.
     *
     * Users of other sources than "local" may log in with their local password
     * if their source lists this client in its allowedAuthClientIds (or allows
     * all clients). Users of other sources are not found at all, so a failed
     * attempt on their behalf (e.g. an LDAP user, whose LDAP client comes after
     * this one in the login form) is not counted against them.
     *
     * A username takes precedence over an email address, as a username may
     * contain "@" and thus equal another user's email.
     *
     * @inheritdoc
     */
    protected function findUserByLogin(string $login): ?User
    {
        $user = User::findOne(['username' => $login]) ?? User::findOne(['email' => $login]);

        if ($user === null || !UserSourceService::getForUser($user)->isAuthClientAllowed($this->getId())) {
            return null;
        }

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function authenticate(string $username, string $password): bool
    {
        $user = $this->getUserByLogin();

        if ($user !== null && $user->currentPassword !== null && $user->currentPassword->validatePassword($password)) {
            $this->setUserAttributes(['id' => $user->id]);
            return true;
        }

        $this->countFailedLoginAttempts();
        return false;
    }
}
