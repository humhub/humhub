<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\notifications\SpaceApprovalAcceptedNotification;
use humhub\modules\space\notifications\SpaceApprovalDeclinedNotification;
use humhub\modules\space\notifications\SpaceApprovalRequestNotification;
use humhub\modules\space\notifications\SpaceRolesChangedNotification;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class MembershipTest extends HumHubDbTestCase
{
    public function testJoinPolicityApprovalApprove()
    {
        $this->becomeUser('User1');

        $user1 = Yii::$app->user->getIdentity();

        // Request Membership for Space 1 (approval join policity)
        $space = Space::findOne(['id' => 1]);
        $space->requestMembership(Yii::$app->user->id, 'Let me in!');

        // Check approval mails are send and notification
        $this->assertSentEmail(1); // Approval notification admin mail
        $this->assertHasNotification(
            SpaceApprovalRequestNotification::class,
            $space,
            Yii::$app->user->id,
            1,
            'Approval Request Notification',
        );
        // only the space admin, not the member (user 3) of space 1
        $this->assertEqualsNotificationCount(1, SpaceApprovalRequestNotification::class, $space);

        $notification = NotificationManager::load(Notification::findOne(['class' => SpaceApprovalRequestNotification::class, 'user_id' => 1]));
        $this->assertSame(['message' => 'Let me in!'], $notification->payload);
        $this->assertSame('Let me in!', $notification->getMailBody());
        $this->assertSame($space->createUrl('/space/manage/member/pending-approvals'), $notification->getUrl());
        $this->assertSame($user1->displayName . ' requests membership for the space Space 1', $notification->getMailSubject());

        // check cached version
        $membership = Membership::findMembership(1, Yii::$app->user->id);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, Membership::STATUS_APPLICANT);

        // check uncached version
        $membership = Membership::findOne(['space_id' => 1, 'user_id' => Yii::$app->user->id]);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, Membership::STATUS_APPLICANT);

        $this->becomeUser('Admin');

        $space->addMember(2);
        $this->assertSentEmail(2); //Approval notification admin mail
        $this->assertHasNoNotification(SpaceApprovalRequestNotification::class, $space, 2, null, 'The approved request is removed');
        $this->assertHasNotification(
            SpaceApprovalAcceptedNotification::class,
            $space,
            1,
            2,
            'Approval Accepted Notification',
        );

        $memberships = Membership::findByUser($user1)->all();
        $this->assertNotEmpty($memberships, 'get all memberships of user query.');
        $match = null;

        foreach ($memberships as $membership) {
            if ($membership->user_id == $user1->id) {
                $match = $membership;
            }
        }

        $this->assertNotNull($match);
    }

    public function testJoinPolicityApprovalDecline()
    {
        $this->becomeUser('User1');

        // Space 1 is approval space
        $space = Space::findOne(['id' => 1]);
        $space->requestMembership(Yii::$app->user->id, 'Let me in!');

        $this->assertSentEmail(1); // Approval notification admin mail
        $this->assertHasNotification(
            SpaceApprovalRequestNotification::class,
            $space,
            Yii::$app->user->id,
            'Approval Request Notification',
        );

        // check cached version
        $membership = Membership::findMembership(1, Yii::$app->user->id);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, Membership::STATUS_APPLICANT);

        // check uncached version
        $membership = Membership::findOne(['space_id' => 1, 'user_id' => Yii::$app->user->id]);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, Membership::STATUS_APPLICANT);

        $this->becomeUser('Admin');

        $space->removeMember(2);
        $this->assertSentEmail(2); // Rejection notification admin mail
        $this->assertHasNoNotification(SpaceApprovalRequestNotification::class, $space, 2, null, 'The declined request is removed');
        $this->assertHasNotification(
            SpaceApprovalDeclinedNotification::class,
            $space,
            1,
            2,
            'Approval Declined Notification',
        );
    }

    public function testChangeRoleMembership()
    {
        $membership = Membership::findMembership(3, 2);
        $membership->group_id = Space::USERGROUP_MODERATOR;
        $this->assertTrue($membership->save());

        NotificationManager::dispatch(
            SpaceRolesChangedNotification::class,
            $membership->user,
            $membership,
            User::findOne(['id' => 1]),
        );

        $this->assertSentEmail(1);
        $this->assertHasNotification(SpaceRolesChangedNotification::class, $membership, 1, 2);

        $record = Notification::findOne(['class' => SpaceRolesChangedNotification::class, 'user_id' => 2]);
        $this->assertNull($record->contentcontainer_id);

        $notification = NotificationManager::load($record);
        $space = Space::findOne(['id' => 3]);
        $this->assertSame($space->getUrl(), $notification->getUrl());
        $this->assertSame(
            '<strong>' . User::findOne(['id' => 1])->displayName . '</strong> changed your role to <strong>Moderators</strong> in the space <strong>Space 3</strong>.',
            $notification->asWeb(),
        );
        $this->assertSame(
            User::findOne(['id' => 1])->displayName . ' changed your role to Moderators in the space Space 3.',
            $notification->getMailSubject(),
        );
    }

    public function testInvitingAnApplicantWithoutACurrentUserNamesTheInviterAsApprover()
    {
        $this->becomeUser('User1');
        $space = Space::findOne(['id' => 1]);
        $space->requestMembership(Yii::$app->user->id, 'Let me in!');

        // e.g. in AddUsersToSpaceJob, run by the queue
        $this->logout();
        $space->inviteMember(2, 1);

        $this->assertTrue($space->isMember(2));
        $this->assertHasNotification(SpaceApprovalAcceptedNotification::class, $space, 1, 2);
    }

    public function testAnEmptyRequestMessageHasNoMailBody()
    {
        $this->becomeUser('User1');

        $space = Space::findOne(['id' => 1]);
        $space->requestMembership(Yii::$app->user->id, '');

        $record = Notification::findOne(['class' => SpaceApprovalRequestNotification::class, 'user_id' => 1]);
        $notification = NotificationManager::load($record);
        $this->assertSame([], $notification->payload);
        $this->assertNull($notification->getMailBody());
    }

    public function testTheRoleOfThePayloadWinsOverTheCurrentMembershipRole()
    {
        $membership = Membership::findMembership(3, 2);
        $membership->group_id = Space::USERGROUP_MODERATOR;
        $this->assertTrue($membership->save());

        // as the member management dispatches it, then the role changes again
        NotificationManager::dispatch(SpaceRolesChangedNotification::class, $membership->user, $membership, User::findOne(['id' => 1]), [
            'dedupe' => false,
            'payload' => ['groupId' => Space::USERGROUP_ADMIN],
        ]);

        $notification = NotificationManager::load(Notification::findOne(['class' => SpaceRolesChangedNotification::class, 'user_id' => 2]));
        $this->assertStringContainsString('changed your role to Administrators in', $notification->getMailSubject());
    }

    public function testAnUnknownRoleRendersItsId()
    {
        $membership = Membership::findMembership(3, 2);

        NotificationManager::dispatch(SpaceRolesChangedNotification::class, $membership->user, $membership, User::findOne(['id' => 1]), [
            'payload' => ['groupId' => 'gone-role'],
        ]);

        $notification = NotificationManager::load(Notification::findOne(['class' => SpaceRolesChangedNotification::class, 'user_id' => 2]));
        $this->assertStringContainsString('changed your role to gone-role in', $notification->getMailSubject());
        $this->assertStringContainsString('<strong>gone-role</strong>', $notification->asWeb());
    }

    public function testRemovingTheMemberRemovesTheRolesChangedNotification()
    {
        $this->becomeUser('Admin');

        $space = Space::findOne(['id' => 3]);
        $membership = Membership::findMembership(3, 2);
        $membership->group_id = Space::USERGROUP_MODERATOR;
        $this->assertTrue($membership->save());
        NotificationManager::dispatch(SpaceRolesChangedNotification::class, $membership->user, $membership, User::findOne(['id' => 1]), ['dedupe' => false]);
        $this->assertHasNotification(SpaceRolesChangedNotification::class, $membership, 1, 2);

        $this->assertTrue($space->removeMember(2));

        $this->assertSame(0, (int)Notification::find()->where(['class' => SpaceRolesChangedNotification::class, 'user_id' => 2])->count());
    }

    public function testASecondRoleChangeNotifiesAgain()
    {
        $membership = Membership::findMembership(3, 2);
        $admin = User::findOne(['id' => 1]);

        foreach ([Space::USERGROUP_MODERATOR, Space::USERGROUP_ADMIN] as $groupId) {
            $membership->group_id = $groupId;
            $this->assertTrue($membership->save());
            // as the member management dispatches it
            NotificationManager::dispatch(SpaceRolesChangedNotification::class, $membership->user, $membership, $admin, ['dedupe' => false]);
        }

        $this->assertEqualsNotificationCount(2, SpaceRolesChangedNotification::class, $membership, 1, 2);
    }
}
