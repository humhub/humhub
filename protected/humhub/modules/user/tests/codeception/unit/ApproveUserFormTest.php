<?php

namespace tests\codeception\unit;

use humhub\modules\admin\models\forms\ApproveUserForm;
use humhub\modules\admin\models\forms\AuthenticationSettingsForm;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\NotFoundHttpException;

class ApproveUserFormTest extends HumHubDbTestCase
{
    /**
     * @var User
     */
    public $unapprovedUser;

    public function _before()
    {
        Yii::$app->getModule('user')->settings->set('auth.needApproval', 1);
        $this->unapprovedUser = User::findOne(['id' => 6]);
    }

    public function testDefaultApproveMessage()
    {
        $this->becomeUser('Admin');
        $form = new ApproveUserForm($this->unapprovedUser->id);
        $form->setApprovalDefaults();
        $this->assertEquals("Hello UnApproved User,<br><br>\nYour account has been activated.<br><br>\n" .
            "Click here to login:<br>\n<a href='http://localhost/index-test.php?r=user/auth/login'>http://localhost/index-test.php?r=user/auth/login</a><br><br>\n\n" .
            "Kind Regards<br>\nAdmin Tester<br><br>", $form->message);

        $settings = new AuthenticationSettingsForm();
        $this->assertEquals(ApproveUserForm::getDefaultApprovalMessage(), $settings->registrationApprovalMailContent);

    }

    public function testOverwrittenApproveMessage()
    {
        $this->becomeUser('Admin');

        $this->setApprovalMessage('Hey {displayName} your account was approved by {AdminName}, please click {loginURL}');

        $form = new ApproveUserForm($this->unapprovedUser->id);
        $form->setApprovalDefaults();
        $this->assertEquals("Hey UnApproved User your account was approved by Admin Tester, please click http://localhost/index-test.php?r=user/auth/login"
            , $form->message);
    }

    public function testDefaultDeclineMessage()
    {
        $this->becomeUser('Admin');
        $form = new ApproveUserForm($this->unapprovedUser->id);
        $form->setDeclineDefaults();
        $this->assertEquals("Hello UnApproved User,<br><br>\n" .
            "Your account request has been declined.<br><br>\n\n" .
            "Kind Regards<br>\n" .
            "Admin Tester <br><br > ", $form->message);

        $settings = new AuthenticationSettingsForm();
        $this->assertEquals(ApproveUserForm::getDefaultDeclineMessage(), $settings->registrationDenialMailContent);
    }

    public function testOverwrittenDeclineMessage()
    {
        $this->becomeUser('Admin');

        $this->setDenialMessage('Hey {displayName} your account was declined by {AdminName}.');

        $form = new ApproveUserForm($this->unapprovedUser->id);
        $form->setDeclineDefaults();
        $this->assertEquals("Hey UnApproved User your account was declined by Admin Tester."
            , $form->message);
    }

    public function testAdminCanApproveUnapprovedUser()
    {
        $this->becomeUser('Admin');
        $form = new ApproveUserForm($this->unapprovedUser->id);
        $this->assertTrue($form->approve());
        $this->assertApproved();
    }

    public function testAdminCanDeclineUnapprovedUser()
    {
        $this->becomeUser('Admin');
        $form = new ApproveUserForm($this->unapprovedUser->id);
        $this->assertTrue($form->decline());
        $this->assertDeclined();
    }

    public function testApprovedUserCanNotBeApproved()
    {
        $this->becomeUser('Admin');
        try {
            $form = new ApproveUserForm(2);
            $form->approve();
            $this->assertFalse(true);
        } catch (NotFoundHttpException $e) {
            $this->assertTrue(true);
        }
    }

    public function testApprovedUserCanNotBeDeclined()
    {
        $this->becomeUser('Admin');
        try {
            $form = new ApproveUserForm(2);
            $form->decline();
            $this->assertFalse(true);
        } catch (NotFoundHttpException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGroupManagerCanApproveUnapprovedUser()
    {
        // User2 is group_manager of group 3, UnApproved User is member of group 3
        $this->becomeUser('User2');
        $form = new ApproveUserForm($this->unapprovedUser->id);
        $this->assertTrue($form->approve());
        $this->assertApproved();
    }

    public function testNonGroupManagerCannotApproveUnapprovedUser()
    {
        // User2 is group_manager of group 3, UnApproved User is member of group 3
        $this->becomeUser('User1');

        try {
            $form = new ApproveUserForm($this->unapprovedUser->id);
            $form->approve();
            $this->assertFalse(true);
        } catch (NotFoundHttpException $e) {
            $this->assertTrue(true);
        }

        $this->assertUnapproved();
    }

    public function testNonGroupManagerCannotDeclineUnapprovedUser()
    {
        // User2 is group_manager of group 3, UnApproved User is member of group 3
        $this->becomeUser('User1');

        try {
            $form = new ApproveUserForm($this->unapprovedUser->id);
            $form->decline();
            $this->assertFalse(true);
        } catch (NotFoundHttpException $e) {
            $this->assertTrue(true);
        }

        $this->assertUnapproved();
    }

    private function assertDeclined(User $user = null)
    {
        if (!$user) {
            $user = $this->unapprovedUser;
        }

        $this->assertMailSent(1);
        $this->assertEqualsLastEmailSubject('Account Request for \'UnApproved User\' has been declined.');
        $this->assertEqualsLastEmailTo($user->email);
        $this->assertNull(User::findOne(['id' => $this->unapprovedUser->id]));
    }

    private function assertUnapproved(User $user = null)
    {
        if (!$user) {
            $user = $this->unapprovedUser;
        }

        $user = User::findOne(['id' => $user->id]);
        $this->assertEquals(User::STATUS_NEED_APPROVAL, $user->status);
    }

    private function assertApproved(User $user = null)
    {
        if (!$user) {
            $user = $this->unapprovedUser;
        }

        $this->assertMailSent(1);
        $this->assertEqualsLastEmailSubject('Account Request for \'UnApproved User\' has been approved.');
        $this->assertEqualsLastEmailTo($user->email);
        $user = User::findOne(['id' => $user->id]);
        $this->assertEquals(User::STATUS_ENABLED, $user->status);
    }

    private function setApprovalMessage($message)
    {
        $authSettings = new AuthenticationSettingsForm();
        $authSettings->registrationApprovalMailContent = $message;
        $this->assertTrue($authSettings->save());
    }

    private function setDenialMessage($message)
    {
        $authSettings = new AuthenticationSettingsForm();
        $authSettings->registrationDenialMailContent = $message;
        $this->assertTrue($authSettings->save());
    }

}
