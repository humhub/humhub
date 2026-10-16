<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\assets;

use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class AssetManagerClearTest extends HumHubDbTestCase
{
    /**
     * `clear()` must only remove published assets. Dot files at the mount root,
     * such as the `assets/.gitignore` shipped with the installation, have to
     * survive a cache flush.
     */
    public function testClearRemovesPublishedContentButKeepsDotFiles()
    {
        $fs = Yii::$app->fs->getAssetsMount();

        $fs->write('.gitignore', "*\n!.gitignore\n");
        $fs->write('a1b2c3d4/test.js', 'test');

        Yii::$app->assetManager->clear();

        $this->assertFalse($fs->directoryExists('a1b2c3d4'));
        $this->assertTrue($fs->fileExists('.gitignore'));
    }

    /**
     * The published copies of AssetImages are linked from emails, which are never rendered
     * again: a cache flush, run after every migration, must not break them.
     */
    public function testClearKeepsPublishedAssetImages()
    {
        $fs = Yii::$app->fs->getAssetsMount();

        $fs->write('a1b2c3d4/test.js', 'test');
        $fs->write('_/e5f6a7b8/header_1a2b3c4d.png', 'image');

        Yii::$app->assetManager->clear();

        $this->assertFalse($fs->directoryExists('a1b2c3d4'));
        $this->assertTrue($fs->fileExists('_/e5f6a7b8/header_1a2b3c4d.png'));
    }

    public function testClearRemovesPublishedAssetImagesOnRequest()
    {
        $fs = Yii::$app->fs->getAssetsMount();

        $fs->write('_/e5f6a7b8/header_1a2b3c4d.png', 'image');

        Yii::$app->assetManager->clear(true);

        $this->assertFalse($fs->directoryExists('_'));
    }
}
