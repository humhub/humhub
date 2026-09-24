<?php

namespace tests\codeception\unit\assets;

use humhub\assets\CoreVueAsset;
use humhub\modules\user\assets\UserVueAsset;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class UserVueAssetTest extends HumHubDbTestCase
{
    public function testArtifactFilesExistAfterBuild()
    {
        $path = Yii::getAlias('@user/resources/js');
        $this->assertFileExists($path . '/humhub.user.vue.js');
        $this->assertFileExists($path . '/humhub.user.vue.js.map');
    }

    public function testDependsOnTheCoreVueBundle()
    {
        $this->assertContains(CoreVueAsset::class, (new UserVueAsset())->depends);
    }
}
