<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use humhub\components\health\HealthLevel;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Reports the health of this installation, for container probes and monitoring.
 *
 * ```
 * yii health/status [--check=live|ready|all] [--format=text|json] [--fail-on=warning|failure]
 * ```
 *
 * Exit codes follow Nagios: 0 ok, 1 warning, 2 failure, 3 unknown. With `--fail-on=failure` only 0 and 1
 * are used (Docker HEALTHCHECK reserves 2):
 *
 * ```
 * HEALTHCHECK CMD php yii health/status --check=ready --fail-on=failure
 * ```
 *
 * Deliberately without #[WithoutModuleAutoload]: pending module migrations and module checks need the
 * enabled modules. A module that breaks the bootstrap makes the command fail, which is still the right signal.
 *
 * @since 1.20
 */
class HealthController extends Controller
{
    public $defaultAction = 'status';

    /**
     * @var string live, ready or all
     */
    public string $check = 'all';

    /**
     * @var string text or json
     */
    public string $format = 'text';

    /**
     * @var string warning (exit codes 0 to 3) or failure (only 0 and 1)
     */
    public string $failOn = 'warning';

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['check', 'format', 'failOn']);
    }

    /**
     * Prints the health report.
     */
    public function actionStatus(): int
    {
        $level = HealthLevel::tryFrom($this->check);
        if ($level === null || !in_array($this->format, ['text', 'json'], true)
            || !in_array($this->failOn, ['warning', 'failure'], true)) {
            $this->stderr("Invalid option. Usage: yii health/status [--check=live|ready|all] [--format=text|json] [--fail-on=warning|failure]\n");
            return ExitCode::USAGE;
        }

        $report = Yii::$app->health->run($level);

        $this->stdout($this->format === 'json'
            ? json_encode($report->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n"
            : $report->toText());

        return $report->getExitCode($this->failOn === 'failure');
    }
}
