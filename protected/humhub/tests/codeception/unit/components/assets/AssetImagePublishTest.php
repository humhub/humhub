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
 * Verifies the publish state handling of AssetImages in both modes of
 * `AssetManager::$cachePublishState`:
 *
 * - With the cache (remote mounts), publish states must be shared through the cache
 *   and invalidated immediately, so that changes made in one process (e.g. a queue
 *   worker updating a profile image) are visible to all other processes.
 * - Without the cache (local mounts, the default), every publish call verifies the
 *   published copy against the filesystem, making the state fully self-healing.
 *
 * Each `freshAssetManager()` call simulates a new process: a manager instance with
 * an empty request-local memo.
 */
class AssetImagePublishTest extends HumHubDbTestCase
{
    private const FILE = '/tests/asset-image/test-image.jpg';
    private const OPTIONS = ['width' => 40, 'height' => 40];

    protected function _before()
    {
        parent::_before();

        // Remove leftovers of previous runs (files, published copies and cache entries)
        Yii::$app->cache->flush();
        $this->createAssetImage()->delete();
    }

    public function testPublishStateIsSharedAndInvalidatedAcrossProcesses()
    {
        $scaledFileName = $this->getScaledFileName();

        // "Process A" (web): upload an image and render its URL
        $this->freshAssetManager(true);
        $imageA = $this->createAssetImage();
        $imageA->setByFile($this->createTempImage('default_user.jpg'));
        $this->assertNotEmpty($imageA->getUrl());

        // "Process B" (e.g. queue worker): sees the publish state without publishing again
        $managerB = $this->freshAssetManager(true);
        $published = $managerB->getPublishedAssetImage($scaledFileName);
        $this->assertNotNull($published);
        $this->assertTrue(Yii::$app->fs->getAssetsMount()->fileExists($published[0]));

        // ... and replaces the image, which must invalidate the publish state immediately
        $this->createAssetImage()->setByFile($this->createTempImage('default_space.jpg'));
        $this->assertFalse(Yii::$app->fs->getAssetsMount()->fileExists($published[0]));

        // "Process C" (web): must not serve the stale publish state, but republish
        $managerC = $this->freshAssetManager(true);
        $this->assertNull($managerC->getPublishedAssetImage($scaledFileName));

        $this->assertNotEmpty($this->createAssetImage()->getUrl());
        $republished = $managerC->getPublishedAssetImage($scaledFileName);
        $this->assertNotNull($republished);
        $this->assertTrue(Yii::$app->fs->getAssetsMount()->fileExists($republished[0]));
    }

    public function testClearInvalidatesPublishState()
    {
        $scaledFileName = $this->getScaledFileName();

        $manager = $this->freshAssetManager(true);
        $image = $this->createAssetImage();
        $image->setByFile($this->createTempImage('default_user.jpg'));
        $this->assertNotEmpty($image->getUrl());

        $published = $manager->getPublishedAssetImage($scaledFileName);
        $this->assertNotNull($published);

        $manager->clear();

        $this->assertFalse(Yii::$app->fs->getAssetsMount()->fileExists($published[0]));
        // The same instance and other processes no longer see the publish state
        $this->assertNull($manager->getPublishedAssetImage($scaledFileName));
        $this->assertNull($this->freshAssetManager(true)->getPublishedAssetImage($scaledFileName));

        // A new render republishes the image
        $this->assertNotEmpty($this->createAssetImage()->getUrl());
        $republished = Yii::$app->assetManager->getPublishedAssetImage($scaledFileName);
        $this->assertNotNull($republished);
        $this->assertTrue(Yii::$app->fs->getAssetsMount()->fileExists($republished[0]));
    }

    public function testLocalMountsSelfHealWithoutCache()
    {
        // Local mounts in the test environment: the publish-state cache is off by default (auto-detected)
        $autoConfigured = new AssetManager(['basePath' => Yii::$app->assetManager->basePath]);
        $this->assertFalse($autoConfigured->cachePublishState);

        $this->freshAssetManager(false);
        $image = $this->createAssetImage();
        $image->setByFile($this->createTempImage('default_user.jpg'));
        $url = $image->getUrl();
        $this->assertNotEmpty($url);

        // The URL must be stable across processes (`?t=` derives from the file, not the request)
        $this->freshAssetManager(false);
        $this->assertSame($url, $this->createAssetImage()->getUrl());

        // Replacing the image in another process requires no invalidation:
        // the next render verifies against the filesystem and republishes
        $this->createAssetImage()->setByFile($this->createTempImage('default_space.jpg'));

        $this->freshAssetManager(false);
        $urlAfterReplace = $this->createAssetImage()->getUrl();
        $this->assertNotEmpty($urlAfterReplace);
        $published = Yii::$app->assetManager->getPublishedAssetImage($this->getScaledFileName());
        $this->assertTrue(Yii::$app->fs->getAssetsMount()->fileExists($published[0]));

        // Even a published copy deleted out-of-band is restored on the next render
        Yii::$app->fs->getAssetsMount()->delete($published[0]);
        $this->freshAssetManager(false);
        $this->assertNotEmpty($this->createAssetImage()->getUrl());
        $this->assertTrue(Yii::$app->fs->getAssetsMount()->fileExists($published[0]));
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

    public function testUrlForSourceFileWithoutExtension()
    {
        // e.g. a HumHub `File` record's stored file, saved without an extension as `file`
        $image = new AssetImage([
            'file' => '/tests/asset-image/file',
            'defaultOptions' => self::OPTIONS,
        ]);
        $image->delete(); // remove leftovers of previous runs
        $image->setByFile($this->createTempImage('default_user.jpg'));

        // The variant falls back to the resolved image format for its extension
        $this->assertStringContainsString('.png', $image->getUrl());
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

    private function freshAssetManager(bool $cachePublishState): AssetManager
    {
        Yii::$app->set('assetManager', [
            'class' => AssetManager::class,
            'basePath' => Yii::$app->assetManager->basePath,
            'cachePublishState' => $cachePublishState,
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
