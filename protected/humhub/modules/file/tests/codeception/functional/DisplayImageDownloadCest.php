<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace file\functional;

use file\FunctionalTester;
use humhub\modules\file\converter\DisplayImage;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use PHPUnit\Framework\Assert;

/**
 * The display version of an image is created on its first request; an image within the bounds is served as it is
 */
class DisplayImageDownloadCest
{
    public function testLargeImageIsScaledDown(FunctionalTester $I)
    {
        $I->wantTo('get a large image scaled down to the display bounds');
        $I->amAdmin();
        $file = $this->createImageFile($this->createJpeg(2500, 100), 'image/jpeg');

        $I->amOnRoute('/file/file/download', ['guid' => $file->guid, 'variant' => DisplayImage::VARIANT]);

        $I->seeResponseCodeIs(200);
        $size = getimagesizefromstring($I->grabPageSource());
        Assert::assertSame(2048, $size[0]);
        Assert::assertTrue($file->store->has(DisplayImage::VARIANT));
    }

    public function testSmallImageIsServedAsItIs(FunctionalTester $I)
    {
        $I->wantTo('get a small image as it is under its display URL');
        $I->amAdmin();
        $content = $this->createJpeg(80, 40);
        $file = $this->createImageFile($content, 'image/jpeg');

        $I->amOnRoute('/file/file/download', ['guid' => $file->guid, 'variant' => DisplayImage::VARIANT]);

        $I->seeResponseCodeIs(200);
        Assert::assertSame($content, $I->grabPageSource());
        Assert::assertFalse($file->store->has(DisplayImage::VARIANT));
    }

    public function testOtherFileIsServedAsItIs(FunctionalTester $I)
    {
        $I->wantTo('get a file the display version does not apply to as it is');
        $I->amAdmin();
        $file = $this->createImageFile('GIF89a', 'image/gif');

        $I->amOnRoute('/file/file/download', ['guid' => $file->guid, 'variant' => DisplayImage::VARIANT]);

        $I->seeResponseCodeIs(200);
        Assert::assertSame('GIF89a', $I->grabPageSource());
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

    private function createJpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($image);

        return ob_get_clean();
    }
}
