<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\queue\driver;

use zhuravljov\yii\queue\sync\Driver;

/**
 * Sync queue driver
 *
 * @since 1.2
 * @author Luke
 */
class Sync extends Driver
{

    public $handle = true;

}
