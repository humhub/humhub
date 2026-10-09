<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\notifications\SpaceInviteAcceptedNotification;
use humhub\modules\space\notifications\SpaceInviteDeclinedNotification;
use humhub\modules\space\notifications\SpaceInviteNotification;
use humhub\modules\space\notifications\SpaceInviteRevokedNotification;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class InviteTest extends HumHubDbTestCase
{
    public function testInviteAccept()
    {
        $this->becomeUser('Admin');

        // Space 1 is approval space
        $space = Space::findOne(['id' => 1]);
        $space->inviteMember(2, Yii::$app->user->id);

        $this->assertMailSent(1);
        $this->assertHasNotification(SpaceInviteNotification::class, $space, Yii::$app->user->id, 2, 'Invite Request Notification');

        $record = Notification::findOne(['class' => SpaceInviteNotification::class, 'user_id' => 2]);
        $this->assertSame((int)$space->contentcontainer_id, (int)$record->contentcontainer_id);
        $this->assertNull($record->content_id);
        $this->assertNull($record->source_record_id);

        $notification = NotificationManager::load($record);
        // the about page, which the invited user may open
        $this->assertSame($space->createUrl('/space/space/about'), $notification->getUrl());
        $this->assertSame(
            '<strong>' . Yii::$app->user->identity->displayName . '</strong> invited you to the space <strong>Space 1</strong>',
            $notification->asWeb(),
        );
        $this->assertSame([$notification->getEntryUrl()], array_column($notification->getActions(), 'url'));

        // check cached version
        $membership = Membership::findMembership(1, 2);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, Membership::STATUS_INVITED);

        // check uncached version
        $membership = Membership::findOne(['space_id' => 1, 'user_id' => 2]);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, Membership::STATUS_INVITED);

        $this->becomeUser('User1');

        $space->addMember(2);
        $this->assertMailSent(2);
        $this->assertHasNoNotification(SpaceInviteNotification::class, $space, null, 2, 'The accepted invite is removed');
        $this->assertHasNotification(SpaceInviteAcceptedNotification::class, $space, 2, 1, 'Accepted Invite Notification');
    }

    public function testInviteToAPrivateSpaceNotifiesTheInvitee()
    {
        $this->becomeUser('Admin');

        // Space 5 is private (invisible), user 3 is no member of it
        $space = Space::findOne(['id' => 5]);
        $this->assertSame(Space::VISIBILITY_NONE, (int)$space->visibility);
        $space->inviteMember(3, Yii::$app->user->id);

        $this->assertHasNotification(SpaceInviteNotification::class, $space, Yii::$app->user->id, 3);
    }

    public function testRevokingAnInviteNotifiesTheInvitee()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(['id' => 1]);
        $space->inviteMember(2, Yii::$app->user->id);
        $this->assertHasNotification(SpaceInviteNotification::class, $space, null, 2);

        // the admin cancels the invitation
        $this->assertTrue($space->removeMember(2));

        $this->assertHasNoNotification(SpaceInviteNotification::class, $space, null, 2, 'The revoked invite is removed');
        $this->assertHasNotification(SpaceInviteRevokedNotification::class, $space, Yii::$app->user->id, 2);
        $this->assertHasNoNotification(SpaceInviteDeclinedNotification::class, $space);
    }

    public function testReInviteReplacesTheInviteNotification()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(['id' => 1]);
        $space->inviteMember(2, Yii::$app->user->id);
        $space->inviteMember(2, Yii::$app->user->id);

        $this->assertEqualsNotificationCount(1, SpaceInviteNotification::class, $space, null, 2);
    }

    public function testInviteDecline()
    {
        $this->becomeUser('Admin');

        // Space 1 is approval space
        $space = Space::findOne(['id' => 1]);
        $space->inviteMember(2, Yii::$app->user->id);

        $this->assertMailSent(1);
        $this->assertHasNotification(SpaceInviteNotification::class, $space, Yii::$app->user->id, 2, 'Invite Request Notification');

        // check cached version
        $membership = Membership::findMembership(1, 2);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, Membership::STATUS_INVITED);

        // check uncached version
        $membership = Membership::findOne(['space_id' => 1, 'user_id' => 2]);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, Membership::STATUS_INVITED);

        $this->becomeUser('User1');

        $space->removeMember();
        $this->assertMailSent(2);
        $this->assertHasNoNotification(SpaceInviteNotification::class, $space, null, 2, 'The declined invite is removed');
        $this->assertHasNotification(SpaceInviteDeclinedNotification::class, $space, 2, 1, 'Declined Invite Notification');
        $this->assertHasNoNotification(SpaceInviteAcceptedNotification::class, $space);
    }
}
