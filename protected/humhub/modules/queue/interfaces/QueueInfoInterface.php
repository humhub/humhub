<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue\interfaces;

/**
 * QueueInfoInterface
 *
 * @author Luke
 */
interface QueueInfoInterface
{
    /**
     * @return int|null the number of waiting jobs in the queue
     */
    public function getWaitingJobCount();

    /**
     * @return int|null the number of delayed jobs in the queue
     */
    public function getDelayedJobCount();

    /**
     * @return int|null the number of reserved jobs in the queue
     */
    public function getReservedJobCount();

    /**
     * @return int|null the number of done jobs in the queue
     */
    public function getDoneJobCount();
}
