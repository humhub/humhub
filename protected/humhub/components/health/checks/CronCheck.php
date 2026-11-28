<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health\checks;

use humhub\components\health\HealthCheck;
use humhub\components\health\HealthResult;
use Yii;

/**
 * When cron ran last. A warning after `$warningAge`, a failure after `$failureAge` (0 = never a failure):
 * by then every daily job (summary mails, cleanups) was missed.
 *
 * @since 1.20
 */
class CronCheck extends HealthCheck
{
    public string $id = 'cron';

    public bool $requiresInstallation = true;

    public int $warningAge = 3600;

    public int $failureAge = 86400;

    /**
     * @var int|null unix time of the last run, 0 = never; null reads the setting `cronLastRun`
     */
    public ?int $lastRun = null;

    /**
     * @var int|null current unix time; null = time()
     */
    public ?int $now = null;

    public function run(): HealthResult
    {
        $lastRun = $this->lastRun ?? (int)Yii::$app->settings->getUncached('cronLastRun');
        if ($lastRun <= 0) {
            return HealthResult::warning('Cron has never run');
        }

        $iso = gmdate('Y-m-d\TH:i:s\Z', $lastRun);
        $data = ['lastRun' => $iso];
        $age = ($this->now ?? time()) - $lastRun;
        $message = sprintf('Cron last ran %d minutes ago', intdiv($age, 60));

        if ($this->failureAge > 0 && $age > $this->failureAge) {
            return HealthResult::failure($message, $data);
        }

        if ($age > $this->warningAge) {
            return HealthResult::warning($message, $data);
        }

        return HealthResult::ok('last run ' . $iso, $data);
    }
}
