<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\jobs;

use Yii;
use humhub\modules\live\models\Live;
use humhub\modules\queue\ActiveJob;
use humhub\modules\live\driver\Poll;

/**
 * DatabaseCleanup removes old live events
 *
 * @since 1.2
 * @author Luke
 */
class DatabaseCleanup extends ActiveJob
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->live->driver instanceof Poll) {
            Live::deleteAll('created_at +' . Yii::$app->live->maxLiveEventAge . ' < ' . time());
        }
    }

}
