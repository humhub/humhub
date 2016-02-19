<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship;

/**
 * Friedship Module
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\friendship\controllers';

    /**
     * Returns if the friendship system is enabled
     * 
     * @return boolean is enabled
     */
    public function getIsEnabled()
    {
        if (\humhub\models\Setting::Get('enable', 'friendship')) {
            return true;
        }

        return false;
    }

}
