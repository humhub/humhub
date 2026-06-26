<?php

namespace tests\codeception\unit;

use humhub\modules\activity\models\Activity;
use humhub\modules\user\activities\FollowActivity;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\Followed;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class FollowTest extends HumHubDbTestCase
{
    public function testFollowUser()
    {
        $this->becomeUser('User1');

        $user = User::findOne(['id' => 1]);
        $this->assertTrue($user->follow());

        $follow = Follow::findOne(['object_model' => User::class, 'object_id' => 1, 'user_id' => 2]);

        $this->assertNotNull($follow);
        $this->assertMailSent(1);
        $this->assertHasNotification(Followed::class, $follow, Yii::$app->user->id, 'Approval Request Notification');
    }

    public function testUnfollowUserRemovesActivity()
    {
        $this->becomeUser('User1');

        $user = User::findOne(['id' => 1]);
        $this->assertTrue($user->follow());

        // Following a user creates a FollowActivity on the followed user's
        // content container, authored by the follower.
        $activity = Activity::findOne([
            'class' => FollowActivity::class,
            'contentcontainer_id' => $user->contentcontainer_id,
            'created_by' => Yii::$app->user->id,
        ]);
        $this->assertNotNull($activity);

        // Unfollowing must not raise (regression: Follow::beforeDelete used to
        // query the dropped activity.object_model/object_id columns) and must
        // clean up both the follow record and its activity.
        $this->assertTrue($user->unfollow());

        $this->assertNull(Follow::findOne(['object_model' => User::class, 'object_id' => 1, 'user_id' => 2]));
        $this->assertNull(Activity::findOne(['id' => $activity->id]));
    }
}
