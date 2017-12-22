<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue\driver;

use yii\queue\Queue;

/**
 * Instant queue driver, mainly used for testing purposes
 *
 * @since 1.2
 * @author buddha
 */
class Instant extends Queue
{

    /**
     * @var int the message counter
     */
    protected $messageId = 1;

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
        return Queue::STATUS_DONE;
    }

}
