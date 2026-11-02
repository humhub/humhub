<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\connection\LdapConnectionRegistry;
use humhub\modules\ldap\Module;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\authclient\Password;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\services\AuthClientService;
use humhub\modules\user\source\GenericUserSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\authclient\BaseClient;

/**
 * Tests which auth clients may write the LDAP-managed attributes of an LDAP
 * user on login ({@see AuthClientService::updateUser()}).
 *
 * Regression for https://github.com/humhub/humhub/issues/8669: auth clients
 * that are allowed only for authentication (JWT, password, ...) cleared the
 * LDAP-managed profile fields, because their attribute set lacks them.
 *
 * Fixture profile fields map firstname => givenName and lastname => sn.
 * No real LDAP server is required.
 */
class LdapAttributeSyncTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    private User $user;

    protected function _before(): void
    {
        parent::_before();

        $registry = new LdapConnectionRegistry();
        $registry->setConfigs([
            'ldap' => new LdapConnectionConfig([
                'usernameAttribute' => 'uid',
                'emailAttribute' => 'mail',
                'idAttribute' => 'uid',
            ]),
            'ldap2' => new LdapConnectionConfig([
                'usernameAttribute' => 'uid',
                'emailAttribute' => 'mail',
                'idAttribute' => 'uid',
            ]),
        ]);
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        $module->setConnectionRegistry($registry);

        // Fixture user "User1" (Peter Tester), moved into the LDAP source
        $this->user = User::findOne(['username' => 'User1']);
        $this->user->updateAttributes(['user_source' => 'ldap']);
    }

    public function testJwtClientDoesNotClearLdapProfileFields(): void
    {
        $this->installSource(['allowedAuthClientIds' => ['ldap', 'jwt']]);

        $this->assertTrue($this->updateUser($this->makeClient('jwt', ['id' => 'User1', 'username' => 'User1'])));

        $this->assertProfile('Peter', 'Tester');
    }

    public function testJwtClientDoesNotOverwriteLdapProfileFields(): void
    {
        $this->installSource(['allowedAuthClientIds' => ['ldap', 'jwt']]);

        $this->updateUser($this->makeClient('jwt', ['id' => 'User1', 'firstname' => 'Other', 'lastname' => 'Name']));

        $this->assertProfile('Peter', 'Tester');
    }

    public function testPasswordClientDoesNotClearLdapProfileFields(): void
    {
        $this->installSource(['allowedAuthClientIds' => ['ldap', 'local']]);

        $password = new Password();
        $password->setUserAttributes(['id' => $this->user->id]);

        $this->assertTrue($this->updateUser($password));

        $this->assertProfile('Peter', 'Tester');
    }

    public function testOwnLdapClientUpdatesProfileFields(): void
    {
        $this->installSource();

        $this->updateUser($this->makeLdapClient(['givenname' => 'John', 'sn' => 'Doe']));

        $this->assertProfile('John', 'Doe');
    }

    public function testOwnLdapClientClearsFieldsRemovedInDirectory(): void
    {
        ProfileField::updateAll(['ldap_attribute' => 'mobile'], ['internal_name' => 'mobile']);
        $this->user->profile->updateAttributes(['mobile' => '+49 123 456']);
        $this->installSource();

        $this->updateUser($this->makeLdapClient(['givenname' => 'John', 'sn' => 'Doe']));

        $this->assertProfile('John', 'Doe');
        $this->assertSame('', (string) User::findOne($this->user->id)->profile->mobile);
    }

    public function testLdapClientOfOtherConnectionDoesNotUpdate(): void
    {
        $this->installSource(['allowedAuthClientIds' => ['ldap', 'ldap2']]);

        $client = $this->makeLdapClient(['givenname' => 'John', 'sn' => 'Doe']);
        $client->clientId = 'ldap2';
        $client->connectionId = 'ldap2';

        $this->updateUser($client);

        $this->assertProfile('Peter', 'Tester');
    }

    public function testOtherSourcesAcceptAllowedClientsByDefault(): void
    {
        $this->installGenericSource(['jwt']);

        $this->updateUser($this->makeClient('jwt', ['id' => 'User1', 'firstname' => 'John', 'lastname' => 'Doe']));

        $this->assertProfile('John', 'Doe');
    }

    public function testOtherSourcesIgnoreNotAllowedClients(): void
    {
        $this->installGenericSource(['saml']);

        $this->updateUser($this->makeClient('jwt', ['id' => 'User1', 'firstname' => 'John', 'lastname' => 'Doe']));

        $this->assertProfile('Peter', 'Tester');
    }

    private function installSource(array $config = []): void
    {
        $source = new LdapUserSource(array_merge(['connectionId' => 'ldap'], $config));
        Yii::$app->userSourceCollection->setUserSource('ldap', $source);
    }

    private function installGenericSource(array $allowedAuthClientIds): void
    {
        Yii::$app->userSourceCollection->setUserSource('generic', new GenericUserSource([
            'id' => 'generic',
            'managedAttributes' => ['firstname', 'lastname'],
            'allowedAuthClientIds' => $allowedAuthClientIds,
        ]));
        $this->user->updateAttributes(['user_source' => 'generic']);
    }

    private function updateUser(BaseClient $client): bool
    {
        return (new AuthClientService($client))->updateUser($this->user);
    }

    private function makeClient(string $id, array $attributes): BaseClient
    {
        $client = new class extends BaseClient {
            protected function initUserAttributes()
            {
                return [];
            }
        };
        $client->setId($id);
        $client->setUserAttributes($attributes);

        return $client;
    }

    private function makeLdapClient(array $profileAttributes): LdapAuth
    {
        $client = new LdapAuth(['connectionId' => 'ldap']);
        $client->setUserAttributes(array_merge([
            'dn' => 'uid=user1,ou=people,dc=example,dc=com',
            'uid' => 'User1',
            'mail' => 'user1@example.com',
        ], $profileAttributes));

        return $client;
    }

    private function assertProfile(string $firstname, string $lastname): void
    {
        $profile = User::findOne($this->user->id)->profile;
        $this->assertSame($firstname, (string) $profile->firstname);
        $this->assertSame($lastname, (string) $profile->lastname);
    }
}
