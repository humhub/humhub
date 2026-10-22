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
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class PasswordChangeTest extends HumHubDbTestCase
{
    protected function _before(): void
    {
        parent::_before();
        Grant::deleteAll();
        Module::instance()->settings->set('patMaxLifetimeDays', 0);
    }

    protected function _after(): void
    {
        Module::instance()->settings->delete('revokeOnPasswordChange');
        Grant::deleteAll();
        parent::_after();
    }

    private function createGrants(int $userId): void
    {
        $user = User::findOne(['id' => $userId]);
        (new GrantService())->createOAuthGrant($user, Client::findOne(['client_id' => Client::SYSTEM_CLIENT_ID]), ['full']);
        (new PersonalAccessTokenService())->create($user, 'CI', ['content:read'], 30);
    }

    private function changePassword(int $userId): void
    {
        $password = new Password(['user_id' => $userId]);
        $password->setPassword('NewPass123!');
        $this->assertTrue($password->save());
    }

    public function testChangeRevokesAllGrantsOfTheUserOnly()
    {
        Module::instance()->settings->set('revokeOnPasswordChange', true);
        $this->createGrants(2);
        $this->createGrants(3);
        $this->assertSame(4, (int)Grant::find()->count());

        $this->changePassword(2);

        $this->assertSame(0, (int)Grant::find()->where(['user_id' => 2])->count());
        $this->assertSame(2, (int)Grant::find()->where(['user_id' => 3])->count());
    }

    public function testGrantsStayWhenTheSettingIsOff()
    {
        $this->createGrants(2);
        $this->changePassword(2);

        $this->assertSame(2, (int)Grant::find()->where(['user_id' => 2])->count());
    }

    public function testFirstPasswordRevokesNothing()
    {
        Module::instance()->settings->set('revokeOnPasswordChange', true);
        Password::deleteAll(['user_id' => 2]);
        $this->createGrants(2);

        $this->changePassword(2);

        $this->assertSame(2, (int)Grant::find()->where(['user_id' => 2])->count());
    }
}
