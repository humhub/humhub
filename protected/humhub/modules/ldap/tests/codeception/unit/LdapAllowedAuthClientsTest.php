<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\connection\LdapConnectionRegistry;
use humhub\modules\ldap\Module;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Tests that allowedAuthClientIds on LdapUserSource is correctly
 * exposed and enforced via UserSourceService.
 *
 * No real LDAP server is required — LdapUserSource and a fake connection
 * config are constructed in-memory.
 */
class LdapAllowedAuthClientsTest extends HumHubDbTestCase
{
    protected function _before(): void
    {
        parent::_before();
        $this->installFakeConnection();
    }

    private function installFakeConnection(): void
    {
        $registry = new LdapConnectionRegistry();
        $registry->setConfigs([
            'ldap' => new LdapConnectionConfig([
                'usernameAttribute' => 'uid',
                'emailAttribute' => 'mail',
                'idAttribute' => 'uid',
            ]),
        ]);
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        $module->setConnectionRegistry($registry);
    }

    // ---------------------------------------------------------------------------
    // LdapUserSource::getAllowedAuthClientIds
    // ---------------------------------------------------------------------------

    public function testDefaultAllowedAuthClientsIsLdapOnly(): void
    {
        $source = new LdapUserSource(['connectionId' => 'ldap']);
        $source->init();

        $this->assertSame(['ldap'], $source->getAllowedAuthClientIds());
    }

    public function testAllowedAuthClientsAreConfigurable(): void
    {
        $source = new LdapUserSource([
            'connectionId' => 'ldap',
            'allowedAuthClientIds' => ['ldap', 'saml'],
        ]);
        $source->init();

        $this->assertContains('ldap', $source->getAllowedAuthClientIds());
        $this->assertContains('saml', $source->getAllowedAuthClientIds());
    }

    // ---------------------------------------------------------------------------
    // UserSourceService::canChangePassword for LDAP users
    // ---------------------------------------------------------------------------

    public function testLdapUserCannotChangePasswordByDefault(): void
    {
        $user = $this->makeUserWithLdapSource(['allowedAuthClientIds' => ['ldap']]);

        $this->assertFalse(UserSourceService::getForUser($user)->canChangePassword());
    }

    public function testLdapUserCanChangePasswordWhenLocalAllowed(): void
    {
        $user = $this->makeUserWithLdapSource(['allowedAuthClientIds' => ['ldap', 'local']]);

        $this->assertTrue(UserSourceService::getForUser($user)->canChangePassword());
    }

    // ---------------------------------------------------------------------------
    // UserSourceService::isAuthClientAllowed for LDAP users
    // ---------------------------------------------------------------------------

    public function testLdapAuthClientAlwaysAllowedByDefault(): void
    {
        $user = $this->makeUserWithLdapSource();
        $service = UserSourceService::getForUser($user);

        $this->assertTrue($service->isAuthClientAllowed('ldap'));
    }

    public function testOAuthClientNotAllowedByDefault(): void
    {
        $user = $this->makeUserWithLdapSource();
        $service = UserSourceService::getForUser($user);

        $this->assertFalse($service->isAuthClientAllowed('oauth'));
        $this->assertFalse($service->isAuthClientAllowed('local'));
    }

    public function testOAuthClientAllowedWhenExplicitlyPermitted(): void
    {
        $user = $this->makeUserWithLdapSource(['allowedAuthClientIds' => ['ldap', 'oauth']]);
        $service = UserSourceService::getForUser($user);

        $this->assertTrue($service->isAuthClientAllowed('oauth'));
        $this->assertFalse($service->isAuthClientAllowed('local'));
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    /**
     * Registers a LdapUserSource in userSourceCollection and returns a User
     * model instance (not persisted) with user_source = 'ldap'.
     */
    private function makeUserWithLdapSource(array $sourceConfig = []): User
    {
        $sourceConfig = array_merge(['connectionId' => 'ldap'], $sourceConfig);
        $source = new LdapUserSource($sourceConfig);
        $source->init();
        Yii::$app->userSourceCollection->setUserSource('ldap', $source);

        $user = new User();
        $user->user_source = 'ldap';

        return $user;
    }
}
