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
 * The variants of the file API (`humhub\modules\file\controllers\api\FileController`): the read-only list of the
 * files stored next to a file's content.
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

    public function testListsAllVariantsByName(ApiTester $I)
    {
        $I->wantTo('list all variants of my video, with their kind of storage only');
        $id = $this->seedFile(1);
        $file = File::findOne(['id' => $id]);
        $file->store->setContent(self::VTT, 'subtitles-de.vtt');
        $file->store->setContent('pic', 'preview-image');
        $file->store->setContent('old', 'old-3');
        $I->amLoggedInAs(1);

        $I->sendGet("file/$id/variants");

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['old-3', 'preview-image', 'subtitles-de.vtt'], $I->grabDataFromResponseByJsonPath('$.results[*].name'));
        Assert::assertSame([true, false, true], $I->grabDataFromResponseByJsonPath('$.results[*].persistent'));
        $I->seeResponseContainsJson(['name' => 'subtitles-de.vtt', 'persistent' => true, 'mimeType' => 'text/vtt', 'size' => strlen(self::VTT)]);
        $I->seeResponseJsonMatchesJsonPath('$.results[0].updatedAt');
        Assert::assertStringContainsString('variant=subtitles-de.vtt', $I->grabDataFromResponseByJsonPath('$.results[2].url')[0]);
        $I->dontSeeResponseJsonMatchesJsonPath('$.results[0].type');
        $I->dontSeeResponseJsonMatchesJsonPath('$.results[0].generated');
    }

    public function testNeedsTheRightToSeeTheFile(ApiTester $I)
    {
        // Somebody else's unattached file: not viewable
        $id = $this->seedFile(2);
        $I->amLoggedInAs(1);

        $I->sendGet("file/$id/variants");
        $I->seeResponseCodeIs(403);

        $I->sendGet('file/999999/variants');
        $I->seeResponseCodeIs(404);
    }

    public function testViewersMayRead(ApiTester $I)
    {
        $I->wantTo('read the variants of a post\'s file');
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
        (new \humhub\modules\file\libs\FileVariants($file))->put('subtitles-de.vtt', self::VTT);

        $I->amLoggedInAs(3);

        $I->sendGet("file/{$file->id}/variants");
        $I->seeResponseCodeIs(200);
        Assert::assertSame(['subtitles-de.vtt'], $I->grabDataFromResponseByJsonPath('$.results[*].name'));
    }

    public function testVariantsCannotBeWrittenThroughTheApi(ApiTester $I)
    {
        $id = $this->seedFile(1);
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPut("file/$id/variants/subtitles-de.vtt", json_encode(['content' => self::VTT]));
        $I->seeResponseCodeIs(404);

        $I->sendDelete("file/$id/variants/subtitles-de.vtt");
        $I->seeResponseCodeIs(404);

        $I->sendPost('file/1/variants');
        $I->seeResponseCodeIs(404);
    }
}
