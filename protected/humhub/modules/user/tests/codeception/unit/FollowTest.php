<?php

namespace tests\codeception\unit;

use humhub\modules\activity\models\Activity;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Space;
use humhub\modules\user\activities\FollowActivity;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\FollowedNotification;
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
        // new followers reach the user in the web list only, unless switched on for e-mail
        $this->assertMailSent(0);
        $this->assertHasNotification(FollowedNotification::class, $follow, Yii::$app->user->id, 1);

        $record = Notification::findOne(['class' => FollowedNotification::class, 'user_id' => 1]);
        $this->assertNull($record->seen_at);
        $this->assertSame(1, (int)$record->listed);
        $this->assertSame((int)$record->id, (int)$record->grouping_key);

        $notification = NotificationManager::load($record);
        $this->assertStringEndsWith('is now following you.', $notification->asWeb());
        $this->assertSame(User::findOne(['id' => 2])->getUrl(), $notification->getUrl());
        $this->assertNull($notification->getPreviewRecord());
    }

    public function testFollowOfASpaceCreatesNoNotification()
    {
        $this->becomeUser('User1');

        $space = Space::findOne(['id' => 1]);
        $this->assertTrue($space->follow(null, true));

        $this->assertSame(0, (int)Notification::find()->where(['class' => FollowedNotification::class])->count());
    }

    public function testFollowsGroup()
    {
        $admin = User::findOne(['id' => 1]);

        $this->becomeUser('User1');
        $this->assertTrue($admin->follow());
        $this->becomeUser('User2');
        $this->assertTrue($admin->follow());

        $rows = Notification::find()->forUser($admin)->grouped()->andWhere(['notification.class' => FollowedNotification::class])->all();
        $this->assertCount(1, $rows);

        $notification = NotificationManager::load($rows[0]);
        $this->assertSame(2, $notification->groupCount);
        $web = $notification->asWeb();
        $this->assertStringContainsString(User::findOne(['id' => 2])->displayName, $web);
        $this->assertStringContainsString(User::findOne(['id' => 3])->displayName, $web);
        $this->assertStringEndsWith('are now following you.', $web);
    }

    public function testAGroupOfOneFollowerUsesTheSingularSentence()
    {
        // A group that names one person only arises when the other grouped followers are not
        // visible to the recipient; simulated here by two rows of the same originator
        $ids = [];
        foreach ([1, 2] as $i) {
            $record = new Notification(['class' => FollowedNotification::class, 'user_id' => 1, 'originator_id' => 2]);
            $this->assertTrue($record->save());
            $ids[] = $record->id;
        }
        Notification::updateAll(['grouping_key' => end($ids)], ['id' => $ids]);

        $row = Notification::find()->forUser(1)->grouped()->andWhere(['notification.class' => FollowedNotification::class])->one();
        $notification = NotificationManager::load($row);
        $this->assertSame(2, $notification->groupCount);
        $this->assertSame('<strong>' . User::findOne(['id' => 2])->displayName . '</strong> is now following you.', $notification->asWeb());
    }

    public function testUnfollowDeletesTheNotification()
    {
        $admin = User::findOne(['id' => 1]);

        $this->becomeUser('User1');
        $this->assertTrue($admin->follow());
        $follow = Follow::findOne(['object_model' => User::class, 'object_id' => 1, 'user_id' => 2]);
        $this->assertHasNotification(FollowedNotification::class, $follow, 2, 1);

        $this->assertTrue($admin->unfollow());
        $this->assertSame(0, (int)Notification::find()->where(['class' => FollowedNotification::class, 'user_id' => 1])->count());
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
}
