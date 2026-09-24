<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\assets;

use humhub\assets\CoreApiAsset;
use humhub\assets\CoreBundleAsset;
use humhub\assets\CoreVueAsset;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class CoreVueAssetTest extends HumHubDbTestCase
{
    public function testArtifactFilesExistAfterBuild()
    {
        $path = Yii::getAlias('@humhub/resources/js');
        $this->assertFileExists($path . '/humhub.core.vue.js');
        $this->assertFileExists($path . '/humhub.core.vue.js.map');
    }

    public function testDependsOnCoreApi()
    {
        $defaults = (new \ReflectionClass(CoreVueAsset::class))->getDefaultProperties();
        $this->assertContains(CoreApiAsset::class, $defaults['depends']);
    }

    public function testBundleIsPartOfTheCoreBundle()
    {
        $this->assertContains(CoreVueAsset::class, CoreBundleAsset::STATIC_DEPENDS);
    }
}
