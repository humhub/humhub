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
use humhub\components\health\PendingMigrations;

/**
 * Pending migrations of enabled modules. A warning only: the instance still serves users.
 *
 * @since 1.20
 */
class ModuleMigrationsCheck extends HealthCheck
{
    public string $id = 'migrations:module';

    public HealthLevel $level = HealthLevel::Ready;

    public bool $requiresInstallation = true;

    public ?PendingMigrations $migrations = null;

    public function run(): HealthResult
    {
        $pending = ($this->migrations ?? new PendingMigrations())->getModulePending();
        if ($pending === []) {
            return HealthResult::ok();
        }

        $parts = [];
        foreach ($pending as $moduleId => $names) {
            $parts[] = sprintf('%s: %d pending migration(s)', $moduleId, count($names));
        }

        return HealthResult::warning(implode('; ', $parts), ['pending' => $pending]);
    }
}
