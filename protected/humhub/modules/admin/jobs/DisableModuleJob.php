<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use Yii;
use humhub\modules\queue\ActiveJob;

class DisableModuleJob extends ActiveJob
{
    public $moduleId;

    public function run()
    {
        Yii::$app->moduleManager->getModule($this->moduleId)->disable();
    }
}
