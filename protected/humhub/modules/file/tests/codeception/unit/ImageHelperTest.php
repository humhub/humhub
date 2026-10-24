<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\imagine\Image;

class ImageHelperTest extends HumHubDbTestCase
{
    public function testDownscaleImageResizesOversizedImage()
    {
        $this->getFileModule()->imageMaxResolution = '50x50';

        $file = $this->createImageFile($this->createJpeg(200, 100));

        ImageHelper::downscaleImage($file);

        $image = Image::getImagine()->load($file->store->getContent());
        $this->assertLessThanOrEqual(50, $image->getSize()->getWidth());
        $this->assertLessThanOrEqual(50, $image->getSize()->getHeight());
        $this->assertEquals($file->store->fileSize(), $file->size);
    }

    public function testFixJpegOrientationReadsExifFromStoredFile()
    {
        $file = $this->createImageFile($this->getExifOrientedJpeg());

        $image = Image::getImagine()->load($file->store->getContent());
        $this->assertSame(40, $image->getSize()->getWidth());
        $this->assertSame(20, $image->getSize()->getHeight());

        ImageHelper::fixJpegOrientation($image, $file);

        // EXIF Orientation 6 requires a 90° rotation, so width/height must be swapped
        $this->assertSame(20, $image->getSize()->getWidth());
        $this->assertSame(40, $image->getSize()->getHeight());
    }

    public function testDownscaleImageStoresFixedOrientation()
    {
        $this->getFileModule()->imageJpegQuality = 90;

        $file = $this->createImageFile($this->getExifOrientedJpeg());

        ImageHelper::downscaleImage($file);

        $image = Image::getImagine()->load($file->store->getContent());
        $this->assertSame(20, $image->getSize()->getWidth());
        $this->assertSame(40, $image->getSize()->getHeight());
    }

    private function getFileModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');
        return $module;
    }

    private function createImageFile(string $content): File
    {
        $post = Post::findOne(['id' => 1]);

        $file = new File();
        $file->file_name = 'test.jpg';
        $file->mime_type = 'image/jpeg';
        $file->save();

        $post->fileManager->attach($file);
        $file->setStoredFileContent($content);

        return $file;
    }

    private function createJpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($image);

        return ob_get_clean();
    }

    private function getExifOrientedJpeg(): string
    {
        return file_get_contents(__DIR__ . '/../_data/exif-orientation-6.jpg');
    }

}
