<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use yii\queue\db\Queue;

/**
 * A queue the delivery layer takes as honouring delays (a database queue), which only records the
 * pushed jobs; a test runs them itself.
 */
class DelayingTestQueue extends Queue
{
    private int $lastId = 0;

    /**
     * @inheritdoc
     */
    protected function pushMessage($payload, $ttr, $delay, $priority)
    {
        return (string)++$this->lastId;
    }
}
