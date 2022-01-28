<?php

namespace tests\codeception\unit;

use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ImpersonateTest extends HumHubDbTestCase
{

    public function testImpersonateByAdmin()
    {
        $this->becomeUser('Admin');

        $this->assertTrue($this->impersonate('User1'));

        $this->assertEquals('Admin', $this->getImpersonator()->username);

        $this->assertTrue($this->stopImpersonation());

        $this->assertFalse(Yii::$app->user->isImpersonated);
    }

    public function testImpersonateByUserWithoutPermission()
    {
        $this->becomeUser('User3');

        $this->assertFalse(Yii::$app->user->isImpersonated);

        $this->assertFalse($this->impersonate('User2'));

        $this->assertFalse(Yii::$app->user->isImpersonated);
    }

    private function impersonate(string $userName): bool
    {
        $this->assertFalse(Yii::$app->user->isImpersonated);

        $user = User::findOne(['username' => $userName]);

        return Yii::$app->user->impersonate($user);
    }

    private function getImpersonator(): User
    {
        $this->assertTrue(Yii::$app->user->isImpersonated);

        return Yii::$app->user->getImpersonator();
    }

    private function stopImpersonation(): bool
    {
        $this->assertTrue(Yii::$app->user->isImpersonated);

        return Yii::$app->user->restoreImpersonator();
    }
}
