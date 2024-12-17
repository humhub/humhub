<?php

use humhub\modules\space\models\forms\InviteForm;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\forms\Invite as UserInviteForm;
use humhub\modules\user\services\LinkRegistrationService;
use user\FunctionalTester;

class LinkInviteCest
{
    public function testDisabledLinkInvite(FunctionalTester $I)
    {
        $I->wantTo('ensure that invitation links are correctly disabled');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 0);

        $inviteForm = new InviteForm(['space' => Space::findOne(['name' => 'Space 2'])]);
        $I->amOnPage($inviteForm->getInviteLink());
        $I->seeResponseCodeIs(403);

        $inviteForm = new UserInviteForm();
        $inviteForm->target = LinkRegistrationService::TARGET_ADMIN;
        $I->amOnPage($inviteForm->getInviteLink());
        // The invitation by link is never disabled because admins or user managers always can send it
        $I->seeResponseCodeIs(200);

        $inviteForm = new UserInviteForm();
        $inviteForm->target = LinkRegistrationService::TARGET_PEOPLE;
        $I->amOnPage($inviteForm->getInviteLink());
        $I->seeResponseCodeIs(403);
    }

    public function testInvalidToken(FunctionalTester $I)
    {
        $I->wantTo('ensure that invite by link is without valid token');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 1);

        $I->amOnRoute('/user/registration/by-link', ['token' => 'abcd', 'spaceId' => 1]);
        $I->seeResponseCodeIs(400);
    }

    public function testValidTokenDifferentTarget(FunctionalTester $I)
    {
        $I->wantTo('ensure that invitation links are different between targets');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 1);

        $adminInviteForm = new UserInviteForm();
        $adminInviteForm->target = LinkRegistrationService::TARGET_ADMIN;
        $firstAdminInviteLink = $adminInviteForm->getInviteLink();

        $peopleInviteForm = new UserInviteForm();
        $peopleInviteForm->target = LinkRegistrationService::TARGET_PEOPLE;
        $firstPeopleInviteLink = $peopleInviteForm->getInviteLink();

        $I->amOnPage($firstAdminInviteLink);
        $I->seeResponseCodeIs(200);

        $I->amOnPage($firstPeopleInviteLink);
        $I->seeResponseCodeIs(200);

        // Reset only the link with admin target
        $secondAdminInviteLink = $adminInviteForm->getInviteLink(true);
        $I->amOnPage($firstAdminInviteLink);
        $I->seeResponseCodeIs(400); // Invalid token
        $I->amOnPage($secondAdminInviteLink);
        $I->seeResponseCodeIs(200); // The second admin token is valid now
        $I->amOnPage($firstPeopleInviteLink);
        $I->seeResponseCodeIs(200); // The first people token must be still valid

        // Reset the link with people target
        $secondPeopleInviteLink = $peopleInviteForm->getInviteLink(true);
        $I->amOnPage($secondAdminInviteLink);
        $I->seeResponseCodeIs(200); // The second admin token should be valid
        $I->amOnPage($firstPeopleInviteLink);
        $I->seeResponseCodeIs(400); // The first people token is invalid after reset
        $I->amOnPage($secondPeopleInviteLink);
        $I->seeResponseCodeIs(200); // The second people token is valid now
    }

    public function testValidTokenDifferentSpaceId(FunctionalTester $I)
    {
        $I->wantTo('ensure that invite by link is with valid token and different space ID');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 1);

        // Generate Token
        $space = Space::findOne(['name' => 'Space 2']);
        $inviteForm = new InviteForm(['space' => $space]);
        $inviteForm->getInviteLink();

        $linkRegistrationService = new LinkRegistrationService(null, $space);
        $I->amOnRoute('/user/registration/by-link', ['token' => $linkRegistrationService->getStoredToken(), 'spaceId' => $space->id]);
        $I->seeResponseCodeIs(200);

        $I->amOnRoute('/user/registration/by-link', ['token' => $linkRegistrationService->getStoredToken(), 'spaceId' => 1]);
        $I->seeResponseCodeIs(400);

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 0);
        $I->amOnRoute('/user/registration/by-link', ['token' => 'abc', 'spaceId' => 1]);
        $I->seeResponseCodeIs(403);
    }

    public function testSpaceInvite(FunctionalTester $I)
    {
        $I->wantTo('ensure that invited users become member of the space');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 1);

        $inviteForm = new InviteForm(['space' => Space::findOne(['name' => 'Space 2'])]);
        $inviteUrl = $inviteForm->getInviteLink();

        $I->amOnPage($inviteUrl);

        $I->see('registration');

        $I->fillField('#register-email', 'text@example.com');
        $I->click('Register');

        $I->see('successful');

        $messages = $I->grabSentEmails();

        if (!array_key_exists('text@example.com', $messages[0]->getTo())) {
            $I->see('text@example.com not in mails');
        }

        $token = $I->fetchInviteToken($messages[0]);

        $I->amOnRoute('/user/registration', ['token' => $token]);
        $I->see('Account registration');

        $I->fillField('User[username]', 'NewUser');
        $I->fillField('Password[newPassword]', 'NewUser123');
        $I->fillField('Password[newPasswordConfirm]', 'NewUser123');
        $I->fillField('Profile[firstname]', 'New');
        $I->fillField('Profile[lastname]', 'User');
        $I->click('#registration-form [type="submit"]');

        $I->see('Dashboard');

        $userId = \humhub\modules\user\models\User::findOne(['username' => 'NewUser']);
        $space = Space::findOne(['name' => 'Space 2']);

        if (!$space->isMember($userId)) {
            $I->see('User is not member of invited Space!');
        }
    }
}
