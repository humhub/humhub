<?php

namespace humhub\modules\space\widgets;

use humhub\components\Widget;

class SpaceNameColorInput extends Widget
{
    public $model;
    public $form;
    /**
     * If set to true, the name input will be focused automatically.
     */
    public bool $focus = false;

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('spaceNameColorInput', [
            'model' => $this->model,
            'form' => $this->form,
            'focus' => $this->focus,
        ]);
    }
}
