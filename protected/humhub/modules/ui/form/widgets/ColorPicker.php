<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use Colors\RandomColor;
use humhub\modules\ui\form\widgets\JsInputWidget;

/**
 * Adds a color picker form field for the given model.
 *
 * @since 1.3
 * @author buddha
 */
class ColorPicker extends JsInputWidget
{
    /**
     * @deprecated since v1.2.2 use $attribute instead
     */
    public $field;

    /**
     * @var string the container id used to append the actual color picker js widget.
     */
    public $container;

    /**
     * @inheritdoc
     */
    public $attribute = 'color';

    /**
     * @var bool If set to true, a random color will be set as default
     */
    public $randomDefault = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!empty($this->field) && is_array($this->field)) {
            $this->attribute = $this->field;
        }

        if (($this->hasModel() && !$this->getValue() && $this->randomDefault)
            || !$this->isCorrectColorValue()) {
            $attr = $this->attribute;
            $this->model->$attr = RandomColor::one(['luminosity' => 'dark']);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('@ui/form/widgets/views/colorPickerField', [
            'model' => $this->model,
            'field' => $this->attribute,
            'container' => $this->container,
            'inputId' => $this->getId(true),
        ]);
    }

    private function isCorrectColorValue(): bool
    {
        return preg_match('/^#[a-f0-9]{3,6}$/i', $this->model->{$this->attribute});
    }

}
