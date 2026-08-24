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

    /**
     * `yii asset` writes every bundle it compressed into a target as "no source path, no
     * files" into `assets-prod.php` (see `humhub\commands\AssetController`) - the runtime
     * lives in `js/humhub-app.js` from then on. The bundle must not put its production
     * variant back on the page from there: with no source path there is nothing to publish
     * it from, so it would be registered as `/vue.runtime.global.prod.js` and 404 on every
     * single page request.
     */
    public function testCompressedBundleRegistersNoFilesOfItsOwn()
    {
        $bundle = Yii::createObject([
            'class' => VueAsset::class,
            'sourcePath' => null,
            'js' => [],
            'css' => [],
            'depends' => [AppAsset::BUNDLE_NAME],
        ]);

        $this->assertSame([], $bundle->js);
    }

    public function testCoreApiDependsOnTheVueRuntime()
    {
        $defaults = (new \ReflectionClass(CoreApiAsset::class))->getDefaultProperties();
        $this->assertContains(VueAsset::class, $defaults['depends']);
    }
}
