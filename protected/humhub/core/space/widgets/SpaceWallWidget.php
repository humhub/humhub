<?php

namespace humhub\core\space\widgets;

use \yii\base\Widget;

class SpaceWallWidget extends Widget
{

    public $space;

    public function run()
    {
        return $this->render('spaceWall', array('space' => $this->space));
    }

}

?>