<?php

namespace tests\codeception\unit\modules\friendship;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\friendship\notifications\FriendshipApprovedNotification;
use humhub\modules\friendship\notifications\FriendshipDeclinedNotification;
use humhub\modules\friendship\notifications\FriendshipRequestNotification;
use humhub\modules\notification\components\NotificationAction;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;

class FriendshipTest extends HumHubDbTestCase
{
    use Specify;

    /**
     * Create a Mock Content class and assign a notify user save it and check if an email was sent and test wallout.
     */
    public function testAcceptFriendShip()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);

        $this->becomeUser('User2');
        $friendUser = User::findOne(['id' => 2]);

        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user->getIdentity()), Friendship::STATE_NONE, 'Check Status before sent');

        // Request Friendship
        $this->assertTrue(Friendship::add(Yii::$app->user->getIdentity(), $friendUser));
        $this->assertMailSent(1);

        $fiendship = Friendship::findOne(['user_id' => Yii::$app->user->id, 'friend_user_id' => 2]);
        $this->assertNotNull($fiendship, 'Friendship model persisted.');
        $this->assertEquals(Friendship::getStateForUser(Yii::$app->user->getIdentity(), $friendUser), Friendship::STATE_REQUEST_SENT, 'Check Sent Status');
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user->getIdentity()), Friendship::STATE_REQUEST_RECEIVED, 'Check Received Status');

        // Accept friendship
        $this->assertTrue(Friendship::add($friendUser, Yii::$app->user->getIdentity()));
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user->getIdentity()), Friendship::STATE_FRIENDS, 'Check Friend Status');
        $this->assertMailSent(2);
    }

    public function testDeclineFriendShip()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);

        $this->becomeUser('User2');
        $friendUser = User::findOne(['id' => 2]);

        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user->getIdentity()), Friendship::STATE_NONE, 'Check Status before sent');

        // Request Friendship
        $this->assertTrue(Friendship::add(Yii::$app->user->getIdentity(), $friendUser));
        $this->assertMailSent(1);

        $fiendship = Friendship::findOne(['user_id' => Yii::$app->user->id, 'friend_user_id' => 2]);
        $this->assertNotNull($fiendship, 'Friendship model persisted.');
        $this->assertEquals(Friendship::getStateForUser(Yii::$app->user->getIdentity(), $friendUser), Friendship::STATE_REQUEST_SENT, 'Check Sent Status');
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user->getIdentity()), Friendship::STATE_REQUEST_RECEIVED, 'Check Received Status');

        // Cancel request
        Friendship::cancel($friendUser, Yii::$app->user->getIdentity());
        $this->assertEquals(Friendship::getStateForUser($friendUser, Yii::$app->user->getIdentity()), Friendship::STATE_NONE, 'Check Friend Status');
        $this->assertMailSent(2);
    }

    public function testRequestApproveAndDeclineNotifications()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);

        $requester = User::findOne(['id' => 3]);
        $friend = User::findOne(['id' => 2]);

        // request: the friend is notified about the requester's friendship record
        $this->becomeUser('User2');
        $this->assertTrue(Friendship::add($requester, $friend));
        $request = Friendship::findOne(['user_id' => 3, 'friend_user_id' => 2]);
        $this->assertHasNotification(FriendshipRequestNotification::class, $request, 3, 2);

        $record = Notification::findOne(['class' => FriendshipRequestNotification::class, 'user_id' => 2]);
        $this->assertNull($record->seen_at);
        $this->assertSame(1, (int)$record->listed);
        $this->assertSame((int)$record->id, (int)$record->grouping_key);

        $notification = NotificationManager::load($record);
        $this->assertStringEndsWith('sent you a friend request.', $notification->asWeb());
        $this->assertSame($requester->getUrl(), $notification->getUrl());
        $this->assertEquals([
            new NotificationAction('View online', $notification->getEntryUrl()),
        ], $notification->getActions());

        // approve: the request notification is gone, the requester is notified
        $this->becomeUser('User1');
        $this->assertTrue(Friendship::add($friend, $requester));
        $this->assertSame(0, (int)Notification::find()->where(['class' => FriendshipRequestNotification::class, 'user_id' => 2])->count());

        $approval = Friendship::findOne(['user_id' => 2, 'friend_user_id' => 3]);
        $this->assertHasNotification(FriendshipApprovedNotification::class, $approval, 2, 3);
        $this->assertSame(NotificationPriority::Normal, FriendshipApprovedNotification::priority());
        $approved = NotificationManager::load(Notification::findOne(['class' => FriendshipApprovedNotification::class, 'user_id' => 3]));
        $this->assertStringEndsWith('accepted your friend request.', $approved->asWeb());
        $this->assertSame($friend->getUrl(), $approved->getUrl());

        // decline: a new request of the requester, declined by the friend
        Friendship::cancel($friend, $requester);
        $this->becomeUser('User2');
        $this->assertTrue(Friendship::add($requester, $friend));
        $this->becomeUser('User1');
        Friendship::cancel($friend, $requester);

        $declined = Notification::findOne(['class' => FriendshipDeclinedNotification::class, 'user_id' => 3, 'originator_id' => 2]);
        $this->assertNotNull($declined);
        $this->assertNull($declined->source_record_id);
        $declinedNotification = NotificationManager::load($declined);
        $this->assertStringEndsWith('declined your friend request.', $declinedNotification->asWeb());
        $this->assertSame($friend->getUrl(), $declinedNotification->getUrl());

        // the declined request's notification went with its friendship record
        $this->assertSame(0, (int)Notification::find()->where(['class' => FriendshipRequestNotification::class, 'user_id' => 2])->count());
    }

    public function testWithdrawnRequestRemovesTheNotification()
    {
        Yii::$app->getModule('friendship')->settings->set('enable', 1);

        $requester = User::findOne(['id' => 3]);
        $friend = User::findOne(['id' => 2]);

        $this->becomeUser('User2');
        $this->assertTrue(Friendship::add($requester, $friend));
        $this->assertSame(1, (int)Notification::find()->where(['class' => FriendshipRequestNotification::class, 'user_id' => 2])->count());

        // withdrawn by the requester: no reverse row, so no declined notification either
        Friendship::cancel($requester, $friend);
        $this->assertSame(0, (int)Notification::find()->where(['class' => FriendshipRequestNotification::class, 'user_id' => 2])->count());
        $this->assertSame(0, (int)Notification::find()->where(['class' => FriendshipDeclinedNotification::class])->count());
    }
}
