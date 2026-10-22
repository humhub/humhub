<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class GrantServiceTest extends HumHubDbTestCase
{
    private function createClient(string $type, array $scopes, ?int $ownerId = null): Client
    {
        $client = new Client([
            'client_id' => bin2hex(random_bytes(16)),
            'type' => $type,
            'name' => 'Test ' . $type,
            'owner_user_id' => $ownerId,
            'redirect_uris' => json_encode(['https://example.org/cb']),
            'scopes' => json_encode($scopes),
            'is_active' => 1,
            'created_at' => time(),
        ]);
        $this->assertTrue($client->save(false));
        return $client;
    }

    public function testSystemClientGrantIsUsableWithFullScope()
    {
        $service = new GrantService();
        $grant = $service->createOAuthGrant(User::findOne(['id' => 1]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);

        $this->assertNotEmpty($grant->guid);
        $this->assertTrue($service->isUsable($grant));
        $this->assertSame(['full'], $service->getEffectiveScopes($grant));
    }

    public function testAdminClientScopesAreIntersectedWithTheClientsAllowedScopes()
    {
        Module::instance()->settings->set('allowClients', true);
        $client = $this->createClient(Client::TYPE_ADMIN, ['content:read']);
        $service = new GrantService();

        $grant = $service->createOAuthGrant(User::findOne(['id' => 1]), $client, ['content:read', 'content:write', 'unknown:scope']);

        $this->assertSame(['content:read'], $service->getEffectiveScopes($grant));
    }

    public function testUserClientsNeverHoldFullAndAreBoundedBySettings()
    {
        Module::instance()->settings->set('allowUserClients', true);
        Module::instance()->settings->set('userClientScopes', json_encode(['content:read']));
        $client = $this->createClient(Client::TYPE_USER, ['full', 'content:read', 'spaces:read'], 2);
        $service = new GrantService();

        $grant = $service->createOAuthGrant(User::findOne(['id' => 2]), $client, ['full', 'content:read', 'spaces:read']);

        $this->assertSame(['content:read'], $service->getEffectiveScopes($grant));
    }

    public function testDisallowedClientTypesDisabledUsersAndExpiryMakeAGrantUnusable()
    {
        $service = new GrantService();
        $client = $this->createClient(Client::TYPE_ADMIN, ['content:read']);
        $grant = $service->createOAuthGrant(User::findOne(['id' => 1]), $client, ['content:read']);

        Module::instance()->settings->set('allowClients', false);
        $this->assertFalse($service->isUsable($grant));

        Module::instance()->settings->set('allowClients', true);
        $this->assertTrue($service->isUsable($grant));

        $grant->updateAttributes(['expires_at' => time() - 1]);
        $this->assertFalse($service->isUsable($grant));

        $grant->updateAttributes(['expires_at' => null]);
        User::findOne(['id' => 1])->updateAttributes(['status' => User::STATUS_DISABLED]);
        $this->assertFalse($service->isUsable(Grant::findOne(['id' => $grant->id])));
    }

    public function testGrantMaxLifetimeSetsTheExpiry()
    {
        Module::instance()->settings->set('grantMaxLifetimeDays', 30);
        $grant = (new GrantService())->createOAuthGrant(User::findOne(['id' => 1]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);

        $this->assertEqualsWithDelta(time() + 30 * 86400, $grant->expires_at, 5);
    }

    public function testRevokeDeletesTheGrant()
    {
        $service = new GrantService();
        $grant = $service->createOAuthGrant(User::findOne(['id' => 1]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);

        $service->revoke($grant);

        $this->assertNull(Grant::findOne(['id' => $grant->id]));
    }
}
