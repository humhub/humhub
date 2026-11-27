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
use Imagine\Factory\ClassFactoryInterface;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Metadata\MetadataReaderInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\imagine\Image;

class ImageHelperTest extends HumHubDbTestCase
{
    /**
     * Counts the images decoded by `Image::getImagine()`, see [[spyOnImagine()]]
     */
    private ?object $imagine = null;

    protected function tearDown(): void
    {
        if ($this->imagine !== null) {
            Image::setImagine(null);
            $this->imagine = null;
        }

        parent::tearDown();
    }

    public function testDownscaleImageResizesOversizedImage()
    {
        $this->getFileModule()->imageMaxResolution = '50x50';

        $file = $this->createImageFile($this->createJpeg(200, 100));
        $imagine = $this->spyOnImagine();

        ImageHelper::downscaleImage($file);

        $this->assertSame(1, $imagine->loads);

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

    public function testDownscaleImageDecodesNothingWithoutAnOptionThatChangesTheImage()
    {
        $content = $this->createJpeg(200, 100);
        $file = $this->createImageFile($content);
        $imagine = $this->spyOnImagine();

        ImageHelper::downscaleImage($file);

        $this->assertSame(0, $imagine->loads);
        $this->assertSame($content, $file->store->getContent());
    }

    public function testDownscaleImageDecodesNothingWithinTheMaximumResolution()
    {
        $this->getFileModule()->imageMaxResolution = '200x100';

        $content = $this->createJpeg(200, 100);
        $file = $this->createImageFile($content);
        $imagine = $this->spyOnImagine();

        ImageHelper::downscaleImage($file);

        $this->assertSame(0, $imagine->loads);
        $this->assertSame($content, $file->store->getContent());
    }

    public function testDownscaleImageComparesTheRotatedDimensionsWithTheMaximumResolution()
    {
        // Stored 40×20 with EXIF orientation 6, i.e. shown as 20×40: within 30×40, as the decoded image after its rotation
        $this->getFileModule()->imageMaxResolution = '30x40';

        $content = $this->getExifOrientedJpeg();
        $file = $this->createImageFile($content);
        $imagine = $this->spyOnImagine();

        ImageHelper::downscaleImage($file);

        $this->assertSame(0, $imagine->loads);
        $this->assertSame($content, $file->store->getContent());

        // Shown as 20×40, over 40×30: decoded, rotated and scaled down
        $this->getFileModule()->imageMaxResolution = '40x30';

        ImageHelper::downscaleImage($file);

        $this->assertSame(1, $imagine->loads);
        $this->assertSame([15, 30], ImageHelper::getDimensions($file));
    }

    public function testDownscaleImageDecodesAnImageWithoutStoredDimensions()
    {
        $this->getFileModule()->imageMaxResolution = '50x50';

        $file = $this->createImageFile($this->createJpeg(200, 100));
        $file->updateAttributes(['metadata' => null]);
        $imagine = $this->spyOnImagine();

        ImageHelper::downscaleImage($file);

        $this->assertSame(1, $imagine->loads);
        $this->assertSame([50, 25], ImageHelper::getDimensions($file));
    }

    public function testDownscaleImageDecodesForAQualityOption()
    {
        $this->getFileModule()->imageMaxResolution = '200x100';
        $this->getFileModule()->imageJpegQuality = 50;

        $content = $this->createJpeg(200, 100);
        $file = $this->createImageFile($content);
        $imagine = $this->spyOnImagine();

        ImageHelper::downscaleImage($file);

        $this->assertSame(1, $imagine->loads);
        $this->assertNotSame($content, $file->store->getContent());
    }

    /**
     * Wraps the Imagine instance of `Image::getImagine()` to count the images it decodes (`load()`)
     */
    private function spyOnImagine(): object
    {
        $this->imagine = new class (Image::getImagine()) implements ImagineInterface {
            public int $loads = 0;

            public function __construct(private readonly ImagineInterface $imagine)
            {
            }

            public function create(BoxInterface $size, ?ColorInterface $color = null)
            {
                return $this->imagine->create($size, $color);
            }

            public function open($path)
            {
                return $this->imagine->open($path);
            }

            public function load($string)
            {
                $this->loads++;

                return $this->imagine->load($string);
            }

            public function read($resource)
            {
                return $this->imagine->read($resource);
            }

            public function font($file, $size, ColorInterface $color)
            {
                return $this->imagine->font($file, $size, $color);
            }

            public function setMetadataReader(MetadataReaderInterface $metadataReader)
            {
                return $this->imagine->setMetadataReader($metadataReader);
            }

            public function getMetadataReader()
            {
                return $this->imagine->getMetadataReader();
            }

            public function setClassFactory(ClassFactoryInterface $classFactory)
            {
                return $this->imagine->setClassFactory($classFactory);
            }

            public function getClassFactory()
            {
                return $this->imagine->getClassFactory();
            }
        };
        Image::setImagine($this->imagine);

        return $this->imagine;
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
