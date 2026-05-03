<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\authclient\LdapAuth;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Unit tests for {@see LdapAuth} attribute normalisation logic.
 *
 * These tests exercise the mapping from raw LDAP attributes to HumHub user
 * attributes.  A real LDAP server is NOT required – user attributes are
 * injected directly via setUserAttributes().
 */
class LdapAuthTest extends HumHubDbTestCase
{
    private function makeLdapAuth(array $config = []): LdapAuth
    {
        return new LdapAuth(array_merge([
            'usernameAttribute' => 'uid',
            'emailAttribute'    => 'mail',
            'idAttribute'       => null,
        ], $config));
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
        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes([
            'uid'               => 'john.doe',
            'mail'              => 'john@example.org',
            'preferredLanguage' => 'de',
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
            'uid'  => ['john.doe', 'johndoe'],
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
            'uid'      => 'john.doe',
            'mail'     => 'john@example.org',
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
        // Configure a date field mapping via Yii params
        \Yii::$app->params['ldap']['dateFields'] = ['birthday' => 'Ymd'];

        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes([
            'uid'      => 'john.doe',
            'mail'     => 'john@example.org',
            'birthday' => '19900115',
        ]);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('1990-01-15', $attrs['birthday']);

        // Clean up
        \Yii::$app->params['ldap']['dateFields'] = [];
    }

    public function testInvalidDateFieldResultsInEmptyString(): void
    {
        \Yii::$app->params['ldap']['dateFields'] = ['birthday' => 'Ymd'];

        $auth = $this->makeLdapAuth();
        $auth->setUserAttributes([
            'uid'      => 'john.doe',
            'mail'     => 'john@example.org',
            'birthday' => 'not-a-date',
        ]);

        $attrs = $auth->getUserAttributes();

        $this->assertSame('', $attrs['birthday']);

        \Yii::$app->params['ldap']['dateFields'] = [];
    }
}
