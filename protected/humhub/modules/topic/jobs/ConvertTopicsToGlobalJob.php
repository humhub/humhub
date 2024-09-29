<?php

namespace humhub\modules\topic\jobs;

use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\topic\models\Topic;

class ConvertTopicsToGlobalJob extends ActiveJob implements ExclusiveJobInterface
{
    public function getExclusiveJobId()
    {
        return 'module.topics.global-conversion';
    }

    public function run()
    {
        Topic::convertToGlobal();
    }
}
