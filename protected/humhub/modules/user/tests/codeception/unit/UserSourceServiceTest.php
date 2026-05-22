<?php

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;
use humhub\modules\user\source\GenericUserSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class UserSourceServiceTest extends HumHubDbTestCase
{
    // ---------------------------------------------------------------------------
    // canChangePassword — depends on allowedAuthClientIds
    // ---------------------------------------------------------------------------

    public function testLocalUserCanChangePassword(): void
    {
        $user = User::findOne(['email' => 'user1@example.com']);
        $this->assertNotNull($user);

        $this->assertTrue(UserSourceService::getForUser($user)->canChangePassword());
    }

    public function testCannotChangePasswordWhenLocalNotInAllowedList(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['allowedAuthClientIds' => ['oauth']]);

        $this->assertFalse(UserSourceService::getForUser($user)->canChangePassword());
    }

    public function testCanChangePasswordWhenLocalInAllowedList(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['allowedAuthClientIds' => ['oauth', 'local']]);

        $this->assertTrue(UserSourceService::getForUser($user)->canChangePassword());
    }

    public function testCanChangePasswordWhenAllowedListIsEmpty(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['allowedAuthClientIds' => []]);

        $this->assertTrue(UserSourceService::getForUser($user)->canChangePassword());
    }

    // ---------------------------------------------------------------------------
    // isAuthClientAllowed
    // ---------------------------------------------------------------------------

    public function testAllClientsAllowedWhenListIsEmpty(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['allowedAuthClientIds' => []]);
        $service = UserSourceService::getForUser($user);

        $this->assertTrue($service->isAuthClientAllowed('local'));
        $this->assertTrue($service->isAuthClientAllowed('oauth'));
        $this->assertTrue($service->isAuthClientAllowed('ldap'));
    }

    public function testAuthClientAllowedWhenInList(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['allowedAuthClientIds' => ['ldap', 'oauth']]);
        $service = UserSourceService::getForUser($user);

        $this->assertTrue($service->isAuthClientAllowed('ldap'));
        $this->assertTrue($service->isAuthClientAllowed('oauth'));
    }

    public function testAuthClientNotAllowedWhenNotInList(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['allowedAuthClientIds' => ['ldap']]);
        $service = UserSourceService::getForUser($user);

        $this->assertFalse($service->isAuthClientAllowed('local'));
        $this->assertFalse($service->isAuthClientAllowed('oauth'));
    }

    // ---------------------------------------------------------------------------
    // canChangeUsername / canChangeEmail — depend on managedAttributes
    // ---------------------------------------------------------------------------

    public function testCanChangeUsernameWhenNotManaged(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['managedAttributes' => ['firstname']]);

        $this->assertTrue(UserSourceService::getForUser($user)->canChangeUsername());
    }

    public function testCannotChangeUsernameWhenManaged(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['managedAttributes' => ['username', 'email']]);

        $this->assertFalse(UserSourceService::getForUser($user)->canChangeUsername());
    }

    public function testCanChangeEmailWhenNotManaged(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['managedAttributes' => ['firstname']]);

        $this->assertTrue(UserSourceService::getForUser($user)->canChangeEmail());
    }

    public function testCannotChangeEmailWhenManaged(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['managedAttributes' => ['username', 'email']]);

        $this->assertFalse(UserSourceService::getForUser($user)->canChangeEmail());
    }

    // ---------------------------------------------------------------------------
    // canDeleteAccount
    // ---------------------------------------------------------------------------

    public function testLocalUserCanDeleteAccount(): void
    {
        $user = User::findOne(['email' => 'user1@example.com']);
        $this->assertNotNull($user);

        $this->assertTrue(UserSourceService::getForUser($user)->canDeleteAccount());
    }

    public function testCannotDeleteAccountWhenSourceDisallows(): void
    {
        $user = $this->makeUserWithSource('ext-source', ['deleteAccount' => false]);

        $this->assertFalse(UserSourceService::getForUser($user)->canDeleteAccount());
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    /**
     * Registers a GenericUserSource under the given ID and returns a User
     * model instance (not persisted) with user_source set to that ID.
     */
    private function makeUserWithSource(string $sourceId, array $sourceConfig = []): User
    {
        Yii::$app->userSourceCollection->setUserSource(
            $sourceId,
            new GenericUserSource(array_merge(['id' => $sourceId], $sourceConfig)),
        );

        $user = new User();
        $user->user_source = $sourceId;

        return $user;
    }
}
