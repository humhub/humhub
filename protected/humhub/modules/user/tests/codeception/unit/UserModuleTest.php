<?php

namespace tests\codeception\unit\models;

use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\permissions\ViewAboutPage;
use tests\codeception\_support\HumHubDbTestCase;

class UserModuleTest extends HumHubDbTestCase
{
    public function testModuleMethods()
    {
        $module = new Module('user');

        $this->assertEquals([new ViewAboutPage()], $module->getPermissions(new User()));
        $this->assertEquals([], $module->getPermissions());

        $this->assertEquals('User', $module->getName());

        $this->assertEquals([
            'humhub\modules\user\notifications\Followed',
            'humhub\modules\user\notifications\Mentioned'
        ], $module->getNotifications());

        $this->assertEquals([
            '/^.{5,255}$/' => 'Password needs to be at least 8 characters long.',
        ], $module->getPasswordStrength());

        $module->passwordStrength = [
            '/^$/' => 'test'
        ];

        $this->assertTrue($module->isCustomPasswordStrength());

        $this->assertEquals([
            '/^$/' => 'test'
        ], $module->getPasswordStrength());
    }
}
