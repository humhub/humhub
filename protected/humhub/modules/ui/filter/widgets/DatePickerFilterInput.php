<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\widgets;

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

    /**
     * @var string Class of the used DatePicker widget
     */
    private $datePickerWidgetClass = DatePicker::class;

    /**
     * @var array Options for the DatePicker widget
     */
    private $datePickerOptions = [];

    /**
     * @var string data-action-click handler of the input event
     */
    public $changeAction = 'inputChange';

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
        $this->datePickerOptions['dateFormat'] = 'php:Y-m-d';
    }

    /**
     * @inheritdoc
     */
    protected function getWidgetOptions()
    {
        return ArrayHelper::merge(
            parent::getWidgetOptions(),
            ['datePickerClass' => $this->datePickerWidgetClass, 'datePickerOptions' => $this->datePickerOptions]);
    }
}
