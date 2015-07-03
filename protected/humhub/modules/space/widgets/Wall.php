<?php

namespace humhub\modules\space\widgets;

use \yii\base\Widget;

class Wall extends Widget
{

    public $space;

    public function run()
    {
        return $this->render('spaceWall', array('space' => $this->space));
    }

}

?>