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
class SendBulkNotification extends ActiveJob
{
    /**
     * @var array Basenotification data as array.
     */
    public $notification;
    
    /**
     * @var integer[] Recepient userids.
     */
    public $recepients;
    
    /**
     * @inheritdoc
     */
    public function run()
    {   
        Yii::$app->notification->sendBulk($this->notification, $this->recepients); 
    }
}
