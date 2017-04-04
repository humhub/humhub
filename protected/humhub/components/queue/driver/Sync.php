<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\queue\driver;

use Yii;
use yii\base\Event;
use yii\base\Application;
use yii\base\NotSupportedException;
use zhuravljov\yii\queue\ErrorEvent;
use zhuravljov\yii\queue\Queue;

/**
 * Sync queue driver
 *
 * @since 1.2
 * @author Luke
 */
class Sync extends Queue
{

    /**
     * @var boolean
     */
    public $handle = true;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @inheritdoc
     */
    public function init()
    {

        parent::init();

        if ($this->handle) {
            Yii::$app->on(Application::EVENT_AFTER_REQUEST, function () {
                ob_start();
                $this->run();

                // Important, breaks downloads
                ob_end_clean();
            });
        }

        Event::on(Queue::class, Queue::EVENT_AFTER_ERROR, function(ErrorEvent $errorEvent) {
            /* @var $exception \Expection */
            $exception = $errorEvent->error;
            Yii::error('Could not execute queued job! Message: ' . $exception->getMessage() . ' Trace:' . $exception->getTraceAsString(), 'queue');
        });
    }

    /**
     * Runs all jobs from queue.
     */
    public function run()
    {
        while (($message = array_shift($this->messages)) !== null) {
            $this->handleMessage($message);
        }
    }

    /**
     * @inheritdoc
     */
    protected function sendMessage($message, $timeout)
    {
        if ($timeout) {
            throw new NotSupportedException('Delayed work is not supported in the driver.');
        }

        $this->messages[] = $message;
    }

}
