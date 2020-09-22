<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\jobs;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\user\models\User;
use Yii;
use humhub\modules\queue\ActiveJob;

/**
 * Description of SendNotification
 *
 * @author buddha
 * @since 1.2
 */
class SendNotification extends ActiveJob
{
    /**
     * @var BaseNotification notification instance
     */
    public $notification;

    /**
     * @var int the user id of the recipient
     */
    public $recipientId;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $recipient = User::findOne(['id' => $this->recipientId]);
        if ($recipient !== null) {
            Yii::$app->notification->send($this->notification, $recipient);
        }
    }
}
