<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\AccountProfileMenu;
use humhub\widgets\menu\MenuLink;
use PHPUnit\Framework\Assert;
use user\FunctionalTester;
use Yii;
use yii\base\Event;
use yii\helpers\Url;

/**
 * The account section: the left menu, the profile page and the "Account" page with its "General" tab
 */
class AccountSettingsCest
{
    public function testAccountMenuEntries(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit');

        $I->see('My Settings', '.section-menu .section-menu-title');
        $I->dontSeeElement('.section-menu .section-menu-title strong');
        $labels = array_map('trim', $I->grabMultiple('.section-menu .nav-section .nav-link'));
        Assert::assertSame(
            ['Profile', 'Account', 'Notifications & E-Mails', 'Appearance & Accessibility', 'Security & Privacy'],
            $labels,
        );
        $I->seeElement('.section-menu [data-menu-id="account-settings-profile"].active');
    }

    public function testProfileCategoriesAreTheTabsOfThePage(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit');
        $I->see('Profile', '.page-header-title');
        Assert::assertSame(
            ['General', 'Communication', 'Social bookmarks', 'Tags'],
            array_map('trim', $I->grabMultiple('.page-header .nav-pills .nav-link')),
        );
        $I->see('General', '.page-header .nav-pills .nav-link.active');
        $I->see('General', '.content-card .card-title');
        $I->seeElement('#profile-firstname');
        $I->dontSeeElement('#profile-mobile');
        $I->dontSeeElement('form[data-ui-widget="ui.form.TabbedForm"]');

        $I->amOnRoute('/user/account/edit', ['category' => 2]);
        $I->see('Communication', '.page-header .nav-pills .nav-link.active');
        $I->see('Communication', '.content-card .card-title');
        $I->seeElement('#profile-mobile');
        $I->dontSeeElement('#profile-firstname');

        $I->amOnRoute('/user/account/edit', ['category' => 99]);
        $I->seeResponseCodeIs(404);
    }

    public function testModuleTabOnTheProfilePage(FunctionalTester $I)
    {
        $handler = function ($event) {
            $event->sender->addEntry(new MenuLink(['label' => 'My location', 'url' => ['/dashboard'], 'sortOrder' => 150]));
        };
        Event::on(AccountProfileMenu::class, AccountProfileMenu::EVENT_INIT, $handler);
        try {
            $I->amUser1();
            $I->amOnRoute('/user/account/edit');
            Assert::assertSame(
                ['General', 'My location', 'Communication', 'Social bookmarks', 'Tags'],
                array_map('trim', $I->grabMultiple('.page-header .nav-pills .nav-link')),
            );
        } finally {
            Event::off(AccountProfileMenu::class, AccountProfileMenu::EVENT_INIT, $handler);
        }
    }

    public function testSavingACategoryValidatesOnlyItsFields(FunctionalTester $I)
    {
        $I->amUser1();
        // An empty required field of another category, e.g. one the administrator added later
        Profile::updateAll(['lastname' => ''], ['user_id' => 2]);

        $I->amOnRoute('/user/account/edit', ['category' => 2]);
        $I->submitForm('.content-card form', ['Profile[mobile]' => '0171 123456'], 'save');
        $I->dontSee('Last name cannot be blank');
        Assert::assertSame('0171 123456', Profile::findOne(['user_id' => 2])->mobile);

        $I->amOnRoute('/user/account/edit', ['category' => 1]);
        $I->submitForm('.content-card form', ['Profile[firstname]' => 'Peter', 'Profile[lastname]' => ''], 'save');
        $I->see('Last name cannot be blank');
    }

    public function testTagsPageSavesTheTags(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-tags');
        $I->see('Tags', '.page-header .nav-pills .nav-link.active');
        $I->see('Profile Tags');
        $I->seeElement('#profile-tags-form [name="User[tagsField][]"]');

        $I->sendAjaxPostRequest(Url::toRoute('/user/account/edit-tags'), ['User' => ['tagsField' => ['Tester', 'Actor']]]);
        Assert::assertEqualsCanonicalizing(['Tester', 'Actor'], User::findOne(['username' => 'User1'])->getTags());

        // An emptied picker posts no value and clears the tags
        $I->sendAjaxPostRequest(Url::toRoute('/user/account/edit-tags'), ['User' => ['tagsField' => '']]);
        Assert::assertSame([], User::findOne(['username' => 'User1'])->getTags());
    }

