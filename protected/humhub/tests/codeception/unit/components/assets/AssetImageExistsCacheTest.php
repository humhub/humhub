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
 * Verifies the caching of the {@see AssetImage::exists()} data-mount lookup in
 * both modes of `AssetManager::$cachePublishState`:
 *
 * - With the cache (remote mounts), the lookup result must be shared through the
 *   cache and updated explicitly by `setByFile()`/`delete()`, so that rendering
 *   an image costs no data-mount round trip per request.
 * - Without the cache (local mounts, the default), every call verifies against
 *   the filesystem, making the state fully self-healing.
 */
class AssetImageExistsCacheTest extends HumHubDbTestCase
{
    private const FILE = '/tests/asset-image/exists-test-image.jpg';

    protected function _before()
    {
        parent::_before();

        // Remove leftovers of previous runs (files, published copies and cache entries)
        Yii::$app->cache->flush();
        $this->createAssetImage()->delete();
    }

    public function testExistsIsCachedAcrossInstancesOnRemoteMounts()
    {
        $this->freshAssetManager(true);

        $image = $this->createAssetImage();
        $image->setByFile($this->createTempImage());
        $this->assertTrue($this->createAssetImage()->exists());

        // An out-of-band change on the data mount is not visible to new
        // instances: the cached lookup is authoritative until invalidated
        Yii::$app->fs->getDataMount()->delete(self::FILE);
        $this->assertTrue($this->createAssetImage()->exists());

        // Flushing the cache restores self-healing
        Yii::$app->cache->flush();
        $this->assertFalse($this->createAssetImage()->exists());
    }

    public function testCacheMissFallsBackToLiveProbe()
    {
        $this->freshAssetManager(true);

        $this->createAssetImage()->setByFile($this->createTempImage());

        // A cache miss (fresh cache after deploy/flush) must not be mistaken
        // for a cached "does not exist" - it has to fall through to a live probe
        Yii::$app->cache->flush();
        $this->assertTrue($this->createAssetImage()->exists());
    }

    public function testSetByFileAndDeleteUpdateTheCachedState()
    {
        $this->freshAssetManager(true);

        // The negative lookup of a missing upload is cached, ...
        $this->assertFalse($this->createAssetImage()->exists());

        // ... so setByFile() must overwrite it for other processes
        $this->createAssetImage()->setByFile($this->createTempImage());
        $this->assertTrue($this->createAssetImage()->exists());

        // ... and delete() must do the same in reverse
        $this->createAssetImage()->delete();
        $this->assertFalse($this->createAssetImage()->exists());
    }

    public function testLocalMountsStayUncachedAndSelfHealing()
    {
        $this->freshAssetManager(false);

        $this->createAssetImage()->setByFile($this->createTempImage());
        $this->assertTrue($this->createAssetImage()->exists());

        // Without the cache an out-of-band change is picked up immediately
        Yii::$app->fs->getDataMount()->delete(self::FILE);
        $this->assertFalse($this->createAssetImage()->exists());
    }

    private function createAssetImage(): AssetImage
    {
        return new AssetImage([
            'file' => self::FILE,
            'defaultOptions' => ['width' => 40, 'height' => 40],
        ]);
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

    private function createTempImage(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'asset-image-test');
        copy(Yii::getAlias('@humhub/resources/img/default_user.jpg'), $tempFile);

        return $tempFile;
    }
}
