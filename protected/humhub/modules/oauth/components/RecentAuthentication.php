<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use humhub\modules\user\components\CheckPasswordValidator;
use humhub\modules\user\models\Session;
use humhub\modules\user\models\User;
use Yii;

/**
 * Re-authentication before handing out a credential that bypasses 2FA (a personal access
 * token, a client secret): users with a local password confirm it, users without one must
 * have logged in to the current session recently.
 *
 * @since 1.20
 */
final class RecentAuthentication
{
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
     * @param int|null $loggedInAt login time of the session, null if unknown
     */
    public function __construct(private readonly User $user, private readonly ?int $loggedInAt)
    {
    }

    public static function forCurrentSession(User $user): self
    {
        return new self($user, self::currentSessionLoginTime());
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

    private static function currentSessionLoginTime(): ?int
    {
        if (!Yii::$app->has('session') || !Yii::$app->session->getIsActive()) {
            return null;
        }

        $row = Session::findOne(['id' => Yii::$app->session->getId()]);
        return $row?->logged_in_at === null ? null : (int)$row->logged_in_at;
    }
}