    public function testGeneralTabIsOneFormWithTheCurrentValues(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-settings');

        $I->see('Account', '.page-header-title');
        $I->dontSeeElement('.accordion');
        $I->seeInField('AccountSettings[username]', 'User1');
        $I->seeInField('AccountSettings[email]', User::findOne(['id' => 2])->email);
        $I->see('can make some links unusable', '.field-accountsettings-username .form-text');
        $I->see('once you have confirmed it', '.field-accountsettings-email .form-text');
        $I->seeNumberOfElements('#account-settings-form [type="submit"]', 1);
        $I->dontSee('Profile Tags');
        $I->seeElement('.section-menu [data-menu-id="account-settings-settings"].active');
    }

    public function testChangeUsername(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-settings');
        $I->submitForm('#account-settings-form', ['AccountSettings[username]' => 'User1Renamed']);

        $I->seeInCurrentUrl('account%2Fedit-settings');
        $I->seeInSource('Your username has been successfully changed');
        $I->dontSeeInSource('confirmation e-mail');
        $I->seeEmailIsSent(1);
        Assert::assertNotNull(User::findOne(['username' => 'User1Renamed']));
    }

    public function testChangeUsernameErrorsStayAtTheField(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-settings');
        $I->submitForm('#account-settings-form', ['AccountSettings[username]' => 'Admin']);

        $I->see('Account', '.page-header-title');
        $I->see('is already in use', '.field-accountsettings-username');
        $I->seeInField('AccountSettings[username]', 'Admin');
        $I->seeElement('.section-menu [data-menu-id="account-settings-settings"].active');
        Assert::assertSame('User1', User::findOne(['id' => 2])->username);
    }

    public function testChangeEmailSendsAConfirmationAndItsLinkAppliesTheChange(FunctionalTester $I)
    {
        $I->amUser1();
        $oldEmail = User::findOne(['username' => 'User1'])->email;

        $I->amOnRoute('/user/account/edit-settings');
        $I->submitForm('#account-settings-form', ['AccountSettings[email]' => 'user1-new@example.com']);

        $I->seeInCurrentUrl('account%2Fedit-settings');
        $I->seeInSource('confirmation e-mail');
        $I->dontSeeInSource('Your username has been successfully changed');
        $I->seeEmailIsSent(1);
        $user = User::findOne(['username' => 'User1']);
        Assert::assertSame($oldEmail, $user->email);
        // The field shows the current address until the new one is confirmed
        $I->seeInField('AccountSettings[email]', $oldEmail);

        $token = md5(Yii::$app->settings->get('secret') . $user->guid . 'user1-new@example.com');
        $I->amOnRoute('/user/account/change-email-validate', ['email' => 'user1-new@example.com', 'token' => $token]);

        $I->seeInCurrentUrl('account%2Fedit-settings');
        $I->seeInSource('successfully changed to user1-new@example.com');
        Assert::assertSame('user1-new@example.com', User::findOne(['username' => 'User1'])->email);
    }

    public function testTheIntroductionTourIsSavedWithTheSameButton(FunctionalTester $I)
    {
        $I->amUser1();
        $settings = Yii::$app->getModule('tour')->settings;

        try {
            $settings->set('enable', 0);
            $I->amOnRoute('/user/account/edit-settings');
            $I->dontSee('Hide introduction tour panel on dashboard');

            $settings->set('enable', 1);
            $I->amOnRoute('/user/account/edit-settings');
            $I->see('Hide introduction tour panel on dashboard', '#account-settings-form');
            $I->submitForm('#account-settings-form', ['AccountSettings[show_introduction_tour]' => 1]);

            $I->seeInCurrentUrl('account%2Fedit-settings');
            $I->dontSeeEmailIsSent();
            $user = User::findOne(['username' => 'User1']);
            Assert::assertEquals(1, $settings->contentContainer($user)->get('hideTourPanel'));
            Assert::assertSame('User1', $user->username);
        } finally {
            $settings->delete('enable');
        }
    }

    public function testModulesPageIsATabOfTheAccountPage(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-modules');

        $I->seeResponseCodeIs(200);
        $I->see('Account', '.page-header-title');
        $I->see('Similar to Spaces, your personal profile also allows you to use modules');
        $I->seeElement('.section-menu [data-menu-id="account-settings-settings"].active');
    }

    public function testTopicsPageIsATabOfTheAccountPage(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/topic/manage', ['cguid' => User::findOne(['username' => 'User1'])->guid]);

        $I->see('Account', '.page-header-title');
        $I->see('Topics', '.page-header .nav-pills .nav-link.active');
        $I->see('General', '.page-header .nav-pills');
        $I->seeElement('.section-menu [data-menu-id="account-settings-settings"].active');
    }
}
