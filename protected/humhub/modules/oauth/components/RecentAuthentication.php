<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use humhub\modules\user\components\CheckPasswordValidator;
use humhub\modules\user\models\User;
use Yii;

/**
 * Re-authentication before handing out a personal access token, which bypasses 2FA: users with
 * a local password confirm it, users without one must have signed in interactively to the
 * current session recently. Remember-me cookie logins and impersonations do not count.
 *
 * @since 1.20
 */
final class RecentAuthentication
{
    private const SESSION_KEY = 'oauth.interactiveLoginAt';

    /**
     * How recent a login must be for users without a local password.
     */
    public const MAX_LOGIN_AGE = 900;

    /**
     * Failed password attempts allowed per window before confirmations are refused.
     */
    public const MAX_FAILURES = 5;
    public const FAILURE_WINDOW = 900;

    /**
     * @param int|null $loggedInAt time of the last interactive login, null if unknown
     */
    public function __construct(private readonly User $user, private readonly ?int $loggedInAt)
    {
    }

    public static function forCurrentSession(User $user): self
    {
        return new self($user, self::interactiveLoginTime($user));
    }

    /**
     * Records an interactive login (password, LDAP, SSO …) of the current session.
     */
    public static function recordInteractiveLogin(User $user): void
    {
        if (Yii::$app->has('session')) {
            Yii::$app->session->set(self::SESSION_KEY, ['userId' => (int)$user->id, 'time' => time()]);
        }
    }

    /**
     * Drops the recorded interactive login; any other login (e.g. by cookie) must not inherit it.
     */
    public static function forgetInteractiveLogin(): void
    {
        if (Yii::$app->has('session') && Yii::$app->session->getIsActive()) {
            Yii::$app->session->remove(self::SESSION_KEY);
        }
    }

    public function requiresPassword(): bool
    {
        return CheckPasswordValidator::hasPassword($this->user);
    }

    public function isThrottled(): bool
    {
        return (int)Yii::$app->cache->get($this->throttleKey()) >= self::MAX_FAILURES;
    }

    public function check(?string $password): bool
    {
        if (!$this->requiresPassword()) {
            return $this->loggedInAt !== null && $this->loggedInAt >= time() - self::MAX_LOGIN_AGE;
        }

        if ($this->isThrottled()) {
            return false;
        }

        if ($password !== null && $password !== '' && $this->user->currentPassword->validatePassword($password)) {
            Yii::$app->cache->delete($this->throttleKey());
            return true;
        }

        $key = $this->throttleKey();
        Yii::$app->cache->set($key, (int)Yii::$app->cache->get($key) + 1, self::FAILURE_WINDOW);
        return false;
    }

    private function throttleKey(): string
    {
        return 'oauth.recentAuthentication.' . $this->user->id;
    }

    /**
     * Bound to the user: an impersonation switches the identity within the same session.
     */
    private static function interactiveLoginTime(User $user): ?int
    {
        if (!Yii::$app->has('session') || !Yii::$app->session->getIsActive()
            || Yii::$app->user->getImpersonation()->isActive()) {
            return null;
        }

        $login = Yii::$app->session->get(self::SESSION_KEY);
        if (!is_array($login) || ($login['userId'] ?? null) !== (int)$user->id || !is_int($login['time'] ?? null)) {
            return null;
        }

        return $login['time'];
    }
}
