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
use Yii;
use yii\db\Connection;

/**
 * Whether the database answers. Before the installation a database that is not (yet) usable is fine,
 * the installer has to stay reachable.
 *
 * @since 1.20
 */
class DatabaseCheck extends HealthCheck
{
    public string $id = 'database';

    public HealthLevel $level = HealthLevel::Ready;

    public string $state = HealthState::INSTALLED;

    public ?Connection $db = null;

    public function run(): HealthResult
    {
        if ($this->state === HealthState::NOT_INSTALLED) {
            return HealthResult::ok('Database not configured yet');
        }

        if ($this->state === HealthState::DATABASE_UNAVAILABLE) {
            return HealthResult::failure('Database not reachable');
        }

        try {
            ($this->db ?? Yii::$app->db)->createCommand('SELECT 1')->queryScalar();
        } catch (\Throwable $e) {
            if (in_array($this->state, [HealthState::DATABASE_CONFIGURED, HealthState::DATABASE_CREATED], true)) {
                return HealthResult::ok('Database not set up yet');
            }

            return HealthResult::failure('Database not reachable: ' . $e->getMessage());
        }

        return HealthResult::ok();
    }
}
