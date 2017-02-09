<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use humhub\components\queue\ActiveJob;
use humhub\modules\admin\models\Log;

/**
 * CleanupLog deletes older log records from log table
 *
 * @since 1.2
 * @author Luke
 */
class CleanupLog extends ActiveJob
{

    /**
     * @var int seconds before delete old log messages
     */
    public $cleanupInterval = 60 * 60 * 24 * 7;

    /**
     * @inheritdoc
     */
    public function run()
    {
        Log::deleteAll(['<', 'log_time', time() - $this->cleanupInterval]);
    }

}
