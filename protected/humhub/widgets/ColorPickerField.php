<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * Adds a color picker form field for the given model.
 *
 * @author buddha
 */
class ColorPickerField extends InputWidget
{

    /**
     * @deprecated since v1.2.2 use $attribute instead
     */
    public $field;
    
    /**
     * The container id used to append the actual color picker js widget.
     * @var string 
     */
    public $container;

    /**
     * @inheritdoc
     */
    public $attribute = 'color';

    public function init()
    {
        if(!empty($this->field)) {
            $this->attribute = $this->field;
        }
    }

    public function run()
    {
        return $this->render('colorPickerField', [
                    'model' => $this->model,
                    'field' => $this->attribute,
                    'container' => $this->container,
                    'inputId' => $this->getId(true)
        ]);
    }

}
