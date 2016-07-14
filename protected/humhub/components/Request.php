<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;

/**
 * @inheritdoc
 * 
 * 
 * @author luke
 */
class Request extends \yii\web\Request
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (\humhub\models\Setting::isInstalled()) {
            $secret = \humhub\models\Setting::Get('secret');
            if ($secret != "") {
               $this->cookieValidationKey = \humhub\models\Setting::Get('secret');
            } 
        } else {
            $this->cookieValidationKey = 'installer';
        }
    }

}
