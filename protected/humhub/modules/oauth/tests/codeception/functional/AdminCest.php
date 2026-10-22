<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\functional;

use FunctionalTester;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\forms\SettingsForm;
use humhub\modules\oauth\Module;
use PHPUnit\Framework\Assert;

class AdminCest
{
    private const SETTINGS = [
        'allowApp', 'allowClients', 'allowUserClients', 'userClientScopes', 'allowPersonalAccessTokens',
        'patScopes', 'patMaxLifetimeDays', 'refreshTokenLifetimeDays', 'grantMaxLifetimeDays', 'revokeOnPasswordChange',
    ];

    public function _before(FunctionalTester $I)
    {
        $this->cleanUp();
    }

    public function _after(FunctionalTester $I)
    {
        $this->cleanUp();
    }

    private function cleanUp(): void
    {
        foreach (self::SETTINGS as $name) {
            Module::instance()->settings->delete($name);
        }
    }

    public function testMenuEntryAndPageForAdmin(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/authentication');
        $I->see('Apps & API Access');

        $I->amOnRoute('/oauth/admin/index');
        $I->seeResponseCodeIs(200);
        $I->see('Personal access tokens');
    }

    public function testSettingsAreSaved(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[allowApp]' => '0',
            'SettingsForm[allowClients]' => '1',
            'SettingsForm[allowUserClients]' => '1',
            'SettingsForm[userClientScopes]' => ['content:read'],
            'SettingsForm[allowPersonalAccessTokens]' => '1',
            'SettingsForm[patScopes]' => ['content:read', 'full'],
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '14',
            'SettingsForm[grantMaxLifetimeDays]' => '200',
            'SettingsForm[revokeOnPasswordChange]' => '1',
        ]);

        $module = Module::instance();
        Assert::assertFalse($module->allowsApp());
        Assert::assertTrue($module->allowsClients());
        Assert::assertTrue($module->allowsUserClients());
        Assert::assertSame(['content:read'], $module->getUserClientScopes());
        Assert::assertTrue($module->allowsPersonalAccessTokens());
        Assert::assertSame(['content:read', 'full'], $module->getPatScopes());
        Assert::assertSame(30, $module->getPatMaxLifetimeDays());
        Assert::assertSame(14, $module->getRefreshTokenLifetimeDays());
        Assert::assertSame(200, $module->getGrantMaxLifetimeDays());
        Assert::assertTrue($module->revokesOnPasswordChange());
    }

    public function testUserClientScopesDoNotAllowFull(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[allowClients]' => '1',
            'SettingsForm[userClientScopes]' => ['full'],
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '14',
            'SettingsForm[grantMaxLifetimeDays]' => '0',
        ]);

        Assert::assertFalse(Module::instance()->allowsClients());
        Assert::assertNotContains('full', Module::instance()->getUserClientScopes());
    }

    public function testRefreshTokenLifetimeOfZeroIsRejected(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[allowClients]' => '1',
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '0',
            'SettingsForm[grantMaxLifetimeDays]' => '0',
        ]);

        Assert::assertFalse(Module::instance()->allowsClients());
        Assert::assertSame(90, Module::instance()->getRefreshTokenLifetimeDays());
    }

    public function testDefaultScopeSelectionStaysUnset(FunctionalTester $I)
    {
        $default = ScopeRegistry::getDefaultRestrictedScopeIds();
        Module::instance()->settings->set('patScopes', json_encode(['content:read']));

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[patScopes]' => array_reverse($default),
            'SettingsForm[userClientScopes]' => $default,
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '14',
            'SettingsForm[grantMaxLifetimeDays]' => '0',
        ]);

        Assert::assertNull(Module::instance()->settings->get('patScopes'));
        Assert::assertNull(Module::instance()->settings->get('userClientScopes'));
    }

    public function testUnregisteredStoredScopeDoesNotBlockSaving(FunctionalTester $I)
    {
        Module::instance()->settings->set('patScopes', json_encode(['content:read', 'gone:read']));
        Assert::assertSame(['content:read'], (new SettingsForm())->patScopes);

        $I->amAdmin();
        $I->amOnRoute('/oauth/admin/index');
        $I->submitForm('#oauth-settings-form', [
            'SettingsForm[allowClients]' => '1',
            'SettingsForm[patMaxLifetimeDays]' => '30',
            'SettingsForm[refreshTokenLifetimeDays]' => '14',
            'SettingsForm[grantMaxLifetimeDays]' => '0',
        ]);

        Assert::assertTrue(Module::instance()->allowsClients());
    }

    public function testNormalUserIsForbidden(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/oauth/admin/index');
        $I->seeResponseCodeIs(403);
    }
}
