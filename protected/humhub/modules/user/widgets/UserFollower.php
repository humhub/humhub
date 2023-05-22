<?php

namespace humhub\modules\user\widgets;

use humhub\modules\user\models\User;

/**
 * UserFollowerWidget lists all followers of the user
 *
 * @package humhub.modules_core.user.widget
 * @since 0.5
 * @author Luke
 */
class UserFollower extends \yii\base\Widget
{

    /**
     * @var User
     */
    public $user;

    public function run()
    {
        return $this->render('userFollower', [
            'followers' => $this->user->getFollowersQuery()->limit(16)->all(),
            'following' => $this->user->getFollowingQuery(User::find())->limit(16)->all(),
        ]);
    }

}

?>
