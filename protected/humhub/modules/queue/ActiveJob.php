<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue;

use yii\base\BaseObject;
use humhub\modules\queue\interfaces\JobInterface;

/**
 * ActiveJob
 * 
 * @since 1.3
 * @author Luke
 */
abstract class ActiveJob extends BaseObject implements JobInterface
{

    /**
     * Runs this job
     */
    abstract public function run();

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        return $this->run();
    }

}
