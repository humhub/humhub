<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

use humhub\components\InstallationState;
use yii\base\InvalidConfigException;

/**
 * The lifecycle position reported as `state`. Informational: it never drives exit or HTTP codes.
 * Consumers must tolerate values added later.
 *
 * @since 1.20
 */
final class HealthState
{
    public const UNAVAILABLE = 'unavailable';
    public const INVALID = 'invalid';
    public const NOT_INSTALLED = 'not-installed';
    public const DATABASE_CONFIGURED = 'database-configured';
    public const DATABASE_CREATED = 'database-created';
    public const INSTALLED = 'installed';
    public const MAINTENANCE = 'maintenance';

    /**
     * @param callable(): InstallationState $installationState may throw InvalidConfigException for a stored invalid state
     * @param callable(): bool $maintenanceActive only asked for an installed instance
     */
    public static function resolve(callable $installationState, callable $maintenanceActive): string
    {
        try {
            /** @var InstallationState $state */
            $state = $installationState();
        } catch (InvalidConfigException) {
            return self::INVALID;
        }

        if ($state->isDatabaseUnreachable()) {
            return self::UNAVAILABLE;
        }

        if ($state->hasState(InstallationState::STATE_INSTALLED)) {
            return $maintenanceActive() ? self::MAINTENANCE : self::INSTALLED;
        }

        if ($state->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            return self::DATABASE_CREATED;
        }

        if ($state->hasState(InstallationState::STATE_DATABASE_CONFIGURED)) {
            return self::DATABASE_CONFIGURED;
        }

        return self::NOT_INSTALLED;
    }

    public static function isInstalled(string $state): bool
    {
        return $state === self::INSTALLED || $state === self::MAINTENANCE;
    }
}
