<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health\checks;

use humhub\components\health\HealthCheck;
use humhub\components\health\HealthResult;
use humhub\modules\user\components\MaintenanceModeGate;

/**
 * Maintenance mode as a warning, so a mode nobody switched off again shows up in monitoring that only
 * reads the status. `ready` stays ok: users get the maintenance page.
 *
 * @since 1.20
 */
class MaintenanceCheck extends HealthCheck
{
    public string $id = 'maintenance';

    public bool $requiresInstallation = true;

    /**
     * @var bool|null null asks MaintenanceModeGate
     */
    public ?bool $active = null;

    public function run(): HealthResult
    {
        return ($this->active ?? MaintenanceModeGate::isActive())
            ? HealthResult::warning('Maintenance mode enabled')
            : HealthResult::ok();
    }
}
