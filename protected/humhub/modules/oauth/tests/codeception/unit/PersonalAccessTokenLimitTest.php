<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\helpers\EnvHelper;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class PersonalAccessTokenLimitTest extends HumHubDbTestCase
{
    protected function _after()
    {
        Module::instance()->maxPersonalAccessTokensPerUser = null;
        Module::instance()->settings->delete('maxPersonalAccessTokensPerUser');
        Grant::deleteAll();
        parent::_after();
    }

    public function testPrecedence()
    {
        $module = Module::instance();
        $this->assertSame(3, $module->getMaxPersonalAccessTokensPerUser());

        $module->settings->set('maxPersonalAccessTokensPerUser', 5);
        $this->assertSame(5, $module->getMaxPersonalAccessTokensPerUser());

        $module->maxPersonalAccessTokensPerUser = 2;
        $this->assertTrue($module->isMaxPersonalAccessTokensPerUserConfigured());
        $this->assertSame(2, $module->getMaxPersonalAccessTokensPerUser());

        // 0 in the configuration is a value, not "unset"
        $module->maxPersonalAccessTokensPerUser = 0;
        $this->assertSame(0, $module->getMaxPersonalAccessTokensPerUser());
    }

    public function testTheEnvironmentVariableSetsTheProperty()
    {
        $config = EnvHelper::toConfig(['HUMHUB_CONFIG__MODULES__OAUTH__MAX_PERSONAL_ACCESS_TOKENS_PER_USER' => '5']);
        $this->assertEquals(5, $config['modules']['oauth']['maxPersonalAccessTokensPerUser']);
    }

    public function testOnlyUnexpiredTokensCount()
    {
        $user = User::findOne(['id' => 2]);
        $service = new PersonalAccessTokenService();
        Module::instance()->maxPersonalAccessTokensPerUser = 2;

        [, $first] = $service->create($user, 'a', ['content:read'], 30);
        $this->assertFalse($service->hasReachedLimit($user));
        [, $second] = $service->create($user, 'b', ['content:read'], 30);
        $this->assertTrue($service->hasReachedLimit($user));
        $this->assertFalse($service->hasReachedLimit(User::findOne(['id' => 3])));

        $first->updateAttributes(['expires_at' => time() - 10]);
        $this->assertFalse($service->hasReachedLimit($user));

        Module::instance()->maxPersonalAccessTokensPerUser = 0;
        $second->updateAttributes(['expires_at' => null]);
        $service->create($user, 'c', ['content:read'], 30);
        $this->assertFalse($service->hasReachedLimit($user));
    }
}
