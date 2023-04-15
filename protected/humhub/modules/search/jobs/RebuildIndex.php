<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\jobs;

use Yii;
use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;

/**
 * RebuildIndex job
 *
 * @since 1.3
 * @author Luke
 */
class RebuildIndex extends LongRunningActiveJob implements ExclusiveJobInterface
{
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
        return parent::getTtr() * 2;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return false;
    }
}
