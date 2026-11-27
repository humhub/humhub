<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace file\functional;

use file\FunctionalTester;
use humhub\modules\file\libs\FileVariants;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * A subtitles variant is served as WebVTT, inline and from PHP
 */
class SubtitlesDownloadCest
{
    private const VTT = "WEBVTT\n\n00:00:01.000 --> 00:00:03.500\nHello\n";

    public function testSubtitlesAreServedAsInlineWebVtt(FunctionalTester $I)
    {
        $I->wantTo('get the subtitles as text/vtt without a redirect');
        $I->amAdmin();
        $file = $this->createMediaFile();

        $I->amOnRoute('/file/file/download', ['guid' => $file->guid, 'variant' => 'subtitles-de.vtt']);

        $I->seeResponseCodeIs(200);
        Assert::assertSame('text/vtt; charset=utf-8', Yii::$app->response->headers->get('Content-Type'));
        Assert::assertStringStartsWith('inline;', Yii::$app->response->headers->get('Content-Disposition'));
        Assert::assertSame(self::VTT, $I->grabPageSource());
    }

    public function testOtherVariantsKeepTheirContentType(FunctionalTester $I)
    {
        $I->wantTo('get another variant as before');
        $I->amAdmin();
        $file = $this->createMediaFile();
        $file->store->setContent('text', 'other-variant');

        $I->amOnRoute('/file/file/download', ['guid' => $file->guid, 'variant' => 'other-variant']);

        $I->seeResponseCodeIs(200);
        Assert::assertStringStartsWith('attachment;', Yii::$app->response->headers->get('Content-Disposition'));
    }

    public function testMissingSubtitlesAreNotFound(FunctionalTester $I)
    {
        $I->amAdmin();
        $file = $this->createMediaFile();

        $I->amOnRoute('/file/file/download', ['guid' => $file->guid, 'variant' => 'subtitles-fr.vtt']);

        $I->seeResponseCodeIs(404);
    }

    private function createMediaFile(): File
    {
        $file = new File();
        $file->file_name = 'clip.mp4';
        $file->mime_type = 'video/mp4';
        $file->save();

        Post::findOne(['id' => 1])->fileManager->attach($file);
        $file->setStoredFileContent('video');
        (new FileVariants($file))->put('subtitles-de.vtt', self::VTT);

        return $file;
    }
}
