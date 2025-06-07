<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use humhub\modules\ui\icon\widgets\Icon;
use Yii;
use yii\helpers\Html;

/**
 * TimePicker form field widget
 *
 * @inheritdoc
 * @package humhub\modules\ui\form\widgets
 */
class TimePicker extends \kartik\time\TimePicker
{
    public $bsVersion = 5;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->attribute && $this->hasModel()) {
            $attributeName = Html::getAttributeName($this->attribute);
            if ($attributeName && $this->model->hasErrors($attributeName)) {
                Html::addCssClass($this->containerOptions, 'is-invalid');
            }
        }

        // Use FontAwesome 5 with current FontAwesome 3 library for the time picker
        $this->view->registerCss('
            .bootstrap-timepicker .fas {
                display: inline-block;
                font: normal normal normal 14px / 1 FontAwesome;
                font-size: inherit;
                text-rendering: auto;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
        ');

        if (!isset($this->pluginOptions['showMeridian'])) {
            $this->pluginOptions['showMeridian'] = Yii::$app->formatter->isShowMeridiem();
        }

        if (!isset($this->pluginOptions['defaultTime'])) {
            $this->pluginOptions['defaultTime'] = ($this->pluginOptions['showMeridian']) ? '10:00 AM' : '10:00';
        }
    }

    protected function renderInput()
    {
        // Replace FontAwesome 5 icon with FontAwesome 3
        $this->addon = Icon::get('clock-o');
        return str_replace('<i class="far fa-clock"></i>', '', parent::renderInput());
    }
}
