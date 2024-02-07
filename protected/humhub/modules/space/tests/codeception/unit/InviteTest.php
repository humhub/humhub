<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\space\models\Membership;
use humhub\modules\space\notifications\Invite;
use humhub\modules\space\notifications\InviteAccepted;
use humhub\modules\space\notifications\InviteDeclined;
use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\space\models\Space;

class InviteTest extends HumHubDbTestCase
{
    use Specify;

    public function testInviteAccept()
    {
        $this->becomeUser('Admin');

        // Space 1 is approval space
        $space = Space::findOne(['id' => 1]);
        $space->inviteMember(2, Yii::$app->user->id);

        $this->assertMailSent(1, 'Approval notification admin mail');
        $this->assertHasNotification(Invite::class, $space, Yii::$app->user->id, 'Invite Request Notification');

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
        $this->assertMailSent(2, 'Approval notification admin mail');
        $this->assertHasNotification(InviteAccepted::class, $space, 2, 'Approval Accepted Invite Notificatoin');
    }

    public function testInviteDecline()
    {
        $this->becomeUser('Admin');

        // Space 1 is approval space
        $space = Space::findOne(['id' => 1]);
        $space->inviteMember(2, Yii::$app->user->id);

        $this->assertMailSent(1, 'Approval notification admin mail');
        $this->assertHasNotification(Invite::class, $space, Yii::$app->user->id, 'Invite Request Notification');

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
        $this->assertMailSent(2, 'Approval notification admin mail');
        $this->assertHasNotification(InviteDeclined::class, $space, 2, 'Declined Invite Notificatoin');
    }

}
