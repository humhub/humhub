<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\connection\LdapConnectionRegistry;
use humhub\modules\ldap\Module;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Unit tests for {@see LdapAuth} attribute normalisation logic.
 *
 * No real LDAP server is required. Connection metadata lives in
 * {@see LdapConnectionConfig}; the test installs a fake registry per case.
 */
class LdapAuthTest extends HumHubDbTestCase
{
    private function makeLdapAuth(array $configOverrides = []): LdapAuth
    {
        $config = new LdapConnectionConfig(array_merge([
            'usernameAttribute' => 'uid',
            'emailAttribute' => 'mail',
            'idAttribute' => null,
        ], $configOverrides));

        $registry = new LdapConnectionRegistry();
        $registry->setConfigs(['ldap' => $config]);
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        $module->setConnectionRegistry($registry);

        $auth = new LdapAuth(['connectionId' => 'ldap', 'clientId' => 'ldap']);
        $auth->init();
        return $auth;
    }

    // ---------------------------------------------------------------------------
    // Attribute mapping
    // ---------------------------------------------------------------------------

    public function testUsernameAttributeIsMappedToUsername(): void
    {
        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes(['uid' => 'john.doe', 'mail' => 'john@example.org']);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('john.doe', $attrs['username']);
    }

    public function testEmailAttributeIsMappedToEmail(): void
    {
        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes(['uid' => 'john.doe', 'mail' => 'john@example.org']);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('john@example.org', $attrs['email']);
    }

    public function testLanguageAttributeIsMappedToLanguage(): void
    {
        // LdapHelper::cleanLdapResponse() lowercases all keys before they reach
        // the AuthClient, so the normalise-map is case-sensitive on lowercase keys.
        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes([
            'uid' => 'john.doe',
            'mail' => 'john@example.org',
            'preferredlanguage' => 'de',
        ]);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('de', $attrs['language']);
    }

    // ---------------------------------------------------------------------------
    // Multi-value handling
    // ---------------------------------------------------------------------------

    public function testMultiValueAttributeIsCollapsedToFirstElement(): void
    {
        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes([
            'uid' => ['john.doe', 'johndoe'],
            'mail' => ['john@example.org'],
        ]);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('john.doe', $attrs['username']);
        $this->assertSame('john@example.org', $attrs['email']);
    }

    public function testMemberofRemainsAnArray(): void
    {
        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes([
            'uid' => 'john.doe',
            'mail' => 'john@example.org',
            'memberof' => ['cn=group1,dc=example', 'cn=group2,dc=example'],
        ]);

        $attrs = $auth->getUserAttributes();

        $this->assertIsArray($attrs['memberof']);
        $this->assertCount(2, $attrs['memberof']);
    }

    // ---------------------------------------------------------------------------
    // ID attribute mapping
    // ---------------------------------------------------------------------------

    public function testIdAttributeIsSetAsId(): void
    {
        $auth = $this->makeLdapAuth(['idAttribute' => 'uid']);
        $auth->setUserAttributes(['uid' => 'john.doe', 'mail' => 'john@example.org']);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('john.doe', $attrs['id']);
    }

    public function testIdIsAbsentWhenNoIdAttributeConfigured(): void
    {
        $auth = $this->makeLdapAuth(['idAttribute' => null]);
        $auth->setUserAttributes(['uid' => 'john.doe', 'mail' => 'john@example.org']);

        $attrs = $auth->getUserAttributes();

        $this->assertArrayNotHasKey('id', $attrs);
    }

    // ---------------------------------------------------------------------------
    // Date field formatting
    // ---------------------------------------------------------------------------

    public function testDateFieldIsFormattedAccordingToConfig(): void
    {
        \Yii::$app->params['ldap']['dateFields'] = ['birthday' => 'Ymd'];

        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes([
            'uid' => 'john.doe',
            'mail' => 'john@example.org',
            'birthday' => '19900115',
        ]);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('1990-01-15', $attrs['birthday']);

        \Yii::$app->params['ldap']['dateFields'] = [];
    }

    public function testInvalidDateFieldResultsInEmptyString(): void
    {
        \Yii::$app->params['ldap']['dateFields'] = ['birthday' => 'Ymd'];

        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes([
            'uid' => 'john.doe',
            'mail' => 'john@example.org',
            'birthday' => 'not-a-date',
        ]);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('', $attrs['birthday']);

        \Yii::$app->params['ldap']['dateFields'] = [];
    }
}
