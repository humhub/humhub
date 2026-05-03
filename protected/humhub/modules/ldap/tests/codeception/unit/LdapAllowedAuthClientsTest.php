<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Tests that allowedAuthClientIds on LdapAuth/LdapUserSource is correctly
 * exposed and enforced via UserSourceService.
 *
 * No real LDAP server is required — LdapAuth objects are constructed directly.
 */
class LdapAllowedAuthClientsTest extends HumHubDbTestCase
{
    // ---------------------------------------------------------------------------
    // LdapUserSource::getAllowedAuthClientIds
    // ---------------------------------------------------------------------------

    public function testDefaultAllowedAuthClientsIsLdapOnly(): void
    {
        $source = new LdapUserSource($this->makeLdapAuth());

        $this->assertSame(['ldap'], $source->getAllowedAuthClientIds());
    }

    public function testAllowedAuthClientsAreConfigurable(): void
    {
        $source = new LdapUserSource($this->makeLdapAuth(['allowedAuthClientIds' => ['ldap', 'saml']]));

        $this->assertContains('ldap', $source->getAllowedAuthClientIds());
        $this->assertContains('saml', $source->getAllowedAuthClientIds());
    }

    public function testAllowedAuthClientIdsDelegatesToLdapAuth(): void
    {
        $auth = $this->makeLdapAuth(['allowedAuthClientIds' => ['ldap', 'local']]);
        $source = new LdapUserSource($auth);

        $this->assertSame($auth->allowedAuthClientIds, $source->getAllowedAuthClientIds());
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

    private function makeLdapAuth(array $config = []): LdapAuth
    {
        return new LdapAuth(array_merge([
            'usernameAttribute'   => 'uid',
            'emailAttribute'      => 'mail',
            'idAttribute'         => 'uid',
            'allowedAuthClientIds' => ['ldap'],
        ], $config));
    }

    /**
     * Registers a LdapUserSource in userSourceCollection and returns a User
     * model instance (not persisted) with user_source = 'ldap'.
     */
    private function makeUserWithLdapSource(array $authConfig = []): User
    {
        $source = new LdapUserSource($this->makeLdapAuth($authConfig));
        Yii::$app->userSourceCollection->setUserSource('ldap', $source);

        $user = new User();
        $user->user_source = 'ldap';

        return $user;
    }
}
