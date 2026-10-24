<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class PersonalAccessTokenServiceTest extends HumHubDbTestCase
{
    public function testCreateStoresOnlyTheHash()
    {
        $service = new PersonalAccessTokenService();
        [$token, $grant] = $service->create(User::findOne(['id' => 2]), 'CI script', ['content:read'], 30);

        $this->assertMatchesRegularExpression('/^hhpat_[A-Za-z0-9]{38}$/', $token);
        $this->assertSame(Grant::TYPE_PAT, $grant->type);
        $this->assertSame(hash('sha256', $token), $grant->token_hash);
        $this->assertSame(substr($token, -4), $grant->token_hint);
        $this->assertSame(['content:read'], $grant->getScopeIds());
        $this->assertEqualsWithDelta(time() + 30 * 86400, $grant->expires_at, 5);
        $this->assertNull(Grant::findOne(['token_hash' => $token]));
    }

    public function testChecksumDetectsTypos()
    {
        $service = new PersonalAccessTokenService();
        [$token] = $service->create(User::findOne(['id' => 2]), 'x', ['content:read'], 30);

        $this->assertTrue(PersonalAccessTokenService::isWellFormed($token));
        $typo = substr($token, 0, 10) . ($token[10] === 'a' ? 'b' : 'a') . substr($token, 11);
        $this->assertFalse(PersonalAccessTokenService::isWellFormed($typo));
        $this->assertFalse(PersonalAccessTokenService::isWellFormed('hhpat_short'));
    }

    public function testFindReturnsTheGrant()
    {
        Module::instance()->settings->set('patMaxLifetimeDays', 0);
        $service = new PersonalAccessTokenService();
        [$token, $grant] = $service->create(User::findOne(['id' => 2]), 'x', ['content:read'], null);

        $this->assertSame($grant->id, $service->find($token)?->id);
        $this->assertNull($grant->expires_at);
        $this->assertNull($service->find('hhpat_' . str_repeat('A', 38)));
    }

    public function testLifetimeIsCappedByTheSetting()
    {
        Module::instance()->settings->set('patMaxLifetimeDays', 90);
        $service = new PersonalAccessTokenService();

        $this->assertSame([7, 30, 90], $service->getLifetimeOptions());
        $this->expectException(\yii\base\InvalidArgumentException::class);
        $service->create(User::findOne(['id' => 2]), 'x', ['content:read'], 365);
    }

    public function testNeverIsOfferedOnlyWithoutALimit()
    {
        Module::instance()->settings->set('patMaxLifetimeDays', 0);
        $this->assertSame([7, 30, 90, 365, null], (new PersonalAccessTokenService())->getLifetimeOptions());
    }
}
