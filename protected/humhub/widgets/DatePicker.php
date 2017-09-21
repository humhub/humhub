<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;
use yii\jui\DatePicker as BaseDatePicker;
use humhub\libs\Html;

/**
 * DatePicker
 *
 * @since 1.2.3
 * @author Luke
 */
class DatePicker extends BaseDatePicker
{

    public function init()
    {
        if ($this->dateFormat === null) {
            $this->dateFormat = Yii::$app->params['formatter']['defaultDateFormat'];
        }

        Html::addCssClass($this->options, 'form-control');

        parent::init();
    }

}
