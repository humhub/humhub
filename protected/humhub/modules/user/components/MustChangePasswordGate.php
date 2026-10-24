<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\components\gates\RequestClass;
use humhub\components\gates\UserGate;
use Yii;

/**
 * Forces users flagged with "must change password" through the password change form
 * before they can use the platform (see `docs/develop/user-gates.md`).
 *
 * Replaces the former `ControllerAccess::RULE_MUST_CHANGE_PASSWORD` fixed rule.
 *
 * @since 1.19
 */
class MustChangePasswordGate extends UserGate
{
    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return 'must-change-password';
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder(): int
    {
        return self::SORT_PASSWORD;
    }

    /**
     * @inheritdoc
     */
    public function isOpen(): bool
    {
        return Yii::$app->user->mustChangePassword();
    }

    /**
     * @inheritdoc
     */
    public function getRoute(): array
    {
        return [Yii::$app->user->mustChangePasswordRoute];
    }

    /**
     * @inheritdoc
     */
    public function getAllowedRoutes(): array
    {
        return ['user/auth/logout'];
    }

    /**
     * A compromised password must not be usable through any channel, so this gate
     * also applies to API requests (answered with 403).
     *
     * @inheritdoc
     */
    public function appliesTo(RequestClass $requestClass): bool
    {
        return true;
    }

    /**
     * The check is an in-memory attribute lookup on the loaded identity and must take
     * effect instantly (e.g. right after an admin flags the user), so it is evaluated
     * on every request.
     *
     * @inheritdoc
     */
    public function isCacheable(): bool
    {
        return false;
    }
}
