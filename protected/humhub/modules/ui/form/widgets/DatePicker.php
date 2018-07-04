<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use Yii;
use yii\jui\DatePicker as BaseDatePicker;
use humhub\libs\Html;

/**
 * DatePicker form field widget
 *
 * @since 1.3.0
 * @inheritdoc
 * @package humhub\modules\ui\form\widgets
 */
class DatePicker extends BaseDatePicker
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->dateFormat === null) {
            $this->dateFormat = Yii::$app->formatter->dateInputFormat;
        }

        Html::addCssClass($this->options, 'form-control');

        parent::init();
    }

}
