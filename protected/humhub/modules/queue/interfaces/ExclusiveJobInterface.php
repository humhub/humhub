<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue\interfaces;

/**
 * ExclusiveJobInterface can be added to an ActiveJob to ensure this task is only
 * queued once. As example this is useful for asynchronous jobs like search index rebuild.
 *
 * @see \humhub\modules\queue\ActiveJob
 * @author Luke
 */
interface ExclusiveJobInterface
{

    public function getExclusiveJobId();
}
