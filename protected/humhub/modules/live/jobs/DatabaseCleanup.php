<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\jobs;

use humhub\modules\live\driver\Poll;
use humhub\modules\live\models\Live;
use humhub\modules\queue\LongRunningActiveJob;
use Yii;

/**
 * DatabaseCleanup removes old live events
 *
 * @since 1.2
 * @author Luke
 */
class DatabaseCleanup extends LongRunningActiveJob
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->live->driver instanceof Poll) {
            Live::deleteAll('created_at +' . Yii::$app->live->driver->maxLiveEventAge . ' < ' . time());
        }
    }

}
