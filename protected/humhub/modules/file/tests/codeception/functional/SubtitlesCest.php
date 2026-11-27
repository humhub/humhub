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
use Yii;
use yii\helpers\Url;

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

    private const VTT = "WEBVTT\n\n00:00:01.000 --> 00:00:03.500\nHello\n";

    public function testSavesUpdatesAndDeletesSubtitles(FunctionalTester $I)
    {
        $file = $this->createFile('video/mp4');
        $I->amAdmin();

        $this->post($I, 'save', $file, ['name' => 'subtitles-de.vtt', 'content' => self::VTT]);
        $I->seeResponseCodeIs(201);
        $json = json_decode($I->grabPageSource(), true);
        Assert::assertSame(['name', 'persistent', 'mimeType', 'size', 'updatedAt', 'url'], array_keys($json));
        Assert::assertSame('subtitles-de.vtt', $json['name']);
        Assert::assertTrue($json['persistent']);
        Assert::assertSame('text/vtt', $json['mimeType']);
        Assert::assertSame(strlen(self::VTT), $json['size']);
        Assert::assertSame(self::VTT, File::findOne(['id' => $file->id])->store->getContent('subtitles-de.vtt'));

        // The kind is in the name: the same language as captions is another variant
        $this->post($I, 'save', $file, ['name' => 'captions-de.vtt', 'content' => self::VTT]);
        $I->seeResponseCodeIs(201);

        $this->post($I, 'save', $file, ['name' => 'subtitles-de.vtt', 'content' => "1\n00:00:01,000 --> 00:00:02,000\nHi\n"]);
        $I->seeResponseCodeIs(200);
        Assert::assertSame("WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHi\n", File::findOne(['id' => $file->id])->store->getContent('subtitles-de.vtt'));

        $this->post($I, 'delete', $file, ['name' => 'subtitles-de.vtt']);
        $I->seeResponseCodeIs(200);
        Assert::assertFalse(File::findOne(['id' => $file->id])->store->has('subtitles-de.vtt'));
        Assert::assertTrue(File::findOne(['id' => $file->id])->store->has('captions-de.vtt'));

        $this->post($I, 'delete', $file, ['name' => 'subtitles-de.vtt']);
        $I->seeResponseCodeIs(404);
    }

    public function testRefusesInvalidInput(FunctionalTester $I)
    {
        $file = $this->createFile('video/mp4');
        $I->amAdmin();

        $this->post($I, 'save', $file, ['name' => 'subtitles-de.vtt', 'content' => "WEBVTT\n\n00:00:05.000 --> 00:00:02.000\nHi"]);
        $I->seeResponseCodeIs(422);
        Assert::assertSame(
            ['errors' => ['content' => ['Line 3: The start of the cue must be before its end.']]],
            json_decode($I->grabPageSource(), true),
        );

        foreach (['other.vtt', 'preview-image', 'old-1', 'subtitles-de.txt'] as $name) {
            $this->post($I, 'save', $file, ['name' => $name, 'content' => self::VTT]);
            $I->seeResponseCodeIs(422);
            Assert::assertArrayHasKey('name', json_decode($I->grabPageSource(), true)['errors']);
        }

        $this->post($I, 'save', $file, ['name' => 'subtitles-de.vtt']);
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('content', json_decode($I->grabPageSource(), true)['errors']);

        $this->post($I, 'save', $file, ['name' => 'subtitles-de.vtt', 'content' => "WEBVTT\n\n" . str_repeat('x', 1048576)]);
        $I->seeResponseCodeIs(422);
        Assert::assertArrayHasKey('content', json_decode($I->grabPageSource(), true)['errors']);

        // Nothing but the writable names is deleted
        $file->store->setContent('x', 'preview-image');
        $this->post($I, 'delete', $file, ['name' => 'preview-image']);
        $I->seeResponseCodeIs(422);
        Assert::assertTrue(File::findOne(['id' => $file->id])->store->has('preview-image'));
    }

    public function testNonMediaFileIsNotFoundForWrites(FunctionalTester $I)
    {
        $file = $this->createFile('text/plain');
        $I->amAdmin();

        $this->post($I, 'save', $file, ['name' => 'subtitles-de.vtt', 'content' => self::VTT]);

        $I->seeResponseCodeIs(404);
    }

    public function testUserWhoCannotEditMayNotWrite(FunctionalTester $I)
    {
        $file = $this->createFile('video/mp4');
        (new \humhub\modules\file\libs\FileVariants($file))->put('subtitles-de.vtt', self::VTT);
        $I->amUser3();

        $this->post($I, 'save', $file, ['name' => 'subtitles-en.vtt', 'content' => self::VTT]);
        $I->seeResponseCodeIs(403);

        $this->post($I, 'delete', $file, ['name' => 'subtitles-de.vtt']);
        $I->seeResponseCodeIs(403);
        Assert::assertTrue(File::findOne(['id' => $file->id])->store->has('subtitles-de.vtt'));
    }

    public function testWritesArePostOnly(FunctionalTester $I)
    {
        $file = $this->createFile('video/mp4');
        $I->amAdmin();

        $I->amOnRoute('/file/subtitles/save', ['guid' => $file->guid]);
        $I->seeResponseCodeIs(405);

        $I->amOnRoute('/file/subtitles/delete', ['guid' => $file->guid]);
        $I->seeResponseCodeIs(405);
    }

    public function testGuestsCannotWrite(FunctionalTester $I)
    {
        $file = $this->createFile('video/mp4');

        $this->post($I, 'save', $file, ['name' => 'subtitles-de.vtt', 'content' => self::VTT]);

        Assert::assertContains(Yii::$app->response->statusCode, [302, 401, 403]);
        Assert::assertFalse(File::findOne(['id' => $file->id])->store->has('subtitles-de.vtt'));
    }

    private function post(FunctionalTester $I, string $action, File $file, array $params): void
    {
        $I->sendAjaxPostRequest(Url::to(['/file/subtitles/' . $action, 'guid' => $file->guid]), $params);
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
