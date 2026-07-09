<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\assets;

use humhub\components\assets\AssetImage;
use humhub\components\assets\AssetManager;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Verifies that the publish state of AssetImages is shared through the cache and
 * invalidated immediately, so that changes made in one process (e.g. a queue worker
 * updating a profile image) are visible to all other processes.
 *
 * Each `freshAssetManager()` call simulates a new process: a manager instance with
 * an empty request-local memo that only knows the shared cache.
 */
class AssetImagePublishTest extends HumHubDbTestCase
{
    private const FILE = '/tests/asset-image/test-image.jpg';
    private const OPTIONS = ['width' => 40, 'height' => 40];

    protected function _before()
    {
        parent::_before();

        // Remove leftovers of previous runs (files, published copies and cache entries)
        $this->createAssetImage()->delete();
    }

    public function testPublishStateIsSharedAndInvalidatedAcrossProcesses()
    {
        $scaledFileName = $this->getScaledFileName();

        // "Process A" (web): upload an image and render its URL
        $imageA = $this->createAssetImage();
        $imageA->setByFile($this->createTempImage('default_user.jpg'));
        $this->assertNotEmpty($imageA->getUrl());

        // "Process B" (e.g. queue worker): sees the publish state without publishing again
        $managerB = $this->freshAssetManager();
        $published = $managerB->getPublishedAssetImage($scaledFileName);
        $this->assertNotNull($published);
        $this->assertTrue(Yii::$app->fs->getAssetsMount()->fileExists($published[0]));

        // ... and replaces the image, which must invalidate the publish state immediately
        $this->createAssetImage()->setByFile($this->createTempImage('default_space.jpg'));
        $this->assertFalse(Yii::$app->fs->getAssetsMount()->fileExists($published[0]));

        // "Process C" (web): must not serve the stale publish state, but republish
        $managerC = $this->freshAssetManager();
        $this->assertNull($managerC->getPublishedAssetImage($scaledFileName));

        $this->assertNotEmpty($this->createAssetImage()->getUrl());
        $republished = $managerC->getPublishedAssetImage($scaledFileName);
        $this->assertNotNull($republished);
        $this->assertTrue(Yii::$app->fs->getAssetsMount()->fileExists($republished[0]));
    }

    public function testClearInvalidatesPublishState()
    {
        $scaledFileName = $this->getScaledFileName();

        $image = $this->createAssetImage();
        $image->setByFile($this->createTempImage('default_user.jpg'));
        $this->assertNotEmpty($image->getUrl());

        $published = Yii::$app->assetManager->getPublishedAssetImage($scaledFileName);
        $this->assertNotNull($published);

        Yii::$app->assetManager->clear();

        $this->assertFalse(Yii::$app->fs->getAssetsMount()->fileExists($published[0]));
        // The same instance and other processes no longer see the publish state
        $this->assertNull(Yii::$app->assetManager->getPublishedAssetImage($scaledFileName));
        $this->assertNull($this->freshAssetManager()->getPublishedAssetImage($scaledFileName));

        // A new render republishes the image
        $this->assertNotEmpty($this->createAssetImage()->getUrl());
        $republished = Yii::$app->assetManager->getPublishedAssetImage($scaledFileName);
        $this->assertNotNull($republished);
        $this->assertTrue(Yii::$app->fs->getAssetsMount()->fileExists($republished[0]));
    }

    public function testUrlWithoutImageFallsBackToDefaultFileOrEmptyString()
    {
        $withDefault = new AssetImage([
            'file' => self::FILE,
            'defaultOptions' => self::OPTIONS,
            'defaultFile' => '@humhub/resources/img/default_user.jpg',
        ]);
        $this->assertStringContainsString('default_user', $withDefault->getUrl());

        $withoutDefault = $this->createAssetImage();
        $this->assertSame('', $withoutDefault->getUrl());
    }

    private function createAssetImage(): AssetImage
    {
        return new AssetImage([
            'file' => self::FILE,
            'defaultOptions' => self::OPTIONS,
        ]);
    }

    private function getScaledFileName(): string
    {
        $options = self::OPTIONS;
        ksort($options);

        return dirname(self::FILE) . DIRECTORY_SEPARATOR
            . 'test-image_' . hash('xxh32', json_encode($options)) . '.jpg';
    }

    private function freshAssetManager(): AssetManager
    {
        Yii::$app->set('assetManager', [
            'class' => AssetManager::class,
            'basePath' => Yii::$app->assetManager->basePath,
        ]);

        return Yii::$app->assetManager;
    }

    private function createTempImage(string $resourceImage): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'asset-image-test');
        copy(Yii::getAlias('@humhub/resources/img/' . $resourceImage), $tempFile);

        return $tempFile;
    }
}
