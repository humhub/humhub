<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace notification\functional;

use notification\FunctionalTester;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The notification settings pages render the `NotificationSettings` island with its props.
 */
class SettingsPageCest
{
    public function testUserPageRendersTheIsland(FunctionalTester $I)
    {
        $I->wantTo('see the notification settings island on my account page');
        $I->amUser2();
        $I->amOnRoute('/notification/user');

        $I->seeResponseCodeIs(200);
        $I->seeElement('notification-settings#notification-settings');
        // the island brings its own cards: no account panel around it
        $I->dontSeeElement('.panel notification-settings');
        $I->seeElement('notification-settings[scope="user"]');
        $I->seeElement('notification-settings[settings-url$="/api/v2/notification/settings"]');
        $I->seeElement('notification-settings[reset-url$="/api/v2/notification/settings/reset"]');
        $I->dontSeeElement('notification-settings[reset-all-url]');

        $props = json_decode($I->grabAttributeFrom('notification-settings', 'props'), true);
        Assert::assertSame(['initial'], array_keys($props));
        Assert::assertSame('user', $props['initial']['scope']);
        Assert::assertSame('web', $props['initial']['channels'][0]['id']);
        Assert::assertContains('followers', array_column($props['initial']['categories'], 'id'));
        Assert::assertIsArray($props['initial']['defaults']);
        Assert::assertArrayHasKey('spaces', $props['initial']);
        Assert::assertNull($props['initial']['privacy'], 'the privacy mode is on the administration page only');
    }

    public function testAdminPageRendersTheIslandInTheGlobalScope(FunctionalTester $I)
    {
        $I->wantTo('see the notification defaults island on the administration page');
        $I->amAdmin();
        $I->amOnRoute('/notification/admin/defaults');

        $I->seeResponseCodeIs(200);
        $I->seeElement('notification-settings[scope="global"]');
        $I->seeElement('notification-settings[settings-url$="/api/v2/notification/settings?scope=global"]');
        $I->seeElement('notification-settings[reset-all-url$="/api/v2/notification/settings/reset-all"]');
        $I->dontSeeElement('notification-settings[reset-url]');

        $props = json_decode($I->grabAttributeFrom('notification-settings', 'props'), true);
        Assert::assertSame('global', $props['initial']['scope']);
        Assert::assertNull($props['initial']['defaults']);
        Assert::assertContains('admin', array_column($props['initial']['categories'], 'id'));
        Assert::assertSame(['mode' => 'full', 'fixed' => false], $props['initial']['privacy']);
    }

    public function testAdminPageShowsAPrivacyModeOfTheConfigurationAsFixed(FunctionalTester $I)
    {
        $I->wantTo('see the privacy mode of the configuration file as read-only');
        Yii::$app->getModule('notification')->privacyMode = 'redacted';
        try {
            $I->amAdmin();
            $I->amOnRoute('/notification/admin/defaults');

            $I->seeResponseCodeIs(200);
            $props = json_decode($I->grabAttributeFrom('notification-settings', 'props'), true);
            Assert::assertSame(['mode' => 'redacted', 'fixed' => true], $props['initial']['privacy']);
        } finally {
            Yii::$app->getModule('notification')->privacyMode = null;
        }
    }

    public function testAdminPageIsForbiddenForUsers(FunctionalTester $I)
    {
        $I->wantTo('be refused the notification defaults as a normal user');
        $I->amUser2();
        $I->amOnRoute('/notification/admin/defaults');

        $I->seeResponseCodeIs(403);
    }
}
