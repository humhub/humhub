<?php

namespace humhub\modules\space\widgets;

use humhub\components\Widget;

class Wall extends Widget
{

    public $space;

    public function run()
    {
        return $this->render('spaceWall', ['space' => $this->space]);
    }

}
