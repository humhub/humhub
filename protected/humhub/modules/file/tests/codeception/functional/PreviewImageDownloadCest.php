<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace file\functional;

use file\FunctionalTester;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The preview of an image is created on its first request, so a list of many new images does not
 * create all their previews while it is built
 */
class PreviewImageDownloadCest
{
    public function testThePreviewIsCreatedOnItsFirstRequest(FunctionalTester $I)
    {
        $I->wantTo('get the preview of an image created when it is first requested');
        $I->amAdmin();
        $file = $this->createImageFile($this->createJpeg(1200, 900));

        $preview = new PreviewImage();
        Assert::assertTrue($preview->applyFile($file));
        $url = $preview->getLazyUrl();
        Assert::assertStringContainsString(PreviewImage::VARIANT, $url);
        Assert::assertFalse($file->store->has(PreviewImage::VARIANT), 'The URL alone creates no preview');

        $I->amOnRoute('/file/file/download', ['guid' => $file->guid, 'variant' => PreviewImage::VARIANT]);

        $I->seeResponseCodeIs(200);
        $size = getimagesizefromstring($I->grabPageSource());
        Assert::assertLessThanOrEqual(Yii::$app->getModule('file')->imagePreviewMaxWidth, $size[0]);
        Assert::assertTrue($file->store->has(PreviewImage::VARIANT));
    }

    private function createImageFile(string $content): File
    {
        $file = new File();
        $file->file_name = 'test.jpeg';
        $file->mime_type = 'image/jpeg';
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

        return (string)ob_get_clean();
    }
}
