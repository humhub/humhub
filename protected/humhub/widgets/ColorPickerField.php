<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * Adds a color picker form field for the given model.
 *
 * @author buddha
 */
class ColorPickerField extends \humhub\components\Widget
{

    /**
     * The model instance
     * @var \yii\base\Model
     */
    public $model;

    /**
     * The color field of the model
     * @var string
     */
    public $field;

    /**
     * The container id used to append the actual color picker js widget.
     * @var string
     */
    public $container;

    public function run()
    {
        $ts = time();
        $inputId = $ts . 'space-color-picker-edit' . $this->field;

        return $this->render('colorPickerField', [
            'model' => $this->model,
            'field' => $this->field,
            'container' => $this->container,
            'inputId' => $inputId
        ]);
    }

}
