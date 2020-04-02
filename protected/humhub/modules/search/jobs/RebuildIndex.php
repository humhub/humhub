<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\jobs;

use Yii;
use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use yii\queue\RetryableJobInterface;

/**
 * RebuildIndex job
 *
 * @since 1.3
 * @author Luke
 */
class RebuildIndex extends ActiveJob implements ExclusiveJobInterface, RetryableJobInterface
{

    /**
     * @var int maximum 2 hours
     */
    private $maxExecutionTime = 60 * 60 * 2;

    /**
     * @inhertidoc
     */
    public function getExclusiveJobId()
    {
        return 'search.rebuild-index';
    }

    /**
     * @inhertidoc
     */
    public function run()
    {
        Yii::$app->search->rebuild();
    }

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return $this->maxExecutionTime;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return false;
    }
}
