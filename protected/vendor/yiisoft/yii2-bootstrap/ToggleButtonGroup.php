<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;

/**
 * ToggleButtonGroup allows rendering form inputs Checkbox/Radio toggle button groups.
 *
 * You can use this widget in an [[yii\bootstrap\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'item_id')->widget(\yii\bootstrap\ToggleButtonGroup::classname(), [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * @see http://getbootstrap.com/javascript/#buttons-checkbox-radio
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.6
 */
class ToggleButtonGroup extends InputWidget
{
    /**
     * @var string input type, can be:
     * - 'checkbox'
     * - 'radio'
     */
    public $type;
    /**
     * @var array the data item used to generate the checkboxes.
     * The array values are the labels, while the array keys are the corresponding checkbox or radio values.
     */
    public $items = [];
    /**
     * @var array, the HTML attributes for the label (button) tag.
     * @see Html::checkbox()
     * @see Html::radio()
     */
    public $labelOptions = [];
    /**
     * @var boolean whether the items labels should be HTML-encoded.
     */
    public $encodeLabels = true;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerPlugin('button');
        Html::addCssClass($this->options, 'btn-group');
        $this->options['data-toggle'] = 'buttons';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!isset($this->options['item'])) {
            $this->options['item'] = [$this, 'renderItem'];
        }
        switch ($this->type) {
            case 'checkbox':
                return Html::activeCheckboxList($this->model, $this->attribute, $this->items, $this->options);
            case 'radio':
                return Html::activeRadioList($this->model, $this->attribute, $this->items, $this->options);
            default:
                throw new InvalidConfigException("Unsupported type '{$this->type}'");
        }
    }

    /**
     * Default callback for checkbox/radio list item rendering.
     * @param integer $index item index.
     * @param string $label item label.
     * @param string $name input name.
     * @param boolean $checked whether value is checked or not.
     * @param string $value input value.
     * @return string generated HTML.
     * @see Html::checkbox()
     * @see Html::radio()
     */
    public function renderItem($index, $label, $name, $checked, $value)
    {
        $labelOptions = $this->labelOptions;
        Html::addCssClass($labelOptions, 'btn');
        if ($checked) {
            Html::addCssClass($labelOptions, 'active');
        }
        $type = $this->type;
        if ($this->encodeLabels) {
            $label = Html::encode($label);
        }
        return Html::$type($name, $checked, ['label' => $label, 'labelOptions' => $labelOptions, 'value' => $value]);
    }
}