<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\services;

use Yii;

class SearchJobService
{
    public const MUTEX_ID = 'SearchQueueJob';
    public int $retryAttemptNum = 10;
    public int $retryDelayInSeconds = 5 * 60;

    public function run(callable $callable): bool
    {
        if (!Yii::$app->mutex->acquire(self::MUTEX_ID, $this->retryDelayInSeconds)) {
            return false;
        }

        try {
            call_user_func($callable);
            Yii::$app->mutex->release(self::MUTEX_ID);
        } catch (\Exception $e) {
            Yii::$app->mutex->release(self::MUTEX_ID);
            return false;
        }

        return true;
    }

    /**
     * @param $attempt Number of the current attempt
     * @return bool
     */
    public function canRetry($attempt): bool
    {
        return $attempt < $this->retryAttemptNum;
    }
}
