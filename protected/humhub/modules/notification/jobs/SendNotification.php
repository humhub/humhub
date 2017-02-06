<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\jobs;

use Yii;
use humhub\components\queue\ActiveJob;

/**
 * Description of SendNotification
 *
 * @author buddha
 * @since 1.2
 */
class SendNotification extends ActiveJob
{
    /**
     * @var humhub\modules\notification\components\BaseNotification notification instance
     */
    public $notification;
    
    /**
     * @var \humhub\modules\user\models\User Recepient user id.
     */
    public $recepient;
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        Yii::$app->notification->send($this->notification, $this->recepient); 
    }
}
