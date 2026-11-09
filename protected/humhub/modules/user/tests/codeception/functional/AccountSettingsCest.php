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

        $I->see('My Settings', '.left-navigation .panel-heading');
        $labels = array_map('trim', $I->grabMultiple('.left-navigation .list-group-item'));
        Assert::assertSame(
            ['Profile', 'Account', 'Notifications & E-Mails', 'Appearance & Accessibility', 'Security & Privacy'],
            $labels,
        );
        $I->seeElement('.left-navigation [data-menu-id="account-settings-profile"].active');
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

    public function testGeneralTabShowsTheUsernameAndEmailSections(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-settings');

        $I->see('Account', '.page-header-title');
        $I->seeNumberOfElements('.accordion .accordion-item', 2);
        $I->see('E-Mail address', '.accordion-item:first-child .accordion-button');
        $I->see('Username', '.accordion-item:nth-child(2) .accordion-button');
        $I->seeElement('.accordion-item:first-child .accordion-collapse.show form[action*="change-email"]');
        $I->dontSeeElement('.accordion-item:nth-child(2) .accordion-collapse.show');
        $I->seeElement('.accordion-item:nth-child(2) form[action*="change-username"]');
        $I->see('Your current username is User1.', '.accordion-subtitle');
        $I->dontSee('Profile Tags');
        $I->seeElement('.left-navigation [data-menu-id="account-settings-settings"].active');
    }

    public function testChangeUsernameInItsSection(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-settings');
        $I->submitForm('form[action*="change-username"]', [
            'AccountChangeUsername[currentPassword]' => 'user^humhub@PASS%worD!',
            'AccountChangeUsername[newUsername]' => 'User1Renamed',
        ]);

        $I->seeInCurrentUrl('account%2Fedit-settings');
        $I->seeInSource('Your username has been successfully changed');
        $I->seeEmailIsSent();
        Assert::assertNotNull(User::findOne(['username' => 'User1Renamed']));
    }

    public function testChangeUsernameErrorsStayInItsSection(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit-settings');
        $I->submitForm('form[action*="change-username"]', [
            'AccountChangeUsername[currentPassword]' => 'wrong',
            'AccountChangeUsername[newUsername]' => 'User1Renamed',
        ]);

        $I->see('Account', '.page-header-title');
        $I->see('Your password is incorrect!', 'form[action*="change-username"]');
        // The section with the error starts open instead of the first one
        $I->seeElement('.accordion-collapse.show form[action*="change-username"]');
        $I->dontSeeElement('.accordion-collapse.show form[action*="change-email"]');
        $I->seeElement('.left-navigation [data-menu-id="account-settings-settings"].active');
        Assert::assertNull(User::findOne(['username' => 'User1Renamed']));
    }

    public function testChangeEmailSendsAConfirmationAndItsLinkAppliesTheChange(FunctionalTester $I)
    {
        $I->amUser1();
        $oldEmail = User::findOne(['username' => 'User1'])->email;

        $I->amOnRoute('/user/account/edit-settings');
        $I->submitForm('form[action*="change-email"]', [
            'AccountChangeEmail[currentPassword]' => 'user^humhub@PASS%worD!',
            'AccountChangeEmail[newEmail]' => 'user1-new@example.com',
        ]);

        $I->seeInCurrentUrl('account%2Fedit-settings');
        $I->seeInSource('confirmation e-mail');
        $I->seeEmailIsSent();
        $user = User::findOne(['username' => 'User1']);
        Assert::assertSame($oldEmail, $user->email);

        $token = md5(Yii::$app->settings->get('secret') . $user->guid . 'user1-new@example.com');
        $I->amOnRoute('/user/account/change-email-validate', ['email' => 'user1-new@example.com', 'token' => $token]);

        $I->seeInCurrentUrl('account%2Fedit-settings');
        $I->seeInSource('successfully changed to user1-new@example.com');
        Assert::assertSame('user1-new@example.com', User::findOne(['username' => 'User1'])->email);
    }

    public function testOtherSectionHoldsTheIntroductionTour(FunctionalTester $I)
    {
        $I->amUser1();
        $settings = Yii::$app->getModule('tour')->settings;

        try {
            $settings->set('enable', 0);
            $I->amOnRoute('/user/account/edit-settings');
            $I->dontSee('Other', '.accordion-title');

            $settings->set('enable', 1);
            $I->amOnRoute('/user/account/edit-settings');
            $I->see('Other', '.accordion-item:last-child .accordion-title');
            $I->dontSeeElement('.accordion-item:last-child .accordion-collapse.show');
            $I->see('Hide introduction tour panel on dashboard', '.accordion-item:last-child');
            $I->submitForm('#account-settings-form', ['AccountSettings[show_introduction_tour]' => 1]);

            $user = User::findOne(['username' => 'User1']);
            Assert::assertEquals(1, $settings->contentContainer($user)->get('hideTourPanel'));
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
        $I->seeElement('.left-navigation [data-menu-id="account-settings-settings"].active');
    }

    public function testTopicsPageIsATabOfTheAccountPage(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/topic/manage', ['cguid' => User::findOne(['username' => 'User1'])->guid]);

        $I->see('Account', '.page-header-title');
        $I->see('Topics', '.page-header .nav-pills .nav-link.active');
        $I->see('General', '.page-header .nav-pills');
        $I->seeElement('.left-navigation [data-menu-id="account-settings-settings"].active');
    }
}
