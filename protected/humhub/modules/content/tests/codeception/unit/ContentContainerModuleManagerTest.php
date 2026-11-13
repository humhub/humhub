<?php

namespace humhub\modules\content\tests\codeception\unit;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\space\models\Space;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ContentContainerModuleManagerTest extends HumHubDbTestCase
{
    private const MODULE_ID = 'module-manager-test';

    private ContentContainerModuleManager $moduleManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->becomeUser('Admin');

        $module = new class (self::MODULE_ID, Yii::$app) extends ContentContainerModule {
            public function getContentContainerTypes()
            {
                return [Space::class];
            }

            public function enableContentContainer(ContentContainerActiveRecord $container)
            {
            }

            public function disableContentContainer(ContentContainerActiveRecord $container)
            {
            }
        };
        Yii::$app->setModule(self::MODULE_ID, $module);

        $this->moduleManager = Space::findOne(1)->moduleManager;
        // The test module is not installed, so make it available to the space directly
        (new ReflectionProperty(ContentContainerModuleManager::class, '_available'))
            ->setValue($this->moduleManager, [self::MODULE_ID => $module]);
    }

    protected function tearDown(): void
    {
        Yii::$app->setModule(self::MODULE_ID, null);
        parent::tearDown();
    }

    public function testIsEnabledRightAfterEnable()
    {
        $this->assertFalse($this->moduleManager->isEnabled(self::MODULE_ID));

        $this->assertTrue($this->moduleManager->enable(self::MODULE_ID));

        $this->assertTrue($this->moduleManager->isEnabled(self::MODULE_ID));
        $this->assertContains(self::MODULE_ID, $this->moduleManager->getEnabled());
    }

    public function testIsDisabledRightAfterDisable()
    {
        $this->assertTrue($this->moduleManager->enable(self::MODULE_ID));

        $this->assertTrue($this->moduleManager->disable(self::MODULE_ID));

        $this->assertFalse($this->moduleManager->isEnabled(self::MODULE_ID));
        $this->assertNotContains(self::MODULE_ID, $this->moduleManager->getEnabled());
    }
}
