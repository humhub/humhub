<?php

/**
 * UserFollowerWidget lists all followers of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author Luke
 */
class UserFollowerWidget extends HWidget {

    public function run() {

        $user = Yii::app()->getController()->getUser();

        $follow = array();
        $follower = array();

        $i = 0;
        foreach ($user->followsUser as $userFollow) {
            $follow[] = $userFollow;
            $i++;
            if ($i > 20)
                break;
        }

        $i = 0;
        foreach ($user->followerUser as $userFollow) {
            $follower[] = $userFollow;
            $i++;
            if ($i > 20)
                break;
        }

        $this->render('userFollower', array(
            'follow' => $follow,
            'follower' => $follower
        ));
    }

}

?>
