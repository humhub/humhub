<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

use yii\base\Event;

/**
 * Triggered by {@see HealthService} for the level `all` on an installed instance, so modules can add checks.
 *
 * ```php
 * // config.php
 * ['class' => HealthService::class, 'event' => HealthService::EVENT_COLLECT_CHECKS,
 *  'callback' => [Events::class, 'onHealthCollectChecks']],
 *
 * // Events.php
 * public static function onHealthCollectChecks(CollectChecksEvent $event): void
 * {
 *     $event->addCheck('ldap', new LdapBindCheck());
 * }
 * ```
 *
 * @since 1.20
 */
class CollectChecksEvent extends Event
{
    /** @var array<int, array{0: string, 1: HealthCheck}> */
    private array $checks = [];

    /**
     * @param string $moduleId id of an enabled module; the check is reported as `module:<moduleId>:<check id>`
     */
    public function addCheck(string $moduleId, HealthCheck $check): void
    {
        $this->checks[] = [$moduleId, $check];
    }

    /**
     * @return array<int, array{0: string, 1: HealthCheck}>
     */
    public function getChecks(): array
    {
        return $this->checks;
    }
}
