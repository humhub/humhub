<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace notification\functional;

use notification\FunctionalTester;
use PHPUnit\Framework\Assert;

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
        $I->see('Notification Settings');
        $I->seeElement('notification-settings#notification-settings');
        $I->seeElement('notification-settings[scope="user"]');
        $I->seeElement('notification-settings[settings-url$="/api/v2/notification/settings"]');
        $I->seeElement('notification-settings[reset-url$="/api/v2/notification/settings/reset"]');
        $I->dontSeeElement('notification-settings[reset-all-url]');

        $props = json_decode($I->grabAttributeFrom('notification-settings', 'props'), true);
        Assert::assertSame(['initial'], array_keys($props));
        Assert::assertSame('web', $props['initial']['channels'][0]['id']);
        Assert::assertArrayHasKey('spaces', $props['initial']);
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
        Assert::assertContains('admin', array_column($props['initial']['channels'][1]['groups'], 'id'));
    }

    public function testAdminPageIsForbiddenForUsers(FunctionalTester $I)
    {
        $I->wantTo('be refused the notification defaults as a normal user');
        $I->amUser2();
        $I->amOnRoute('/notification/admin/defaults');

        $I->seeResponseCodeIs(403);
    }
}
