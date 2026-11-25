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
 * Pending core migrations: the schema does not match the code, the instance must not serve users.
 * Typical while the Docker entrypoint runs `migrate/up` after an image update.
 *
 * @since 1.20
 */
class CoreMigrationsCheck extends HealthCheck
{
    public string $id = 'migrations:core';

    public HealthLevel $level = HealthLevel::Ready;

    public bool $requiresInstallation = true;

    public ?PendingMigrations $migrations = null;

    public function run(): HealthResult
    {
        $pending = ($this->migrations ?? new PendingMigrations())->getCorePending();
        if ($pending === []) {
            return HealthResult::ok();
        }

        $count = array_sum(array_map('count', $pending));

        return HealthResult::failure(sprintf('%d pending core migration(s)', $count), ['pending' => $pending]);
    }
}
