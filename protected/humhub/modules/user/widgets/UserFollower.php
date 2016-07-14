<?php

namespace humhub\modules\user\widgets;

/**
 * UserFollowerWidget lists all followers of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author Luke
 */
class UserFollower extends \yii\base\Widget
{

    public $user;

    public function run()
    {
        return $this->render('userFollower', array('user' => $this->user));
    }

}

?>
