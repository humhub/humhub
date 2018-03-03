<?php

class ModuleManagerTest extends \tests\codeception\_support\HumHubDbTestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testModuleManagerDefaultApi() {
        $this->assertInstanceOf(\humhub\components\ModuleManager::class, Yii::$app->moduleManager);
        $this->assertInstanceOf(\humhub\modules\activity\Module::class, Yii::$app->getModule('activity'));
    }
}
