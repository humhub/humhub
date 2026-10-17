<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\assets;

use humhub\assets\AppAsset;
use humhub\components\assets\AssetManager;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class AssetManagerBaseUrlTest extends HumHubDbTestCase
{
    /**
     * The assets mount base URL is configured as alias (e.g. `@web/assets`). Modules use
     * `AssetBundle::register($view)->baseUrl` directly to build URLs, so the alias has to
     * be resolved.
     */
    public function testBaseUrlAliasIsResolved()
    {
        Yii::setAlias('@web', '/humhub');

        $assetManager = new AssetManager();

        $this->assertSame('/humhub/assets/tests', $assetManager->baseUrl);

        $bundle = $assetManager->getBundle(AppAsset::class);
        $this->assertStringStartsWith('/humhub/assets/tests/', $bundle->baseUrl);
    }
}
