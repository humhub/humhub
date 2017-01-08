<?php

namespace tests\codeception\unit\modules\space;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\space\models\Space;

class ApprovalTest extends HumHubDbTestCase
{

    use Specify;

    public function testJoinPolicityApprovalApprove()
    {
        $this->becomeUser('User1');

        // Space 1 is approval space
        $space = Space::findOne(['id' => 1]);
        $space->requestMembership(Yii::$app->user->id, 'Let me in!');

        $this->assertMailSent(1, 'Approval notification admin mail');
        $this->assertHasNotification(\humhub\modules\space\notifications\ApprovalRequest::class, $space, Yii::$app->user->id, 'Approval Request Notification');

        $membership = \humhub\modules\space\models\Membership::findOne(['space_id' => 1, 'user_id' => Yii::$app->user->id]);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, \humhub\modules\space\models\Membership::STATUS_APPLICANT);

        $this->becomeUser('Admin');

        $space->addMember(2);
        $this->assertMailSent(2, 'Approval notification admin mail');
        $this->assertHasNotification(\humhub\modules\space\notifications\ApprovalRequestAccepted::class, $space, 1, 'Approval Accepted Notification');
    }

    public function testJoinPolicityApprovalDecline()
    {
        $this->becomeUser('User1');

        // Space 1 is approval space
        $space = Space::findOne(['id' => 1]);
        $space->requestMembership(Yii::$app->user->id, 'Let me in!');

        $this->assertMailSent(1, 'Approval notification admin mail');
        $this->assertHasNotification(\humhub\modules\space\notifications\ApprovalRequest::class, $space, Yii::$app->user->id, 'Approval Request Notification');

        $membership = \humhub\modules\space\models\Membership::findOne(['space_id' => 1, 'user_id' => Yii::$app->user->id]);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, \humhub\modules\space\models\Membership::STATUS_APPLICANT);

        $this->becomeUser('Admin');

        $space->removeMember(2);
        $this->assertMailSent(2, 'Rejection notification admin mail');
        $this->assertHasNotification(\humhub\modules\space\notifications\ApprovalRequestDeclined::class, $space, 1, 'Approval Accepted Notification');
    }

}
