<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\jobs;

use humhub\modules\marketplace\components\LicenceManager;
use humhub\modules\queue\ActiveJob;

class PeActiveCheckJob extends ActiveJob
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        LicenceManager::get();
    }
}
