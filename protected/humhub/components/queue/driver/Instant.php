<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\queue\driver;

use Yii;
use yii\base\Event;
use zhuravljov\yii\queue\ErrorEvent;
use zhuravljov\yii\queue\Queue;

/**
 * Instant queue driver, mainly used for testing purposes
 *
 * @since 1.2
 * @author buddha
 */
class Instant extends Queue
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
            Yii::error('Could not execute queued job! Message: ' . $exception->getMessage() . ' Trace:' . $exception->getTraceAsString(), 'queue');
        });
    }

    /**
     * @inheritdoc
     */
    protected function sendMessage($message, $timeout)
    {
        $this->handleMessage($message);
    }

}
