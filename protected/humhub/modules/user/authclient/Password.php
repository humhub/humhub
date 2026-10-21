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
     * ID of the password client; it is also the ID of the local user source.
     *
     * @since 1.19.1
     */
    public const ID = 'local';

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return self::ID;
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
     * Finds the user a login belongs to, regardless of its user source, see
     * {@see isPasswordUser()} for which users are found.
     *
     * A username takes precedence over an email address, as a username may
     * contain "@" and thus equal another user's email. If the user matched by
     * username cannot log in with a password, the user matched by email is
     * used instead, so one user's username cannot block another user's login
     * by email.
     *
     * @inheritdoc
     */
    protected function findUserByLogin(string $login): ?User
    {
        foreach ([['username' => $login], ['email' => $login]] as $condition) {
            $user = User::findOne($condition);
            if ($user !== null && $this->isPasswordUser($user)) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Whether the given user may log in with this client.
     *
     * The user's source must allow the password client, see
     * {@see UserSourceService::isAuthClientAllowed()}: users of other sources
     * than "local" may log in with their local password if their source lists
     * this client in its allowedAuthClientIds (or allows all clients).
     *
     * Such users additionally need a password. Without one, another form
     * client owns the login attempt (e.g. the LDAP client, which comes after
     * this one in the login form), so this client must neither find the user
     * nor count a failed attempt against them.
     */
    private function isPasswordUser(User $user): bool
    {
        if (!UserSourceService::getForUser($user)->isAuthClientAllowed($this->getId())) {
            return false;
        }

        return $user->user_source === self::ID || $user->currentPassword !== null;
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
