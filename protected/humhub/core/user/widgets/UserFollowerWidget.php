<?php

/**
 * UserFollowerWidget lists all followers of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author Luke
 */
class UserFollowerWidget extends HWidget
{

    public $user;

    public function run()
    {
        $this->render('userFollower', array(
            'user' => $this->user,
        ));
    }

}

?>
