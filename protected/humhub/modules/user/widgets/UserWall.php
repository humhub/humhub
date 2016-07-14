<?php

namespace humhub\modules\user\widgets;

class UserWall extends \yii\base\Widget
{

    public $user;

    public function run()
    {
        return $this->render('userWall', array('user' => $this->user));
    }

}

?>