<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\jobs\CollectFileMetadata;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\libs\Metadata;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileContent;
use humhub\modules\file\Module;
use humhub\modules\file\serializers\FileSerializer;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\queue\RetryableJobInterface;

class ImageDimensionsTest extends HumHubDbTestCase
{
    public function testStoringAnImageCollectsItsDimensions()
    {
        $file = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));

        $this->assertSame([200, 100], ImageHelper::getDimensions($file));
    }

    public function testDimensionsFollowTheExifRotation()
    {
        // Stored 40×20 with EXIF orientation 6: shown (and previewed) as 20×40
        $file = $this->createFile('photo.jpg', 'image/jpeg', file_get_contents(__DIR__ . '/../_data/exif-orientation-6.jpg'));

        $this->assertSame([20, 40], ImageHelper::getDimensions($file));
    }

    public function testOtherFilesGetNoDimensions()
    {
        $file = $this->createFile('notes.txt', 'text/plain', 'plain text');

        $this->assertNull(ImageHelper::getDimensions($file));
    }

    public function testAnUnreadableImageGetsNoDimensions()
    {
        $file = $this->createFile('broken.jpg', 'image/jpeg', 'not an image');

        $this->assertNull(ImageHelper::getDimensions($file));
    }

    public function testReplacingAnImageByUnreadableContentRemovesItsDimensions()
    {
        $file = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));

        $file->setStoredFileContent('not an image');

        $file = File::findOne($file->id);
        $this->assertNull(ImageHelper::getDimensions($file));
        $this->assertNull($file->getAttribute('metadata'));
    }

    public function testUnparsableMetadataNeitherBreaksStoringNorReading()
    {
        $file = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));
        $file->updateAttributes(['metadata' => 'garbage']);

        $file->setStoredFileContent($this->createJpeg(30, 60));

        $this->assertNull(ImageHelper::getDimensions(File::findOne($file->id)));
    }

    public function testAFileContentImageGetsItsDimensions()
    {
        $file = new FileContent();
        $file->file_name = 'photo.jpg';
        $file->mime_type = 'image/jpeg';
        $file->newFileContent = $this->createJpeg(200, 100);
        $this->assertTrue($file->save());

        $file = File::findOne($file->id);
        $this->assertSame([200, 100], ImageHelper::getDimensions($file));
        $this->assertNotEmpty($file->hash_sha1);
    }

    public function testDownscalingUpdatesTheDimensions()
    {
        $this->getFileModule()->imageMaxResolution = '50x50';
        $file = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));

        ImageHelper::downscaleImage($file);

        $this->assertSame([50, 25], ImageHelper::getDimensions(File::findOne($file->id)));
    }

    public function testCollectingKeepsOtherMetadata()
    {
        $file = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));
        $file->setMetadata(['origin' => 'import']);
        $file->updateAttributes(['metadata' => (string)$file->getMetadata()]);

        $file = File::findOne($file->id);
        $file->setStoredFileContent($this->createJpeg(30, 60));

        $file = File::findOne($file->id);
        $this->assertSame([30, 60], ImageHelper::getDimensions($file));
        $this->assertSame('import', $file->getMetadata()->origin);
    }

    public function testTheJobCollectsTheDimensionsOfOlderImages()
    {
        $file = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));
        $file->updateAttributes(['metadata' => null]); // as uploaded before 1.20

        CollectFileMetadata::collect();

        $this->assertSame([200, 100], ImageHelper::getDimensions(File::findOne($file->id)));
    }

    public function testTheJobSkipsImagesThatHaveTheirDimensions()
    {
        $file = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));

        $seen = [];
        CollectFileMetadata::collect(function (File $f) use (&$seen) {
            $seen[] = $f->id;
        });

        $this->assertNotContains($file->id, $seen);
    }

    public function testTheJobCollectsTheDimensionsOfImagesWithOtherMetadata()
    {
        $file = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));
        // A value mentioning "width" must not pass for the key
        $file->updateAttributes(['metadata' => (string)new Metadata(['origin' => 'import', 'note' => '"width"'])]);

        CollectFileMetadata::collect();

        $file = File::findOne($file->id);
        $this->assertSame([200, 100], ImageHelper::getDimensions($file));
        $this->assertSame('import', $file->getMetadata()->origin);
    }

    public function testTheJobSkipsImagesWithoutStoredContent()
    {
        $file = new File();
        $file->file_name = 'orphan.jpg';
        $file->mime_type = 'image/jpeg';
        $this->assertTrue($file->save());

        $seen = [];
        CollectFileMetadata::collect(function (File $f) use (&$seen) {
            $seen[] = $f->id;
        });

        $this->assertContains($file->id, $seen);
        $this->assertNull(ImageHelper::getDimensions(File::findOne($file->id)));
    }

    public function testTheJobIsRetriedABoundedNumberOfTimes()
    {
        $job = new CollectFileMetadata();

        $this->assertInstanceOf(RetryableJobInterface::class, $job);
        $this->assertTrue($job->canRetry(1, null));
        $this->assertFalse($job->canRetry(5, null));
    }

    public function testTheFileShapeCarriesTheDimensions()
    {
        $image = $this->createFile('photo.jpg', 'image/jpeg', $this->createJpeg(200, 100));
        $text = $this->createFile('notes.txt', 'text/plain', 'plain text');

        $this->assertSame(200, FileSerializer::file($image)['width']);
        $this->assertSame(100, FileSerializer::file($image)['height']);
        $this->assertNull(FileSerializer::file($text)['width']);
        $this->assertNull(FileSerializer::file($text)['height']);
    }

    protected function createFile(string $fileName, string $mimeType, string $content): File
    {
        $file = new File();
        $file->file_name = $fileName;
        $file->mime_type = $mimeType;
        $this->assertTrue($file->save());

        Post::findOne(['id' => 1])->fileManager->attach($file);
        $file->setStoredFileContent($content);

        return File::findOne($file->id);
    }

    protected function createJpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($image);

        return ob_get_clean();
    }

    private function getFileModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        return $module;
    }
}
