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
            Yii::warning('File: '. $e->getFile() . ', Error: ' . $e->getMessage(), 'console');
            return false;
        }
    }
}
