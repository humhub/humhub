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
        $this->assertEquals("Hello UnApproved User,\n\nYour account has been activated.\n\n" .
            "Click here to login:\nhttp://localhost/index-test.php?r=user%2Fauth%2Flogin\n\n" .
            "Kind Regards\nAdmin Tester\n\n", $form->message);

        $settings = new AuthenticationSettingsForm();
        $this->assertEquals(ApproveUserForm::getDefaultApprovalMessage(), $settings->registrationApprovalMailContent);
    }

    public function testSaveDefaultInUserLanguageDoesNotOverwrite()
    {
        $this->becomeUser('Admin');
        Yii::$app->user->getIdentity()->setAttribute('language', 'de');
        Yii::$app->i18n->setUserLocale(Yii::$app->user->getIdentity());

        $this->assertEquals('Hallo {displayName},

Dein Konto wurde aktiviert.

Klicke hier um dich einzuloggen:
{loginUrl}

Mit freundlichen Grüßen
{AdminName}',
            ApproveUserForm::getDefaultApprovalMessage());

        $this->setApprovalMessage(ApproveUserForm::getDefaultApprovalMessage());

        Yii::$app->user->getIdentity()->setAttribute('language', 'en-US');
        Yii::$app->i18n->setUserLocale(Yii::$app->user->getIdentity());

        $this->testDefaultApproveMessage();
    }

    public function testOverwrittenApproveMessage()
    {
        $this->becomeUser('Admin');

        $this->setApprovalMessage('Hey {displayName} your account was approved by {AdminName}, please click {loginLink}');

        $form = new ApproveUserForm($this->unapprovedUser->id);
        $form->setApprovalDefaults();
        $this->assertEquals("Hey UnApproved User your account was approved by Admin Tester, please click <a href=\"http://localhost/index-test.php?r=user%2Fauth%2Flogin\">http://localhost/index-test.php?r=user/auth/login</a>"
            , $form->message);
    }

    public function testApprovedMessageIsSentInUserLanguage()
    {
        $this->becomeUser('Admin');

        $this->unapprovedUser->setAttribute('language', 'de');

        $form = new ApproveUserForm($this->unapprovedUser->id);
        $form->user->setAttribute('language', 'de');
        $form->setApprovalDefaults();
        $this->assertEquals("Hallo UnApproved User,

Dein Konto wurde aktiviert.

Klicke hier um dich einzuloggen:
http://localhost/index-test.php?r=user%2Fauth%2Flogin

Mit freundlichen Grüßen
Admin Tester", $form->message);
    }

    public function testDeclineMessageIsSentInUserLanguage()
    {
        $this->becomeUser('Admin');

        $this->unapprovedUser->setAttribute('language', 'de');

        $form = new ApproveUserForm($this->unapprovedUser->id);
        $form->user->setAttribute('language', 'de');
        $form->setDeclineDefaults();
        $this->assertEquals("Hallo UnApproved User,

Deine Registrierungsanfrage wurde abgelehnt.

Mit freundlichen Grüßen
Admin Tester", $form->message);
    }

    public function testDefaultDeclineMessage()
    {
        $this->becomeUser('Admin');
        $form = new ApproveUserForm($this->unapprovedUser->id);
        $form->setDeclineDefaults();
        $this->assertEquals("Hello UnApproved User,\n\n" .
            "Your account request has been declined.\n\n" .
            "Kind Regards\n" .
            "Admin Tester\n\n", $form->message);

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
