<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use Yii;

class DisableModuleJob extends ActiveJob implements ExclusiveJobInterface
{
    public $moduleId;

    public function getExclusiveJobId()
    {
        return "module.$this->moduleId.disable";
    }


    public function run()
    {
        Yii::$app->moduleManager->getModule($this->moduleId)->disable();
    }
}
