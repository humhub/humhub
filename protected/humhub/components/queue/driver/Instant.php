<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\queue\driver;

use Yii;
use yii\base\Event;
use yii\queue\Queue as BaseQueue;
use yii\queue\ErrorEvent;

/**
 * Instant queue driver, mainly used for testing purposes
 *
 * @since 1.2
 * @author buddha
 */
class Instant extends BaseQueue
{

    /**
     * @var int the message counter
     */
    protected $messageId = 1;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(Queue::class, Queue::EVENT_AFTER_ERROR, function(ErrorEvent $errorEvent) {
            /* @var $exception \Expection */
            $exception = $errorEvent->error;
            Yii::error('Could not execute queued job! Message: ' . $exception->getMessage() . ' Trace:' . $exception->getTraceAsString(), 'queue');
        });
    }

    /**
     * @inheritdoc
     */
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        $this->handleMessage($this->messageId, $message, $ttr);
        $this->messageId++;
    }

    /**
     * @inheritdoc
     */
    protected function status($id)
    {
        return BaseQueue::STATUS_DONE;
    }

}
