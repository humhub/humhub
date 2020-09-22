<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue\driver;

use humhub\modules\queue\interfaces\QueueInfoInterface;
use yii\queue\redis\Queue;

/**
 * Redis queue driver
 *
 * @since 1.2
 * @author Luke
 */
class Redis extends Queue implements QueueInfoInterface
{
    /**
     * @return int|null the number of waiting jobs in the queue
     */
    public function getWaitingJobCount()
    {
        return (int)$this->redis->llen($this->channel . ".waiting");
    }

    /**
     * @return int|null the number of delayed jobs in the queue
     */
    public function getDelayedJobCount()
    {
        return (int)$this->redis->zcount($this->channel . ".delayed", '-inf', '+inf');
    }

    /**
     * @return int|null the number of reserved jobs in the queue
     */
    public function getReservedJobCount()
    {
        return (int)$this->redis->zcount($this->channel . ".reserved", '-inf', '+inf');
    }

    /**
     * @return int|null the number of done jobs in the queue
     */
    public function getDoneJobCount()
    {
        $total = $this->redis->get($this->channel . ".message_id");
        return $total - $this->getWaitingJobCount() - $this->getDelayedJobCount() - $this->getReservedJobCount();
    }
}
