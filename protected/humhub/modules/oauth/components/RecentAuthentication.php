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

    private readonly ?int $loggedInAt;

    /**
     * @param int|null $loggedInAt login time of the session; -1 = read it from the current session
     */
    public function __construct(private readonly User $user, ?int $loggedInAt = -1)
    {
        $this->loggedInAt = $loggedInAt === -1 ? self::currentSessionLoginTime() : $loggedInAt;
    }

    public function requiresPassword(): bool
    {
        return CheckPasswordValidator::hasPassword($this->user);
    }

    public function check(?string $password): bool
    {
        if ($this->requiresPassword()) {
            return $password !== null && $password !== '' && $this->user->currentPassword->validatePassword($password);
        }

        return $this->loggedInAt !== null && $this->loggedInAt >= time() - self::MAX_LOGIN_AGE;
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
