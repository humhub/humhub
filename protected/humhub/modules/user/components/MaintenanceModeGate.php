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
 * Blocks the platform for non-admins while maintenance mode is active
 * (see `docs/develop/user-gates.md`).
 *
 * Authenticated non-admins are logged out at interception time ({@see onIntercept()})
 * and redirected to the login route, which renders the dedicated maintenance view.
 *
 * Replaces the former `ControllerAccess::RULE_MAINTENANCE_MODE` fixed rule.
 *
 * @since 1.19
 */
class MaintenanceModeGate extends UserGate
{
    /**
     * Name of the settings entry toggling maintenance mode.
     */
    public const SETTING_MAINTENANCE_MODE = 'maintenanceMode';

    /**
     * Whether maintenance mode is currently enabled — independent of the current user;
     * admins can still use the platform while it is active (see [[isOpen()]]).
     *
     * Use this instead of reading the `maintenanceMode` setting directly.
     */
    public static function isActive(): bool
    {
        return (bool)Yii::$app->settings->get(self::SETTING_MAINTENANCE_MODE);
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return 'maintenance-mode';
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder(): int
    {
        return self::SORT_MAINTENANCE;
    }

    /**
     * @inheritdoc
     */
    public function isOpen(): bool
    {
        return static::isActive() && !Yii::$app->user->isAdmin();
    }

    /**
     * @inheritdoc
     */
    public function getRoute(): array
    {
        return ['/user/auth/login'];
    }

    /**
     * The login flow must stay reachable so admins can sign in during maintenance.
     *
     * @inheritdoc
     */
    public function getAllowedRoutes(): array
    {
        return ['user/auth/password', 'user/auth/external'];
    }

    /**
     * Maintenance blocks every channel, including the API (answered with 403).
     *
     * @inheritdoc
     */
    public function appliesTo(RequestClass $requestClass): bool
    {
        return true;
    }

    /**
     * The setting is cached in memory and toggling maintenance must take effect
     * instantly on running sessions, so the gate is evaluated on every request.
     *
     * @inheritdoc
     */
    public function isCacheable(): bool
    {
        return false;
    }

    /**
     * Ends the session of authenticated users (the gate only intercepts non-admins);
     * they stay logged out until the maintenance is completed.
     *
     * @inheritdoc
     */
    public function onIntercept(): void
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
            Yii::$app->getView()->warn(Yii::t('error', 'Maintenance mode activated: You have been automatically logged out and will no longer have access the platform until the maintenance has been completed.'));
        }
    }
}
