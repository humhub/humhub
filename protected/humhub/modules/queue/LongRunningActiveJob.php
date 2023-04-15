<?php

namespace humhub\modules\queue;

use yii\queue\RetryableJobInterface;

abstract class LongRunningActiveJob extends ActiveJob implements RetryableJobInterface
{
    public function getTtr()
    {
        return 60 * 60;
    }
}
