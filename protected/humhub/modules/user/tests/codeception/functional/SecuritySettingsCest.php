<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use humhub\modules\user\models\User;
use humhub\modules\user\tests\codeception\_support\TestExportSection;
use humhub\modules\user\widgets\security\SecurityDataSections;
use humhub\modules\user\widgets\SecuritySettingsMenu;
use humhub\widgets\menu\MenuLink;
use PHPUnit\Framework\Assert;
use user\FunctionalTester;
use Yii;
use yii\base\Event;
use yii\db\Query;

class SecuritySettingsCest
{
    public function _before()
    {
        // The session table is not part of the fixtures
        Yii::$app->db->createCommand()->delete('user_http_session', ['id' => 'other-device'])->execute();
    }

    public function testIndexRedirectsToLoginTab(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/security/index');
        $I->seeInCurrentUrl('security%2Flogin');
        $I->see('Change password');
        $I->see('Security & Privacy');
    }

    public function testOldRoutesRedirect(FunctionalTester $I)
    {
        $I->amUser1();

        $I->amOnRoute('/user/account/change-password');
        $I->seeInCurrentUrl('security%2Flogin');

        $I->amOnRoute('/user/account/delete');
        $I->seeInCurrentUrl('security%2Fdelete');
    }

    public function testProfilePermissionsSectionDependsOnSetting(FunctionalTester $I)
    {
        $I->amUser1();

        Yii::$app->getModule('user')->settings->set('enableProfilePermissions', 0);
        $I->amOnRoute('/user/security/privacy');
        $I->seeResponseCodeIs(200);
        $I->dontSee('Profile permissions');

        Yii::$app->getModule('user')->settings->set('enableProfilePermissions', 1);
        $I->amOnRoute('/user/security/login');
        $I->seeElement('a[href*="security%2Fprivacy"]');
        $I->amOnRoute('/user/security/privacy');
        $I->see('Profile permissions');
        $I->seeElement('.permission-grid-editor');

        $I->amOnRoute('/user/account/permissions');
        $I->seeInCurrentUrl('security%2Fprivacy');
    }

    public function testPrivacyTabHiddenWithoutSections(FunctionalTester $I)
    {
        $settings = Yii::$app->getModule('user')->settings;
        $settings->set('enableProfilePermissions', 0);
        $settings->set('auth.hideOnlineStatus', 1);
        $settings->set('auth.blockUsers', 0);
        $settings->set('auth.allowGuestAccess', 0);

        try {
            $I->amUser1();
            $I->amOnRoute('/user/security/login');
            $I->dontSeeElement('a[href*="security%2Fprivacy"]');
        } finally {
            $settings->delete('auth.hideOnlineStatus');
            $settings->delete('auth.blockUsers');
        }
    }

    public function testSavePrivacySettings(FunctionalTester $I)
    {
        $I->amUser1();
        $user = User::findOne(['username' => 'User1']);
        $user->settings->delete('hideOnlineStatus');
        Yii::$app->getModule('user')->settings->delete('auth.hideOnlineStatus');
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);

        $I->amOnRoute('/user/security/privacy');
        $I->see('Profile visibility');
        $I->see('Hide my online status');
        $I->see('Blocked users');

        $I->submitForm('form[action*="security%2Fprivacy"]', [
            'PrivacySettings[visibility]' => User::VISIBILITY_ALL,
            'PrivacySettings[hideOnlineStatus]' => 1,
        ]);

        $user = User::findOne(['username' => 'User1']);
        Assert::assertEquals(User::VISIBILITY_ALL, $user->visibility);
        Assert::assertEquals('1', $user->settings->get('hideOnlineStatus'));
    }

    public function testGeneralSettingsWithoutPrivacyFields(FunctionalTester $I)
    {
        $I->amUser1();
        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);

        $I->amOnRoute('/user/account/edit-settings');
        $I->see('Profile Tags');
        $I->dontSee('Profile visibility');
        $I->dontSee('Hide my online status');
        $I->dontSee('Blocked users');
        $I->dontSeeElement('[name="AccountSettings[visibility]"]');
    }

    public function testChangePasswordLogsOutOtherSessions(FunctionalTester $I)
    {
        $I->amUser1();
        $userId = User::findOne(['username' => 'User1'])->id;
        Yii::$app->db->createCommand()->insert('user_http_session', [
            'id' => 'other-device', 'user_id' => $userId, 'expire' => time() + 3600, 'data' => '',
        ])->execute();

        $I->amOnRoute('/user/security/login');
        $I->submitForm('form[action*="security%2Flogin"]', [
            'Password[currentPassword]' => 'user^humhub@PASS%worD!',
            'Password[newPassword]' => 'New^humhub@PASS%worD!1',
            'Password[newPasswordConfirm]' => 'New^humhub@PASS%worD!1',
        ]);

        $I->seeInSource('Your password has been successfully changed!');
        Assert::assertFalse((new Query())->from('user_http_session')->where(['id' => 'other-device'])->exists());
    }

    public function testSessionsTabListsAndTerminatesSessions(FunctionalTester $I)
    {
        $I->amUser1();
        $userId = User::findOne(['username' => 'User1'])->id;
        Yii::$app->db->createCommand()->insert('user_http_session', [
            'id' => 'other-device', 'user_id' => $userId, 'expire' => time() + 3600, 'data' => '',
            'logged_in_at' => time() - 600,
            'user_agent' => 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.36',
        ])->execute();

        $I->amOnRoute('/user/security/sessions');
        $I->see('Chrome · Android');

        $I->sendAjaxPostRequest('/index-test.php?r=user%2Fsecurity%2Fterminate-session&key=' . hash('sha256', 'other-device'));
        Assert::assertFalse((new Query())->from('user_http_session')->where(['id' => 'other-device'])->exists());
    }

    public function testModuleTabAndSection(FunctionalTester $I)
    {
        $tabHandler = function ($event) {
            $event->sender->addEntry(new MenuLink(['label' => 'Two-Factor Test', 'url' => ['/dashboard'], 'sortOrder' => 200]));
        };
        $sectionHandler = function ($event) {
            $event->sender->addWidget(TestExportSection::class, [], ['sortOrder' => 100]);
        };
        Event::on(SecuritySettingsMenu::class, SecuritySettingsMenu::EVENT_INIT, $tabHandler);
        Event::on(SecurityDataSections::class, SecurityDataSections::EVENT_INIT, $sectionHandler);

        try {
            $I->amUser1();
            $I->amOnRoute('/user/security/data');
            $I->see('Two-Factor Test');
            $I->see('Test export section');
            $I->see('Delete account');
        } finally {
            Event::off(SecuritySettingsMenu::class, SecuritySettingsMenu::EVENT_INIT, $tabHandler);
            Event::off(SecurityDataSections::class, SecurityDataSections::EVENT_INIT, $sectionHandler);
        }
    }
}
