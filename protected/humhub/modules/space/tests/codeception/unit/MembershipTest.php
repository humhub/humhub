<?php

namespace tests\codeception\unit\modules\space;

use Yii;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;

class MembershipTest extends HumHubDbTestCase
{

    use Specify;

    public function testJoinPolicityApprovalApprove()
    {
        $this->becomeUser('User1');

        $user1 = Yii::$app->user->getIdentity();
        
        // Request Membership for Space 1 (approval join policity)
        $space = Space::findOne(['id' => 1]);
        $space->requestMembership(Yii::$app->user->id, 'Let me in!');

        // Check approval mails are send and notification
        $this->assertMailSent(1, 'Approval notification admin mail');
        $this->assertHasNotification(\humhub\modules\space\notifications\ApprovalRequest::class, $space, Yii::$app->user->id, 'Approval Request Notification');

        $membership = Membership::findOne(['space_id' => 1, 'user_id' => Yii::$app->user->id]);
        $this->assertNotNull($membership);
        $this->assertEquals($membership->status, \humhub\modules\space\models\Membership::STATUS_APPLICANT);

        $this->becomeUser('Admin');

        $space->addMember(2);
        $this->assertMailSent(2, 'Approval notification admin mail');
        $this->assertHasNotification(\humhub\modules\space\notifications\ApprovalRequestAccepted::class, $space, 1, 'Approval Accepted Notification');
        
        $memberships = Membership::findByUser($user1)->all();
        $this->assertNotEmpty($memberships, 'get all memberships of user query.');
        $match = null;

        foreach($memberships as $membership) {
            if($membership->user_id == $user1->id) {
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
