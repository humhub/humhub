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
        // content container, authored by the follower. Columns are qualified
        // because ActiveQueryActivity left-joins the content and user tables,
        // which share these column names.
        $activity = Activity::findOne([
            'activity.class' => FollowActivity::class,
            'activity.contentcontainer_id' => $user->contentcontainer_id,
            'activity.created_by' => Yii::$app->user->id,
        ]);
        $this->assertNotNull($activity);

        // Unfollowing must not raise (regression: Follow::beforeDelete used to
        // query the dropped activity.object_model/object_id columns) and must
        // clean up both the follow record and its activity.
        $this->assertTrue($user->unfollow());

        $this->assertNull(Follow::findOne(['object_model' => User::class, 'object_id' => 1, 'user_id' => 2]));
        $this->assertNull(Activity::findOne(['activity.id' => $activity->id]));
    }

    public function testFollowSubclassWithCustomObjectModel()
    {
        $this->becomeUser('User1');

        // Subclass storing its records under the parent class (like a form model extending a content record)
        $userClass = new class() extends User {
            public static function getObjectModel(): string
            {
                return User::class;
            }
        };
        $user = $userClass::findOne(['id' => 1]);

        $this->assertTrue($user->follow(2));
        // Following again (e.g. on a second save within the same request) must not try to insert a duplicate
        $this->assertTrue($user->follow(2));

        $this->assertEquals(1, Follow::find()->where(['object_id' => 1, 'user_id' => 2])->count());
        $this->assertNotNull(Follow::findOne(['object_model' => User::class, 'object_id' => 1, 'user_id' => 2]));
        $this->assertTrue($user->isFollowedByUser(2));
        $this->assertEquals(1, $user->getFollowersQuery()->count());

        $this->assertTrue($user->unfollow(2));
        $this->assertFalse($user->isFollowedByUser(2));
        $this->assertNull(Follow::findOne(['object_model' => User::class, 'object_id' => 1, 'user_id' => 2]));
    }
}
