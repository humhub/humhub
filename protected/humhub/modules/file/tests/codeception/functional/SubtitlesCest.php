<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace file\functional;

use file\FunctionalTester;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use PHPUnit\Framework\Assert;

/**
 * The modal managing the subtitles of an audio or video file
 */
class SubtitlesCest
{
    public function testUnknownFileIsNotFound(FunctionalTester $I)
    {
        $I->amAdmin();

        $this->ajaxGet($I, ['guid' => 'unknown']);

        $I->seeResponseCodeIs(404);
    }

    public function testNonMediaFileIsNotFound(FunctionalTester $I)
    {
        $I->amAdmin();
        $file = $this->createFile('text/plain');

        $this->ajaxGet($I, ['guid' => $file->guid]);

        $I->seeResponseCodeIs(404);
    }

    public function testUserWhoCannotEditIsForbidden(FunctionalTester $I)
    {
        $file = $this->createFile('video/mp4');
        $I->amUser3();

        $this->ajaxGet($I, ['guid' => $file->guid]);

        $I->seeResponseCodeIs(403);
    }

    public function testModalRendersTheManagerIsland(FunctionalTester $I)
    {
        $file = $this->createFile('video/mp4', 'Holiday <b>clip</b>.mp4');
        $I->amAdmin();

        $this->ajaxGet($I, ['guid' => $file->guid]);

        $I->seeResponseCodeIs(200);
        $source = $I->grabPageSource();
        Assert::assertStringContainsString('<subtitle-manager', $source);
        Assert::assertStringContainsString('file-id="' . $file->id . '"', $source);
        Assert::assertStringContainsString('file-name="Holiday &lt;b&gt;clip&lt;/b&gt;.mp4"', $source);
        // The platform's languages, the language picker's names, and the UI language
        Assert::assertStringContainsString('&quot;code&quot;:&quot;de&quot;,&quot;name&quot;:&quot;Deutsch&quot;', $source);
        Assert::assertStringContainsString('ui-language="', $source);
        // The title is encoded
        Assert::assertStringContainsString('Subtitles of Holiday &lt;b&gt;clip&lt;/b&gt;.mp4', $source);
        Assert::assertStringNotContainsString('<b>clip</b>', $source);
    }

    public function testDirectRequestRedirectsAndOpensTheModal(FunctionalTester $I)
    {
        $file = $this->createFile('video/mp4');
        $I->amAdmin();

        $I->amOnRoute('/file/subtitles', ['guid' => $file->guid]);

        $I->seeResponseCodeIs(200);
        $source = $I->grabPageSource();
        Assert::assertStringContainsString('openFileModal', $source);
        Assert::assertStringContainsString('file%2Fsubtitles\\u0026guid=' . $file->guid, $source);
    }

    private function ajaxGet(FunctionalTester $I, array $params): void
    {
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $I->amOnRoute('/file/subtitles', $params);
    }

    private function createFile(string $mimeType, string $fileName = 'test.mp4'): File
    {
        $file = new File();
        $file->file_name = $fileName;
        $file->mime_type = $mimeType;
        $file->save();

        Post::findOne(['id' => 1])->fileManager->attach($file);

        return $file;
    }
}
