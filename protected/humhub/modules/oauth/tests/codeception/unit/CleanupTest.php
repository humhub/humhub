<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\Events;
use humhub\modules\oauth\models\AccessToken;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class CleanupTest extends HumHubDbTestCase
{
    private function grant(): Grant
    {
        return (new GrantService())->createOAuthGrant(User::findOne(['id' => 1]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);
    }

    public function testExpiredAndAbandonedRecordsAreRemoved()
    {
        $used = $this->grant();
        $used->updateAttributes(['last_used_at' => time()]);
        (new AccessToken(['id' => 'expired', 'grant_id' => $used->id, 'expires_at' => time() - 10, 'is_revoked' => 0]))->save(false);
        (new AccessToken(['id' => 'valid', 'grant_id' => $used->id, 'expires_at' => time() + 3600, 'is_revoked' => 0]))->save(false);

        $expired = $this->grant();
        $expired->updateAttributes(['last_used_at' => time(), 'expires_at' => time() - 10]);

        $abandoned = $this->grant();
        $abandoned->updateAttributes(['created_at' => time() - 7200]);

        $fresh = $this->grant();

        Events::onCronDailyRun(null);

        $this->assertNull(AccessToken::findOne(['id' => 'expired']));
        $this->assertNotNull(AccessToken::findOne(['id' => 'valid']));
        $this->assertNotNull(Grant::findOne(['id' => $used->id]));
        $this->assertNull(Grant::findOne(['id' => $expired->id]));
        $this->assertNull(Grant::findOne(['id' => $abandoned->id]));
        $this->assertNotNull(Grant::findOne(['id' => $fresh->id]), 'a code may still be exchanged within the hour');
    }
}
