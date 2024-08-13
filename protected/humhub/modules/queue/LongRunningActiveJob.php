<?php

namespace humhub\modules\queue;

use Yii;
use yii\queue\RetryableJobInterface;

/**
 * @since 1.15
 */
abstract class LongRunningActiveJob extends ActiveJob implements RetryableJobInterface
{
    public function getTtr()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('queue');

        return $module->longRunningJobTtr;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return false;
    }
}
