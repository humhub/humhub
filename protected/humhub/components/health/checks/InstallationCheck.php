<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health\checks;

use humhub\components\health\HealthCheck;
use humhub\components\health\HealthLevel;
use humhub\components\health\HealthResult;
use humhub\components\health\HealthState;

/**
 * The lifecycle state as a check. A not yet installed instance is `ok`, so orchestrators route traffic
 * to the installer; the reason is listed in the notes.
 *
 * @since 1.20
 */
class InstallationCheck extends HealthCheck
{
    public string $id = 'installation';

    public HealthLevel $level = HealthLevel::Ready;

    public string $state = HealthState::INSTALLED;

    public function run(): HealthResult
    {
        return match ($this->state) {
            HealthState::INSTALLED => HealthResult::ok(),
            HealthState::MAINTENANCE => HealthResult::ok('Maintenance mode enabled', notable: true),
            HealthState::NOT_INSTALLED,
            HealthState::DATABASE_CONFIGURED,
            HealthState::DATABASE_CREATED => HealthResult::ok('Installation not completed, setup wizard pending', notable: true),
            HealthState::DATABASE_UNAVAILABLE => HealthResult::unknown('Installation state unknown: database unavailable'),
            default => HealthResult::failure('Invalid installation state'),
        };
    }
}
