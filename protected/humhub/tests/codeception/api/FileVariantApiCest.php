<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\file\models\File;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The persistent variants of the file API (`humhub\modules\file\controllers\api\FileController`): the subtitles of a
 * video or audio file.
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class FileVariantApiCest
{
    private const VTT = "WEBVTT\n\n00:00:01.000 --> 00:00:03.500\nHello\n";

    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    /**
     * An unattached file row owned by `$userId`: viewable and editable by its creator only
     */
    private function seedFile(int $userId, string $mimeType = 'video/mp4'): int
    {
        $file = new File();
        $file->file_name = 'clip.mp4';
        $file->mime_type = $mimeType;
        $file->size = 6;
        $file->created_by = $userId;
        $file->updated_by = $userId;
        $file->save(false);

        return $file->id;
    }

    private function put(ApiTester $I, int $id, string $name, array $body): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut("file/$id/variants/$name", json_encode($body));
    }

    public function testCreatesUpdatesListsAndDeletesAVariant(ApiTester $I)
    {
        $I->wantTo('manage the subtitles of my video');
        $id = $this->seedFile(1);
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $this->put($I, $id, 'subtitles-de.vtt', ['content' => self::VTT, 'kind' => 'captions']);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'name' => 'subtitles-de.vtt',
            'type' => 'subtitles',
            'language' => 'de',
            'label' => 'Deutsch',
            'kind' => 'captions',
            'generated' => false,
            'size' => strlen(self::VTT),
        ]);
        $I->seeResponseJsonMatchesJsonPath('$.updatedAt');
        Assert::assertStringContainsString('variant=subtitles-de.vtt', $I->grabDataFromResponseByJsonPath('$.url')[0]);
        Assert::assertSame(self::VTT, File::findOne(['id' => $id])->store->getContent('subtitles-de.vtt'));

        $this->put($I, $id, 'subtitles-en.vtt', ['content' => self::VTT, 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(201);

        $this->put($I, $id, 'subtitles-de.vtt', ['content' => self::VTT, 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['kind' => 'subtitles']);

        $I->sendGet("file/$id/variants");
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['subtitles-de.vtt', 'subtitles-en.vtt'], $I->grabDataFromResponseByJsonPath('$.results[*].name'));
        Assert::assertSame(['Deutsch', 'English'], $I->grabDataFromResponseByJsonPath('$.results[*].label'));

        $I->sendDelete("file/$id/variants/subtitles-de.vtt");
        $I->seeResponseCodeIs(204);
        Assert::assertFalse(File::findOne(['id' => $id])->store->has('subtitles-de.vtt'));

        $I->sendDelete("file/$id/variants/subtitles-de.vtt");
        $I->seeResponseCodeIs(404);

        $I->sendGet("file/$id/variants");
        Assert::assertSame(['subtitles-en.vtt'], $I->grabDataFromResponseByJsonPath('$.results[*].name'));
    }

    public function testInvalidContentReportsTheLines(ApiTester $I)
    {
        $id = $this->seedFile(1);
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $this->put($I, $id, 'subtitles-de.vtt', ['content' => "WEBVTT\n\n00:00:05.000 --> 00:00:02.000\nHi", 'kind' => 'subtitles']);

        $I->seeResponseCodeIs(422);
        $I->seeResponseContainsJson(['errors' => ['content' => ['Line 3: The start of the cue must be before its end.']]]);
        Assert::assertFalse(File::findOne(['id' => $id])->store->has('subtitles-de.vtt'));
    }

    public function testConvertsSrt(ApiTester $I)
    {
        $id = $this->seedFile(1);
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $this->put($I, $id, 'subtitles-de.vtt', ['content' => "1\n00:00:01,000 --> 00:00:02,000\nHi\n", 'kind' => 'subtitles']);

        $I->seeResponseCodeIs(201);
        Assert::assertSame("WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHi\n", File::findOne(['id' => $id])->store->getContent('subtitles-de.vtt'));
    }

    public function testRefusesUnknownNamesNonMediaFilesAndTooLargeTexts(ApiTester $I)
    {
        $media = $this->seedFile(1);
        $text = $this->seedFile(1, 'text/plain');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $this->put($I, $media, 'other.vtt', ['content' => self::VTT, 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.name');

        $this->put($I, $text, 'subtitles-de.vtt', ['content' => self::VTT, 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.name');

        $this->put($I, $media, 'subtitles-de.vtt', ['content' => self::VTT, 'kind' => 'karaoke']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.kind');

        $this->put($I, $media, 'subtitles-de.vtt', ['kind' => 'subtitles']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.content');

        $this->put($I, $media, 'subtitles-de.vtt', ['content' => "WEBVTT\n\n" . str_repeat('x', 1048576), 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.content');
    }

    public function testNeedsTheRightToEditTheFile(ApiTester $I)
    {
        // Somebody else's unattached file: not even viewable, so reading is refused as well
        $id = $this->seedFile(2);
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $this->put($I, $id, 'subtitles-de.vtt', ['content' => self::VTT, 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(403);

        $I->sendDelete("file/$id/variants/subtitles-de.vtt");
        $I->seeResponseCodeIs(403);

        $I->sendGet("file/$id/variants");
        $I->seeResponseCodeIs(403);

        $this->put($I, 999999, 'subtitles-de.vtt', ['content' => self::VTT, 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(404);
    }

    public function testViewersMayReadButNotWrite(ApiTester $I)
    {
        $I->wantTo('read the subtitles of a post\'s file without being allowed to edit them');
        $file = new File();
        $file->file_name = 'clip.mp4';
        $file->mime_type = 'video/mp4';
        $file->created_by = 1;
        // Content 10: a public post in Space 2, where user 2 is a member
        $post = \humhub\modules\content\models\Content::findOne(['id' => 10])->getModel();
        $file->object_model = $post::class;
        $file->object_id = $post->getPrimaryKey();
        $file->save();
        $file->setStoredFileContent('video');
        (new \humhub\modules\file\libs\FileVariants($file))->put('subtitles-de.vtt', self::VTT, 'subtitles');

        // User 3 sees the public post but does not edit it
        $I->amLoggedInAs(3);
        $this->withCsrf($I);

        $I->sendGet("file/{$file->id}/variants");
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['subtitles-de.vtt'], $I->grabDataFromResponseByJsonPath('$.results[*].name'));

        $this->put($I, $file->id, 'subtitles-en.vtt', ['content' => self::VTT, 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(403);
    }

    public function testGuestsCannotWrite(ApiTester $I)
    {
        $this->put($I, 1, 'subtitles-de.vtt', ['content' => self::VTT, 'kind' => 'subtitles']);
        $I->seeResponseCodeIs(401);

        $I->sendDelete('file/1/variants/subtitles-de.vtt');
        $I->seeResponseCodeIs(401);
    }

    public function testWrongVerbsDoNotReachTheController(ApiTester $I)
    {
        $I->amLoggedInAs(1);

        $I->sendPost('file/1/variants');
        $I->seeResponseCodeIs(404);

        $I->sendGet('file/1/variants/subtitles-de.vtt');
        $I->seeResponseCodeIs(404);
    }
}
