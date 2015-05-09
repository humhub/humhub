<?php

class SpaceWallWidget extends HWidget
{

    public $space;

    public function run()
    {
        $this->render('spaceWall', array('space' => $this->space));
    }

}

?>