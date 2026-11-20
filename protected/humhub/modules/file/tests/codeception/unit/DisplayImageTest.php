<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\converter\DisplayImage;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\imagine\Image;

class DisplayImageTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getFileModule()->imageDisplayMaxWidth = 100;
        $this->getFileModule()->imageDisplayMaxHeight = 100;
    }

    protected function tearDown(): void
    {
        $this->getFileModule()->imageDisplayMaxWidth = 2048;
        $this->getFileModule()->imageDisplayMaxHeight = 2048;
        parent::tearDown();
    }

    public function testLargeImageGetsAVariantWithinTheBounds()
    {
        $file = $this->createImageFile($this->createImage('jpeg', 300, 150), 'image/jpeg');

        $this->assertTrue((new DisplayImage(['file' => $file]))->prepare());

        $image = Image::getImagine()->load($file->store->getContent(DisplayImage::VARIANT));
        $this->assertSame(100, $image->getSize()->getWidth());
        $this->assertSame(50, $image->getSize()->getHeight());
    }

    public function testVariantKeepsTheFormat()
    {
        $file = $this->createImageFile($this->createImage('png', 300, 150), 'image/png');

        $this->assertTrue((new DisplayImage(['file' => $file]))->prepare());

        $this->assertSame('image/png', getimagesizefromstring($file->store->getContent(DisplayImage::VARIANT))['mime']);
    }

    public function testImageWithinTheBoundsGetsNoVariant()
    {
        $file = $this->createImageFile($this->createImage('png', 80, 40), 'image/png');

        $this->assertFalse((new DisplayImage(['file' => $file]))->prepare());
        $this->assertFalse($file->store->has(DisplayImage::VARIANT));
    }

    public function testGifIsShownAsItIs()
    {
        $file = $this->createImageFile($this->createImage('gif', 300, 150), 'image/gif');

        $this->assertFalse((new DisplayImage())->applyFile($file));
        $this->assertFalse((new DisplayImage(['file' => $file]))->prepare());
        $this->assertFalse($file->store->has(DisplayImage::VARIANT));
    }

    public function testUrlDoesNotConvert()
    {
        $file = $this->createImageFile($this->createImage('jpeg', 300, 150), 'image/jpeg');

        $url = (new DisplayImage(['file' => $file]))->getUrl();

        $this->assertStringContainsString('variant=' . DisplayImage::VARIANT, $url);
        $this->assertStringContainsString('guid=' . $file->guid, $url);
        $this->assertFalse($file->store->has(DisplayImage::VARIANT));
    }

    public function testOrientationIsFixed()
    {
        $this->getFileModule()->imageDisplayMaxWidth = 30;
        $this->getFileModule()->imageDisplayMaxHeight = 30;
        // 40×20, EXIF orientation 6: shown as 20×40
        $file = $this->createImageFile(file_get_contents(__DIR__ . '/../_data/exif-orientation-6.jpg'), 'image/jpeg');

        $this->assertTrue((new DisplayImage(['file' => $file]))->prepare());

        $image = Image::getImagine()->load($file->store->getContent(DisplayImage::VARIANT));
        $this->assertSame(15, $image->getSize()->getWidth());
        $this->assertSame(30, $image->getSize()->getHeight());
    }

    private function getFileModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');
        return $module;
    }

    private function createImageFile(string $content, string $mimeType): File
    {
        $file = new File();
        $file->file_name = 'test.' . substr($mimeType, strlen('image/'));
        $file->mime_type = $mimeType;
        $file->save();

        Post::findOne(['id' => 1])->fileManager->attach($file);
        $file->setStoredFileContent($content);

        return $file;
    }

    private function createImage(string $format, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        match ($format) {
            'jpeg' => imagejpeg($image),
            'png' => imagepng($image),
            'gif' => imagegif($image),
        };

        return ob_get_clean();
    }
}
