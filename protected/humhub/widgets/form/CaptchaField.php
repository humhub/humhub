<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


namespace humhub\widgets\form;

use humhub\components\Widget;
use Yii;

class CaptchaField extends Widget
{
    public static function widget($config = [])
    {
        return Yii::$app->captcha->createInputWidget($config);
    }
}
