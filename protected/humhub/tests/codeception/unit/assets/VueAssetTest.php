<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\assets;

use humhub\assets\AppAsset;
use humhub\assets\CoreApiAsset;
use humhub\assets\VueAsset;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class VueAssetTest extends HumHubDbTestCase
{
    public function testRuntimeFilesExist()
    {
        $path = Yii::getAlias('@npm/vue/dist');
        $this->assertFileExists($path . '/vue.runtime.global.js');
        $this->assertFileExists($path . '/vue.runtime.global.prod.js');
    }

    public function testDebugAndProductionVariantsAreConfigured()
    {
        $defaults = (new \ReflectionClass(VueAsset::class))->getDefaultProperties();
        $this->assertSame(['vue.runtime.global.js'], $defaults['js']);
        $this->assertSame(['vue.runtime.global.prod.js'], $defaults['jsProd']);
    }

    public function testBundleIsPartOfTheAppAsset()
    {
        $this->assertContains(VueAsset::class, AppAsset::STATIC_DEPENDS);
    }

    public function testCoreApiDependsOnTheVueRuntime()
    {
        $defaults = (new \ReflectionClass(CoreApiAsset::class))->getDefaultProperties();
        $this->assertContains(VueAsset::class, $defaults['depends']);
    }
}
