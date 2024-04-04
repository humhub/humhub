<?php

use humhub\modules\space\models\forms\InviteForm;
use humhub\modules\space\models\Space;
use humhub\modules\user\services\LinkRegistrationService;
use user\FunctionalTester;

class LinkInviteCest
{
    public function testDisabledLinkInvite(FunctionalTester $I)
    {
        $I->wantTo('ensure that invite by link is correctly disabled');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 0);

        $inviteForm = new InviteForm();
        $inviteForm->space = Space::findOne(['name' => 'Space 2']);
        $inviteUrl = $inviteForm->getInviteLink();

        $I->amOnPage($inviteUrl);
        $I->seeResponseCodeIs(404);
    }

    public function testInvalidToken(FunctionalTester $I)
    {
        $I->wantTo('ensure that invite by link is without valid token');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 1);

        $I->amOnRoute('/user/registration/by-link', ['token' => 'abcd', 'spaceId' => 1]);
        $I->seeResponseCodeIs(400);
    }

    public function testValidTokenDifferentSpaceId(FunctionalTester $I)
    {
        $I->wantTo('ensure that invite by link is with valid token and different space ID');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 1);


        // Generate Token
        $space = Space::findOne(['name' => 'Space 2']);
        $inviteForm = new InviteForm();
        $inviteForm->space = $space;
        $inviteUrl = $inviteForm->getInviteLink();

        $linkRegistrationService = new LinkRegistrationService(null, $space);
        $I->amOnRoute('/user/registration/by-link', ['token' => $linkRegistrationService->getStoredToken(), 'spaceId' => $space->id]);
        $I->seeResponseCodeIs(200);

        $I->amOnRoute('/user/registration/by-link', ['token' => $linkRegistrationService->getStoredToken(), 'spaceId' => 1]);
        $I->seeResponseCodeIs(400);


        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 0);
        $I->amOnRoute('/user/registration/by-link', ['token' => 'abc', 'spaceId' => 1]);
        $I->seeResponseCodeIs(404);

    }


    public function testSpaceInvite(FunctionalTester $I)
    {
        $I->wantTo('ensure that invited users become member of the space');

        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInviteByLink', 1);

        $inviteForm = new InviteForm();
        $inviteForm->space = Space::findOne(['name' => 'Space 2']);
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
