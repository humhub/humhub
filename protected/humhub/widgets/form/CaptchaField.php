<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\form;

use Yii;
use yii\widgets\InputWidget;

class CaptchaField extends InputWidget
{
    public static function widget($config = [])
    {
        return Yii::$app->captcha->createInputWidget($config);
    }
}
