<?php

namespace tests\codeception\unit\modules\space;

use humhub\modules\activity\models\Activity;
use humhub\modules\content\activities\ModuleDisabledActivity;
use humhub\modules\content\activities\ModuleEnabledActivity;
use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\user\tests\codeception\fixtures\UserFixture;

class ContentContainerModuleManagerTest extends \tests\codeception\_support\HumHubDbTestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testModuleManager()
    {
        /** @var ContentContainerModuleManager $contentModuleManager */
        $contentModuleManager = $this->make(ContentContainerModuleManager::class, [
            'contentContainer' => $this->tester->grabFixture('space', 0)
        ]);

        $this->tester->dontSeeRecord(Activity::class, ['class' => ModuleEnabledActivity::class]);
        $this->tester->dontSeeRecord(Activity::class, ['class' => ModuleDisabledActivity::class]);

        $user = $this->tester->grabFixture(UserFixture::class, 0);

        $contentModuleManager->addActivity('name', ContentContainerModuleState::STATE_DISABLED, $user);
        $this->tester->seeRecord(Activity::class, ['class' => ModuleDisabledActivity::class]);

        $contentModuleManager->addActivity('name', ContentContainerModuleState::STATE_ENABLED, $user);
        $this->tester->seeRecord(Activity::class, ['class' => ModuleEnabledActivity::class]);
    }
}
