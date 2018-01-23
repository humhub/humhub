<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue\driver;

use yii\queue\sync\Queue;

/**
 * Sync queue driver
 *
 * @since 1.2
 * @author Luke
 */
class Sync extends Queue
{

    /**
     * @inheritdoc
     */
    public $handle = true;

}
