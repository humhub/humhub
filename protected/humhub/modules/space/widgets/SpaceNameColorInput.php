<?php

namespace humhub\modules\space\widgets;

use humhub\components\Widget;

class SpaceNameColorInput extends Widget
{
    
    public $model;
    public $form;

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('spaceNameColorInput', [
                    'model' => $this->model,
                    'form' => $this->form
        ]);
    }
}
