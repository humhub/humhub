<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\authclient\BaseFormClient;
use humhub\modules\user\authclient\Collection;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use humhub\modules\user\models\forms\LoginIdentity;
use humhub\modules\user\source\GenericUserSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\authclient\BaseClient;

/**
 * Covers the Step-1 → Step-2 routing decision in the new two-step login flow.
 *
 * @since 1.19
 */
class LoginIdentityTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The authClientCollection persists across tests in the same Yii::$app
        // instance. Reset it to a known-empty state so each test only sees the
        // clients it explicitly registers.
        Yii::$app->set('authClientCollection', [
            'class' => Collection::class,
            'clients' => [],
        ]);
    }

    public function testEmptyUsernameReturnsNull(): void
    {
        $login = new LoginIdentity(['username' => '']);

        $this->assertNull($login->getStep1Redirect());
    }

    public function testUnknownUserReturnsNull(): void
    {
        $login = new LoginIdentity(['username' => 'nope-no-such-user']);

        $this->assertNull($login->getStep1Redirect());
    }

    public function testUserWithLocalPasswordReturnsNull(): void
    {
        // User1 (id=2) has a local password and the default `local` source.
        $login = new LoginIdentity(['username' => 'User1']);

        $this->assertNull($login->getStep1Redirect());
    }

    public function testUserWithoutPasswordButLinkedOAuthRedirectsToProvider(): void
    {
        Password::deleteAll(['user_id' => 2]);
        $this->registerAuthClient('oauth-google', new ExternalAuthClientStub());
        $this->linkUserToAuthClient(2, 'oauth-google');

        $login = new LoginIdentity(['username' => 'User1']);

        $this->assertSame(
            ['/user/auth/external', 'authclient' => 'oauth-google'],
            $login->getStep1Redirect(),
        );
    }

    public function testMostRecentlyLinkedExternalAuthClientWins(): void
    {
        Password::deleteAll(['user_id' => 2]);
        $this->registerAuthClient('oauth-old', new ExternalAuthClientStub());
        $this->registerAuthClient('oauth-new', new ExternalAuthClientStub());
        $this->linkUserToAuthClient(2, 'oauth-old');
        $this->linkUserToAuthClient(2, 'oauth-new');

        $login = new LoginIdentity(['username' => 'User1']);

        $this->assertSame(
            ['/user/auth/external', 'authclient' => 'oauth-new'],
            $login->getStep1Redirect(),
        );
    }

    public function testFormAuthOnlyLinkedReturnsNull(): void
    {
        // Since 1.19 LDAP also writes user_auth rows for DN tracking. Those
        // rows must NOT trigger the OAuth-redirect branch — the user belongs
        // on the password screen so the LDAP form handler can claim them.
        Password::deleteAll(['user_id' => 2]);
        $this->registerAuthClient('ldap', new BaseFormClient(['id' => 'ldap']));
        $this->linkUserToAuthClient(2, 'ldap');

        $login = new LoginIdentity(['username' => 'User1']);

        $this->assertNull($login->getStep1Redirect());
    }

    public function testUnknownAuthClientSourceIsSkipped(): void
    {
        // A stale user_auth row pointing at a no-longer-registered client
        // must not crash the lookup. The next-newest valid row should win.
        Password::deleteAll(['user_id' => 2]);
        $this->registerAuthClient('oauth-google', new ExternalAuthClientStub());
        $this->linkUserToAuthClient(2, 'oauth-google');
        $this->linkUserToAuthClient(2, 'oauth-removed-provider');

        $login = new LoginIdentity(['username' => 'User1']);

        $this->assertSame(
            ['/user/auth/external', 'authclient' => 'oauth-google'],
            $login->getStep1Redirect(),
        );
    }

    public function testUserSourceWithoutFormAuthRedirectsToFirstAllowedClient(): void
    {
        // SAML/OIDC-only source: the source allows only an external client,
        // so Step 1 should not bother with the password screen at all.
        $this->registerAuthClient('saml', new ExternalAuthClientStub());
        Yii::$app->userSourceCollection->setUserSource(
            'saml-source',
            new GenericUserSource([
                'id' => 'saml-source',
                'allowedAuthClientIds' => ['saml'],
            ]),
        );

        $user = User::findOne(['username' => 'User1']);
        $user->user_source = 'saml-source';
        $user->save(false);

        $login = new LoginIdentity(['username' => 'User1']);

        $this->assertSame(
            ['/user/auth/external', 'authclient' => 'saml'],
            $login->getStep1Redirect(),
        );
    }

    public function testUserSourceWithFormAuthAndPasswordReturnsNull(): void
    {
        // Mixed source (form + external both allowed) and the user has a
        // password → Step 2 wins; the password screen is the right answer.
        $this->registerAuthClient('local', new BaseFormClient(['id' => 'local']));
        $this->registerAuthClient('saml', new ExternalAuthClientStub());
        Yii::$app->userSourceCollection->setUserSource(
            'mixed',
            new GenericUserSource([
                'id' => 'mixed',
                'allowedAuthClientIds' => ['local', 'saml'],
            ]),
        );

        $user = User::findOne(['username' => 'User1']);
        $user->user_source = 'mixed';
        $user->save(false);

        $login = new LoginIdentity(['username' => 'User1']);

        $this->assertNull($login->getStep1Redirect());
    }

    private function registerAuthClient(string $id, BaseClient $client): void
    {
        $client->setId($id);
        Yii::$app->authClientCollection->setClient($id, $client);
    }

    private function linkUserToAuthClient(int $userId, string $source): void
    {
        $auth = new Auth([
            'user_id' => $userId,
            'source' => $source,
            'source_id' => $source . '-' . $userId,
        ]);
        $auth->save(false);
    }
}

/**
 * Minimal concrete BaseClient that is *not* a BaseFormClient — used to stand in
 * for an OAuth/SAML provider in the redirect-routing tests.
 */
class ExternalAuthClientStub extends BaseClient
{
    protected function initUserAttributes()
    {
        return [];
    }
}
