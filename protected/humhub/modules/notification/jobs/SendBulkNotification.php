<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\jobs;

use humhub\modules\queue\ActiveJob;
use humhub\modules\user\components\ActiveQueryUser;
use Yii;

/**
 * Description of SendNotification
 *
 * @author buddha
 * @since 1.2
 */
class SendBulkNotification extends ActiveJob
{
    /**
     * @var array Basenotification data as array.
     */
    public $notification;

    /**
     * @var ActiveQueryUser the query to determine which users should receive this notification
     */
    public $query;

    /**
     * @inheritdoc
     */
    public function run()
    {
        Yii::$app->notification->sendBulk($this->notification, $this->query);
    }
}
