<?php

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use humhub\modules\user\services\PasswordRecoveryService;
use tests\codeception\_support\HumHubDbTestCase;

class PasswordRecoveryServiceTest extends HumHubDbTestCase
{
    public function testSendPasswordRecoveryEmail()
    {
        $service = $this->getServiceByUserEmail('user1@example.com');

        $this->assertNull($service->getSavedToken());
        $this->assertFalse($service->isLimited());
        $this->assertTrue($service->sendRecoveryInfo());
        $this->assertTrue($service->isLimited());

        // Check that another user is not limited because he doesn't request a recovery yet
        $serviceUser2 = $this->getServiceByUserEmail('user2@example.com');
        $this->assertFalse($serviceUser2->isLimited());
    }

    public function testResetPassword()
    {
        $service = $this->getServiceByUserEmail('user1@example.com');

        $this->assertTrue($service->sendRecoveryInfo());

        $savedToken = $service->getSavedToken();
        $this->assertIsArray($savedToken);
        $this->assertArrayHasKey('key', $savedToken);
        $this->assertArrayHasKey('time', $savedToken);

        $this->assertFalse($service->checkToken('wrong-key'));
        $this->assertTrue($service->checkToken($savedToken['key']));

        $password = new Password();
        $this->assertTrue($password->load(['Password' => [
            'newPassword' => '123QWE!@',
            'newPasswordConfirm' => '123QWE!@',
        ]]));
        $this->assertTrue($service->reset($password));

        $this->assertNull($service->getSavedToken());
    }

    private function getServiceByUserEmail(string $email): PasswordRecoveryService
    {
        $user = User::findOne(['email' => $email]);
        $this->assertNotNull($user);

        $service = $user->getPasswordRecoveryService();
        $this->assertInstanceOf(PasswordRecoveryService::class, $service);

        return $service;
    }
}
