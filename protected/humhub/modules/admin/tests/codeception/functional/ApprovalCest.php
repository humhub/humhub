<?php

namespace admin\functional;

use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\user\models\Invite;
use tests\codeception\_pages\AdminPage;
use admin\FunctionalTester;
use Yii;

class ApprovalCest
{

    public function testApproveByAdmin(FunctionalTester $I)
    {
        $I->wantTo('ensure that admins can approve users');

        $settingsManager = Yii::$app->getModule('user')->settings;
        $settingsManager->set('auth.needApproval', 1);
        $settingsManager->set('auth.anonymousRegistration', 1);
        $settingsManager->set('auth.allowGuestAccess', 0);

        $this->register($I);
        $I->amAdmin();
        $this->approveUser($I);
    }

    public function testApproveByGroupManager(FunctionalTester $I)
    {
        $I->wantTo('ensure that group manager can approve users');

        $settingsManager = Yii::$app->getModule('user')->settings;
        $settingsManager->set('auth.needApproval', 1);
        $settingsManager->set('auth.anonymousRegistration', 1);
        $settingsManager->set('auth.allowGuestAccess', 0);

        $this->register($I);

        // User1 is group manager of the User group which is the only gorup available at registration
        $I->amUser1();

        $this->approveUser($I);
    }

    public function testApproveNotAllowedByOtherGroupManager(FunctionalTester $I)
    {
        $I->wantTo('ensure that group manager can not approve users of another group');

        $settingsManager = Yii::$app->getModule('user')->settings;
        $settingsManager->set('auth.needApproval', 1);
        $settingsManager->set('auth.anonymousRegistration', 1);
        $settingsManager->set('auth.allowGuestAccess', 0);

        $this->register($I);

        // User2
        $I->amUser2();
        $I->amOnDashboard();
        $I->see('New approval requests');
        $I->click('Click here to review');
        $I->see('Pending user approvals');
        $I->dontSee('approvalTest@test.de');

        // This user was created by fixtures
        $I->see('unnapproved@example.com');

        // Try to approve the user of another group
        $I->amOnRoute('/admin/approval/approve', ['id' => 8]);
        $I->seeResponseCodeIs(404);
    }

    public function testApproveNotAllowedByNormalUser(FunctionalTester $I)
    {
        $I->wantTo('ensure that normal users have no access to the approval page');

        $settingsManager = Yii::$app->getModule('user')->settings;
        $settingsManager->set('auth.needApproval', 1);
        $settingsManager->set('auth.anonymousRegistration', 1);
        $settingsManager->set('auth.allowGuestAccess', 0);

        $this->register($I);

        // User2
        $I->amUser3();
        $I->amOnDashboard();
        $I->dontSee('New approval requests');
        $I->amOnRoute('/admin/approval');

        $I->seeResponseCodeIs(403);


        $I->amOnRoute('/admin/approval/approve', ['id' => 8]);
        $I->seeResponseCodeIs(403);
    }

    private function register(FunctionalTester $I)
    {
        $I->amOnRoute('/user/auth/login');
        $I->see('Sign up');
        $I->fillField('#register-email', 'approvalTest@test.de');
        $I->click('Register');
        $I->see('Registration successful!');

        $invte = Invite::find()->all()[0];

        $I->amOnRoute('/user/registration', ['token' => $invte->token]);
        $I->see('Account registration');
        $I->fillField(['name' => 'User[username]'], 'approvalTest');
        $I->fillField(['name' => 'Password[newPassword]'], 'approva1TestPassword');
        $I->fillField(['name' => 'Password[newPasswordConfirm]'], 'approva1TestPassword');
        $I->fillField(['name' => 'Profile[firstname]'], 'approval');
        $I->fillField(['name' => 'Profile[lastname]'], 'test');

        $I->click('Create account');

        $I->see('Your account has been successfully created!');
        $I->see('After activating your account by the administrator');
    }

    private function approveUser(FunctionalTester $I)
    {
        Yii::$app->settings->set('displayNameFormat', '{profile.firstname} {profile.lastname}');

        $I->amOnDashboard();
        $I->see('New approval requests');
        $I->click('Click here to review');
        $I->see('Pending user approvals');

        $I->see('approvalTest@test.de');
        $I->amOnRoute('/admin/approval/approve', ['id' => 8]);

        $I->see('Accept user: approval test');
        $I->click('Send & save');

        $I->logout();
        $I->amUser('approvalTest', 'approva1TestPassword');
        $I->seeElement('#wallStream');
    }

}
