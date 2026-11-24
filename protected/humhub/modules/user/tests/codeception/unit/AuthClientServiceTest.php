<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use humhub\modules\user\services\AuthClientService;
use humhub\modules\user\source\GenericUserSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\authclient\BaseClient;

/**
 * User lookup of an auth client that has the same ID as its user source.
 */
class AuthClientServiceTest extends HumHubDbTestCase
{
    private const SOURCE_ID = 'test-source';

    private const EXTERNAL_ID = '3cd37aa1-5f0e-4b6c-9d0a-6f1c2e8b4a10';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Yii::$app->userSourceCollection->setUserSource(self::SOURCE_ID, new GenericUserSource([
            'id' => self::SOURCE_ID,
            'allowedAuthClientIds' => [self::SOURCE_ID],
        ]));

        // User2 has the ID 3
        $this->user = User::findOne(['username' => 'User2']);
        $this->user->updateAttributes(['user_source' => self::SOURCE_ID]);
    }

    private function getUser(string $externalId): ?User
    {
        $client = new class extends BaseClient {
            protected function initUserAttributes()
            {
                return [];
            }
        };
        $client->setId(self::SOURCE_ID);
        $client->setUserAttributes(['id' => $externalId]);

        return (new AuthClientService($client))->getUser();
    }

    public function testUserIsFoundByAuthRecord(): void
    {
        (new Auth(['user_id' => $this->user->id, 'source' => self::SOURCE_ID, 'source_id' => self::EXTERNAL_ID]))->save();

        $this->assertSame($this->user->id, $this->getUser(self::EXTERNAL_ID)?->id);
    }

    public function testExternalIdIsNotUsedAsUserId(): void
    {
        // MySQL compares the ID '3cd37aa1-...' with the integer column user.id as 3
        $this->assertNull($this->getUser(self::EXTERNAL_ID));
    }

    public function testNumericExternalIdIsNotUsedAsUserId(): void
    {
        // e.g. the LDAP uidNumber or an employee number
        $this->assertNull($this->getUser('3'));
    }
}
