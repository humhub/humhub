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

/**
 * RebuildIndex job
 *
 * @since 1.3
 * @author Luke
 */
class RebuildIndex extends ActiveJob implements ExclusiveJobInterface
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

}
