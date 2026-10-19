<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\authclient\Password;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\User;
use humhub\modules\user\services\AuthClientService;
use humhub\modules\user\services\AuthClientUserService;
use humhub\modules\user\services\UserSourceService;
use humhub\modules\user\source\GenericUserSource;
use humhub\modules\user\source\LocalUserSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Password login of users that belong to a UserSource other than "local".
 *
 * A source allows its users to log in with their local password by listing
 * the Password client ("local") in allowedAuthClientIds, or by an empty list
 * (all clients allowed).
 *
 * @since 1.19.1
 */
class PasswordLoginUserSourceTest extends HumHubDbTestCase
{
    /**
     * Password of the fixture users User1, User2, ...
     */
    private const PASSWORD = 'user^humhub@PASS%worD!';

    private function registerSource(string $id, array $allowedAuthClientIds): void
    {
        Yii::$app->userSourceCollection->setUserSource($id, new GenericUserSource([
            'id' => $id,
            'allowedAuthClientIds' => $allowedAuthClientIds,
        ]));
    }

    private function moveToSource(string $username, string $sourceId): User
    {
        $user = User::findOne(['username' => $username]);
        $user->updateAttributes(['user_source' => $sourceId]);

        return $user;
    }

    private function createPasswordClient(string $login): Password
    {
        $client = new Password();
        $client->login = new Login(['username' => $login, 'password' => self::PASSWORD]);

        return $client;
    }

    private function logIn(string $login, string $password = self::PASSWORD): ?User
    {
        $client = $this->createPasswordClient($login);

        if (!$client->authenticate($login, $password)) {
            return null;
        }

        return (new AuthClientService($client))->getUser();
    }

    public function testLocalUserLogsIn(): void
    {
        $this->assertSame('User1', $this->logIn('User1')?->username);
        $this->assertSame('User1', $this->logIn('user1@example.com')?->username);
    }

    public function testUserOfSourceAllowingPasswordLogsIn(): void
    {
        $this->registerSource('hr', ['local']);
        $this->moveToSource('User1', 'hr');

        $this->assertSame('User1', $this->logIn('User1')?->username);
        $this->assertSame('User1', $this->logIn('user1@example.com')?->username);
    }

    public function testUserOfSourceAllowingAllClientsLogsIn(): void
    {
        $this->registerSource('hr', []);
        $this->moveToSource('User1', 'hr');

        $this->assertSame('User1', $this->logIn('User1')?->username);
    }

    public function testUserOfSourceWithoutPasswordLoginIsRejected(): void
    {
        $this->registerSource('sso', ['oauth']);
        $user = $this->moveToSource('User1', 'sso');

        $this->assertNull($this->logIn('User1'));
        $this->assertNull($this->createPasswordClient('User1')->getUserByLogin());
    }

    public function testRejectedSourceDoesNotCountFailedAttempts(): void
    {
        // e.g. an LDAP user: the login form tries the Password client before the LDAP client
        $this->registerSource('directory', ['directory']);
        $user = $this->moveToSource('User1', 'directory');

        $this->assertFalse($this->createPasswordClient('User1')->authenticate('User1', 'wrong'));

        $this->assertNull($user->getSettings()->get('failedLoginAttemptsCount'));
    }

    public function testWrongPasswordIsRejected(): void
    {
        $this->registerSource('hr', ['local']);
        $this->moveToSource('User1', 'hr');

        $this->assertNull($this->logIn('User1', 'wrong'));
    }

    public function testUserOfUnknownSourceFallsBackToLocal(): void
    {
        // a source whose module was removed: UserSourceService falls back to the local source
        $this->moveToSource('User1', 'removed-module-source');

        $this->assertSame('User1', $this->logIn('User1')?->username);
    }

    public function testDisabledUserIsFoundSoTheStatusCanBeReported(): void
    {
        $this->registerSource('hr', ['local']);
        $this->moveToSource('DisabledUser', 'hr');

        $user = $this->logIn('DisabledUser');

        $this->assertNotNull($user);
        $this->assertSame(User::STATUS_DEACTIVATED, (int) $user->status);
    }

    public function testUsernameMatchWinsOverEmailMatch(): void
    {
        // usernames may contain "@", so one user's username can be another user's email
        $other = User::findOne(['username' => 'User2']);
        $other->updateAttributes(['username' => 'user1@example.com']);

        $this->assertSame($other->id, $this->createPasswordClient('user1@example.com')->getUserByLogin()?->id);
    }

    public function testLocalIsAlwaysTheSourceOfThePasswordClient(): void
    {
        $this->registerSource('hr', ['local', 'oauth']);
        $collection = UserSourceService::getCollection();

        $this->assertInstanceOf(LocalUserSource::class, $collection->findUserSourceForAuthClient('local'));
        $this->assertFalse($collection->isAuthClientClaimed('local'), 'the password client is no trusted provider');

        // other clients are claimed as before
        $this->assertSame('hr', $collection->findUserSourceForAuthClient('oauth')->getId());
        $this->assertTrue($collection->isAuthClientClaimed('oauth'));
    }

    public function testLocalUsersStillLogInWhenAnotherSourceAllowsPassword(): void
    {
        $this->registerSource('hr', ['local']);

        $this->assertSame('User2', $this->logIn('User2')?->username);
    }

    public function testPasswordClientIsAnAuthClientOfUsersAllowedToUseIt(): void
    {
        Yii::$app->authClientCollection->setClient('local', new Password());
        $this->registerSource('hr', ['local']);
        $this->registerSource('sso', ['oauth']);

        $clientIds = static fn(User $user) => array_map(
            static fn($client) => $client->getId(),
            (new AuthClientUserService($user))->getClients(),
        );

        $this->assertContains('local', $clientIds($this->moveToSource('User1', 'hr')));
        $this->assertNotContains('local', $clientIds($this->moveToSource('User2', 'sso')));
        $this->assertSame(['local'], $clientIds(User::findOne(['username' => 'User3'])), 'local users list it once');
    }
}
