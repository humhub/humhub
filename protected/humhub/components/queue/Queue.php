<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\queue;

use Yii;
use yii\base\Event;
use zhuravljov\yii\queue\Queue as BaseQueue;
use zhuravljov\yii\queue\ErrorEvent;

/**
 * Queue
 *
 * @since 1.2
 * @author Luke
 */
class Queue extends BaseQueue
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(Queue::class, Queue::EVENT_AFTER_ERROR, function(ErrorEvent $errorEvent) {
            /* @var $exception \Expection */
            $exception = $errorEvent->error;
            Yii::error('Could not executed queued job! Message: ' . $exception->getMessage() . ' Trace:' . $exception->getTraceAsString(), 'queue');
        });
    }

}
