<?php

namespace humhub\modules\space\widgets;


use humhub\components\Widget;

/**
 * Created by PhpStorm.
 * User: Struppi
 * Date: 17.12.13
 * Time: 12:49
 */
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

?>