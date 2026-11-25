<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

use yii\base\BaseObject;

/**
 * Base class of a health check.
 *
 * A check answers one question and returns a {@see HealthResult}; exceptions are caught by the
 * {@see HealthService} and reported as `unknown`. For a check added by a module, `level` and
 * `requiresInstallation` are ignored: module checks always belong to `all` and need an installed instance.
 *
 * Timeouts are cooperative: PHP cannot interrupt a running call. Pass `$timeout` to your own clients
 * (HTTP, LDAP, ...). A check that takes longer than `$timeout` is reported as `warning` even if it succeeded.
 *
 * @since 1.20
 */
abstract class HealthCheck extends BaseObject
{
    /**
     * @var string unique id in the report; a module check is reported as `module:<moduleId>:<id>`
     */
    public string $id = '';

    public HealthLevel $level = HealthLevel::All;

    /**
     * @var bool whether the check needs an installed instance with a working database. Before the
     * installation it reports `ok`, while the database is unavailable it is skipped as `unknown`.
     */
    public bool $requiresInstallation = false;

    /**
     * @var int seconds the check may take
     */
    public int $timeout = 2;

    /**
     * @var int|null seconds a result is cached; null uses the default (0 for core checks, 300 for module checks).
     * Results are only cached for the level `all`.
     */
    public ?int $cacheTtl = null;

    abstract public function run(): HealthResult;
}
