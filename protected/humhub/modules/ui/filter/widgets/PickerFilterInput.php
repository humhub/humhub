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
use Yii;
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
    protected function initFromRequest()
    {
        $filters = Yii::$app->request->get($this->category);
        if (!is_array($filters) || empty($filters)) {
            return;
        }

        if ($pickerItemClass = $this->getPickerItemClass()) {
            $this->pickerOptions['selection'] = $pickerItemClass::find()
                ->where(['IN', $this->getPicker()->itemKey, $filters])
                ->all();
        } else if($pickerItems = $this->getPickerItems()) {
            $this->pickerOptions['selection'] = array_intersect($filters, array_keys($pickerItems));
        }
    }

    protected function getPicker(): BasePicker
    {
        return new $this->picker;
    }

    /**
     * @return ActiveRecord|string|null
     */
    protected function getPickerItemClass()
    {
        $picker = $this->getPicker();
        return $picker->itemClass ?: null;
    }

    /**
     * @return array|null
     */
    protected function getPickerItems()
    {
        $picker = $this->getPicker();
        return empty($picker->items) || !is_array($picker->items) ? null : $picker->items;
    }

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
