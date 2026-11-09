<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\modules\user\authclient\BaseFormClient;
use humhub\modules\user\authclient\Password as PasswordClient;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\services\AuthClientService;
use Yii;

/**
 * Sudo mode: the user confirmed their identity recently, so a sensitive action may run. See
 * docs/develop/concept-sudo-mode.md.
 *
 * The state is the time of the last confirmation in the session, bound to the user. It is active while that time is
 * younger than the idle window and is refreshed by every protected request that passes ({@see touch()}). An
 * interactive login starts it, a cookie login drops it, and while impersonating it is never active.
 *
 * @since 1.20
 */
class SudoMode
{
    public const SESSION_KEY = 'user.sudo';
    public const DEFAULT_IDLE_MINUTES = 15;

    /**
     * Failed confirmations allowed per user and window before confirmations are refused
     */
    public const MAX_FAILURES = 5;
    public const FAILURE_WINDOW = 900;

    public function __construct(private readonly User $user)
    {
    }

    /**
     * @return static|null the sudo mode of the logged-in user, null for guests
     */
    public static function forCurrentUser(): ?static
    {
        $user = Yii::$app->user->getIdentity();

        return $user instanceof User ? new static($user) : null;
    }

    /**
     * Drops the state: any login starts without one, only an interactive login records a new one.
     */
    public static function forget(): void
    {
        if (Yii::$app->has('session') && Yii::$app->session->getIsActive()) {
            Yii::$app->session->remove(self::SESSION_KEY);
        }
    }

    /**
     * @return int how long a confirmation stays valid after the last protected request, in seconds
     */
    public static function getIdleSeconds(): int
    {
        return 60 * max(1, (int)self::getModule()->settings->get('auth.sudoIdleMinutes', self::DEFAULT_IDLE_MINUTES));
    }

    /**
     * @return bool whether the administration asks for a confirmation
     */
    public static function isRequiredForAdmin(): bool
    {
        return (bool)self::getModule()->settings->get('auth.sudoAdmin', false);
    }

    public function isActive(): bool
    {
        $lastConfirmation = $this->getLastConfirmation();

        return $lastConfirmation !== null && $lastConfirmation >= time() - self::getIdleSeconds();
    }

    /**
     * Records a confirmation, or refreshes it: a protected request that passed, or an interactive login.
     */
    public function touch(): void
    {
        if (Yii::$app->has('session')) {
            Yii::$app->session->set(self::SESSION_KEY, ['userId' => (int)$this->user->id, 'time' => time()]);
        }
    }

    /**
     * @return bool whether the user can confirm with a password: the local one, or the one of their form auth
     * client (LDAP). Otherwise only a fresh login confirms.
     */
    public function hasPasswordFactor(): bool
    {
        return CheckPasswordValidator::hasPassword($this->user) || $this->getFormClient() !== null;
    }

    public function isThrottled(): bool
    {
        return (int)Yii::$app->cache->get($this->getThrottleKey()) >= self::MAX_FAILURES;
    }

    /**
     * Confirms with the password and starts the sudo mode when it is right.
     */
    public function confirm(?string $password): bool
    {
        if ($this->isThrottled()) {
            return false;
        }

        if ($password !== null && $password !== '' && $this->verifyPassword($password)) {
            Yii::$app->cache->delete($this->getThrottleKey());
            $this->touch();
            return true;
        }

        $key = $this->getThrottleKey();
        Yii::$app->cache->set($key, (int)Yii::$app->cache->get($key) + 1, self::FAILURE_WINDOW);

        return false;
    }

    /**
     * @return array the route that signs the user in again: the login of their current auth client (SSO), else the
     * login page
     */
    public function getReloginRoute(): array
    {
        $client = Yii::$app->user->getCurrentAuthClient();
        if ($client !== null && !$client instanceof BaseFormClient) {
            return ['/user/auth/external', 'authclient' => $client->getId()];
        }

        return ['/user/auth/login'];
    }

    private function verifyPassword(string $password): bool
    {
        if (CheckPasswordValidator::hasPassword($this->user)) {
            return $this->user->currentPassword->validatePassword($password);
        }

        $client = $this->getFormClient();
        if ($client === null) {
            return false;
        }

        // As the login form does: the client checks the credentials, the service resolves the user
        $client->login = new Login(['username' => $this->user->username, 'password' => $password]);

        return $client->authenticate($this->user->username, $password)
            && (new AuthClientService($client))->getUser()?->id === (int)$this->user->id;
    }

    /**
     * @return BaseFormClient|null the user's auth client when it takes a password of its own (LDAP), not the local one
     */
    private function getFormClient(): ?BaseFormClient
    {
        $client = Yii::$app->user->getCurrentAuthClient();

        return $client instanceof BaseFormClient && !$client instanceof PasswordClient ? $client : null;
    }

    /**
     * @return int|null the time of the last confirmation recorded for this user in the session
     */
    private function getLastConfirmation(): ?int
    {
        if (!Yii::$app->has('session') || !Yii::$app->session->getIsActive()
            || Yii::$app->user->getImpersonation()->isActive()) {
            return null;
        }

        $state = Yii::$app->session->get(self::SESSION_KEY);
        if (!is_array($state) || ($state['userId'] ?? null) !== (int)$this->user->id || !is_int($state['time'] ?? null)) {
            return null;
        }

        return $state['time'];
    }

    private function getThrottleKey(): string
    {
        return 'user.sudo.failures.' . $this->user->id;
    }

    private static function getModule(): Module
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('user');

        return $module;
    }
}
