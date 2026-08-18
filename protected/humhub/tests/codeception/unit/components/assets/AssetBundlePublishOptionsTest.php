<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\assets;

use humhub\assets\CoreApiAsset;
use humhub\assets\IntlMessageFormatAsset;
use humhub\modules\like\assets\LikeAsset;
use tests\codeception\_support\HumHubDbTestCase;

class AssetBundlePublishOptionsTest extends HumHubDbTestCase
{
    public function testModuleBundleExcludesVueSources()
    {
        $bundle = new LikeAsset();
        $this->assertSame(['/vue/'], $bundle->publishOptions['except']);
    }

    public function testCoreResourceBundleKeepsDefaultExcludes()
    {
        $bundle = new CoreApiAsset();
        $this->assertContains('/vue/', $bundle->publishOptions['except']);
        $this->assertContains('scss/', $bundle->publishOptions['except']);
        $this->assertNotContains('build/', $bundle->publishOptions['except']);
    }

    public function testExplicitPublishOptionsAreUntouched()
    {
        $bundle = new IntlMessageFormatAsset();
        $this->assertArrayNotHasKey('except', $bundle->publishOptions);
        $this->assertSame(['intl-messageformat.iife.js'], $bundle->publishOptions['only']);
    }
}
