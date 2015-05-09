<?php

class UserWallWidget extends HWidget
{

    public $user;

    public function run()
    {
        $this->render('userWall', array('user' => $this->user));
    }

}

?>