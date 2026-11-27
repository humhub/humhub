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
use yii\base\Component;
use yii\db\Query;
use yii\queue\db\Queue;

/**
 * Whether queued jobs are picked up. Only measurable for the database queue; a module providing another
 * driver can add its own check.
 *
 * @since 1.20
 */
class QueueCheck extends HealthCheck
{
    public string $id = 'queue';

    public bool $requiresInstallation = true;

    /**
     * @var int seconds a job may wait before the queue worker counts as not running
     */
    public int $waitThreshold = 1200;

    public ?Component $queue = null;

    public function run(): HealthResult
    {
        $queue = $this->queue ?? Yii::$app->queue;
        if (!$queue instanceof Queue) {
            return HealthResult::ok(sprintf('not measurable for %s', $queue::class));
        }

        $waitingQuery = (new Query())
            ->from($queue->tableName)
            ->andWhere(['channel' => $queue->channel, 'reserved_at' => null]);

        // Delayed jobs that are not due yet do not count as waiting
        $waitingQuery->andWhere('[[pushed_at]] <= :now - [[delay]]', [':now' => time()]);

        $waiting = (int)(clone $waitingQuery)->count('*', $queue->db);
        $stale = (int)(clone $waitingQuery)
            ->andWhere('[[pushed_at]] <= :time - [[delay]]', [':time' => time() - $this->waitThreshold])
            ->count('*', $queue->db);

        $data = ['waiting' => $waiting];

        if ($stale > 0) {
            return HealthResult::warning(
                sprintf('%d job(s) waiting longer than %d minutes, is the queue worker running?', $stale, intdiv($this->waitThreshold, 60)),
                $data,
            );
        }

        return HealthResult::ok(sprintf('%d waiting', $waiting), $data);
    }
}
