<?php

namespace humhub\modules\queue;

use Yii;
use yii\queue\RetryableJobInterface;

abstract class LongRunningActiveJob extends ActiveJob implements RetryableJobInterface
{
    public function getTtr()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('queue');

        return $module->longRunningJobTtr;
    }
}
