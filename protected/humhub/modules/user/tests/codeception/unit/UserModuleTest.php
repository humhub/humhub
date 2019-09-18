<?php

namespace tests\codeception\unit;

use humhub\commands\CronController;
use humhub\commands\IntegrityController;
use humhub\modules\user\Events;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\permissions\ViewAboutPage;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

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

    public function testModuleEvents()
    {
        Yii::$app->search->rebuild();

        Events::onIntegrityCheck((new Event([
            'name' => IntegrityController::EVENT_ON_RUN,
            'sender' => new IntegrityController('integrity', Yii::$app)])
        ));

        Events::onHourlyCron((new Event(['name' => CronController::EVENT_ON_HOURLY_RUN])));
    }
}