<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;

class HelpController extends \yii\console\controllers\HelpController
{
    /**
     * @inheritdoc
     */
    protected function validateControllerClass($controllerClass)
    {
        try {
            return parent::validateControllerClass($controllerClass);
        } catch (\Throwable $e) {
            // Don't log errors when the module "RESTful API" is not installed, but it is used by another module,
            // for example, if some module controller is extended from humhub\modules\rest\components\BaseController
            if (!preg_match('/Class "' . preg_quote('humhub\\modules\\rest\\') . '.+?" not found/', $e->getMessage())) {
                Yii::warning('File: ' . $e->getFile() . ', Error: ' . $e->getMessage(), 'console');
            }
            return false;
        }
    }
}
