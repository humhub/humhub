<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\space\models\Space;

class ModuleManagerTest extends \tests\codeception\_support\HumHubDbTestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testModuleManager()
    {
        $spaceModel = new Space();
        $this->assertInstanceOf(ContentContainerModuleManager::class, $spaceModel->moduleManager);
    }
}
