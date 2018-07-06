<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\widgets;

use humhub\modules\ui\form\widgets\BasePicker;
use yii\helpers\ArrayHelper;

class PickerFilterInput extends FilterInput
{
    /**
     * @inheritdoc
     */
    public $view = 'pickerInput';

    /**
     * @inheritdoc
     */
    public $type = 'picker';

    public $pickerOptions = [];

    public $picker = BasePicker::class;

    /**
     * @var string data-action-click handler of the input event
     */
    public $changeAction = 'parent.inputChange';

    /**
     * @inheritdoc
     */
    public function prepareOptions()
    {
        parent::prepareOptions();

        $this->options['data-action-change'] = $this->changeAction;
        $this->pickerOptions['options'] = $this->options;

    }

    public function getWidgetOptions()
    {

        return ArrayHelper::merge(parent::getWidgetOptions(), ['pickerClass' => $this->picker, 'pickerOptions' => $this->pickerOptions]);
    }
}
