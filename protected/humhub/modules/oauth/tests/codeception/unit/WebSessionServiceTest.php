<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\WebSessionCode;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\WebSessionService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class WebSessionServiceTest extends HumHubDbTestCase
{
    private function appGrant()
    {
        return (new GrantService())->createOAuthGrant(User::findOne(['id' => 2]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);
    }

    public function testCodeIsStoredHashedAndRedeemedOnce()
    {
        $service = new WebSessionService();
        $grant = $this->appGrant();

        $code = $service->createCode($grant);

        $this->assertNull(WebSessionCode::findOne(['code_hash' => $code]), 'the code itself is never stored');
        $this->assertNotNull(WebSessionCode::findOne(['code_hash' => hash('sha256', $code)]));
        $this->assertSame($grant->id, $service->redeem($code)?->id);
        $this->assertNull($service->redeem($code), 'single use');
    }

    public function testExpiredCodesAndUnusableGrantsAreRejected()
    {
        $service = new WebSessionService();
        $grant = $this->appGrant();

        $code = $service->createCode($grant);
        WebSessionCode::updateAll(['expires_at' => time() - 1], ['grant_id' => $grant->id]);
        $this->assertNull($service->redeem($code));

        $code = $service->createCode($grant);
        User::findOne(['id' => 2])->updateAttributes(['status' => User::STATUS_DISABLED]);
        $this->assertNull($service->redeem($code));
    }

    public function testOnlyAppGrantsGetCodes()
    {
        \humhub\modules\oauth\Module::instance()->settings->set('allowClients', true);
        $client = new Client([
            'client_id' => 'agent-' . bin2hex(random_bytes(4)), 'type' => Client::TYPE_ADMIN, 'name' => 'Agent',
            'redirect_uris' => '[]', 'scopes' => json_encode(['full']), 'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);
        $grant = (new GrantService())->createOAuthGrant(User::findOne(['id' => 1]), $client, ['full']);

        $this->expectException(\yii\base\InvalidArgumentException::class);
        (new WebSessionService())->createCode($grant);
    }

    public function testRedeemRejectsCodesOfNonAppGrants()
    {
        \humhub\modules\oauth\Module::instance()->settings->set('allowClients', true);
        $client = new Client([
            'client_id' => 'agent-' . bin2hex(random_bytes(4)), 'type' => Client::TYPE_ADMIN, 'name' => 'Agent',
            'redirect_uris' => '[]', 'scopes' => json_encode(['full']), 'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);
        $grant = (new GrantService())->createOAuthGrant(User::findOne(['id' => 1]), $client, ['full']);

        (new WebSessionCode(['code_hash' => hash('sha256', 'foreign'), 'grant_id' => $grant->id, 'expires_at' => time() + 60]))->save(false);

        $this->assertNull((new WebSessionService())->redeem('foreign'));
    }

    public function testBoundGrantIdIsNullWithoutSession()
    {
        $this->assertNull((new WebSessionService())->getBoundGrantId());
    }
}
