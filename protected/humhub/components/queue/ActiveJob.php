<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\queue;

use yii\base\Object;
use zhuravljov\yii\queue\Job;

/**
 * ActiveJob
 *
 * @since 1.2
 * @author Luke
 */
abstract class ActiveJob extends Object implements Job
{
    /**
     * Runs this job
     */
    abstract public function run();
}
