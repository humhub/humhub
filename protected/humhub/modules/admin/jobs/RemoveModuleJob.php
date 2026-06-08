<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\services\ModuleService;
use Yii;

class RemoveModuleJob extends ActiveJob implements ExclusiveJobInterface
{
    public $moduleId;

    public function getExclusiveJobId()
    {
        return "module.$this->moduleId.remove";
    }


    public function run()
    {
        $module = Yii::$app->moduleManager->getModule($this->moduleId);
        (new ModuleService($module))->remove();
    }
}
