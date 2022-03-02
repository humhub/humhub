<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\widgets;

use humhub\components\ActiveRecord;
use humhub\modules\ui\form\widgets\BasePicker;
use humhub\modules\ui\form\widgets\DatePicker;
use Yii;
use yii\helpers\ArrayHelper;

class DatePickerFilterInput extends FilterInput
{
    /**
     * @inheritdoc
     */
    public $view = 'datePickerInput';

    /**
     * @inheritdoc
     */
    public $type = 'date-picker';

    public $datePickerOptions = [];

    public $datePicker = DatePicker::class;

    /**
     * @var string data-action-click handler of the input event
     */
    public $changeAction = 'inputChange';

    protected function getDatePicker(): DatePicker
    {
        return new $this->datePicker;
    }

    /**
     * @inheritdoc
     */
    protected function initFromRequest()
    {
        $filter = Yii::$app->request->get($this->category);
        $this->value = $filter;
    }

    /**
     * @inheritdoc
     */
    public function prepareOptions()
    {
        parent::prepareOptions();
        $this->options['data-action-change'] = $this->changeAction;
        $this->datePickerOptions['options'] = $this->options;
        $this->datePickerOptions['value'] = $this->value;
    }

    public function getWidgetOptions()
    {
        return ArrayHelper::merge(parent::getWidgetOptions(), ['datePickerClass' => $this->datePicker, 'datePickerOptions' => $this->datePickerOptions]);
    }
}
