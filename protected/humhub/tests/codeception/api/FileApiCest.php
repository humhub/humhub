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
 * The file API (`humhub\modules\file\controllers\api\FileController`) — the endpoint the Vue
 * `UploadField` posts to.
 *
 * Uploading is a batch operation, so the response reports per-file outcomes instead of
 * failing as a whole; these tests pin that contract (see the controller's own docblock and
 * `docs/develop/concept-api.md`).
 *
 * See `CommentApiCest` for why each test uses a single identity.
 */
class FileApiCest
{
    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    /**
     * Writes a file into the runtime directory and returns its path — the stand-in for the
     * browser's multipart part.
     */
    private function tempFile(string $name, string $content): string
    {
        $path = Yii::getAlias('@runtime/api-upload-test');
        \yii\helpers\FileHelper::createDirectory($path);
        $file = $path . DIRECTORY_SEPARATOR . $name;
        file_put_contents($file, $content);

        return $file;
    }

    /**
     * An unattached file row owned by `$userId`, without going through an upload — for the
     * ownership case that would otherwise need a second session in the same test.
     */
    private function seedFile(int $userId): int
    {
        $file = new File();
        $file->file_name = 'seeded.txt';
        $file->mime_type = 'text/plain';
        $file->size = 6;
        $file->created_by = $userId;
        $file->updated_by = $userId;
        $file->save(false);

        return $file->id;
    }

    public function testUploadSingleFile(ApiTester $I)
    {
        $I->wantTo('upload one file and get its serialized shape back');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('file', [], ['files' => [$this->tempFile('single.txt', 'hello upload')]]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'errors' => [],
            'results' => [
                [
                    'fileName' => 'single.txt',
                    'mimeType' => 'text/plain',
                    'size' => 12,
                    'previewUrl' => null,
                ],
            ],
        ]);

        $guid = $I->grabDataFromResponseByJsonPath('$.results[0].guid')[0];
        $id = (int)$I->grabDataFromResponseByJsonPath('$.results[0].id')[0];
        Assert::assertNotEmpty($guid);

        $file = File::findOne(['id' => $id]);
        Assert::assertNotNull($file, 'the upload was stored');
        Assert::assertEquals(1, $file->created_by);
        // Not attached to anything: the client submits the guid list with its own form.
        Assert::assertEmpty($file->object_model);
    }

    public function testUploadMultipleFiles(ApiTester $I)
    {
        $I->wantTo('upload several files in one request');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('file', [], ['files' => [
            $this->tempFile('first.txt', 'one'),
            $this->tempFile('second.txt', 'two'),
        ]]);

        $I->seeResponseCodeIs(200);
        $names = $I->grabDataFromResponseByJsonPath('$.results[*].fileName');
        Assert::assertSame(['first.txt', 'second.txt'], $names);
        Assert::assertSame([], $I->grabDataFromResponseByJsonPath('$.errors')[0]);
    }

    public function testUploadReportsPerFileErrorsAndKeepsTheValidOnes(ApiTester $I)
    {
        $I->wantTo('see a rejected file reported per file while the valid one is stored');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $fileModule = Yii::$app->getModule('file');
        $previousMaxSize = $fileModule->settings->get('maxFileSize');
        $fileModule->settings->set('maxFileSize', 10);

        try {
            $I->sendPost('file', [], ['files' => [
                $this->tempFile('small.txt', 'ok'),
                $this->tempFile('big.txt', str_repeat('x', 200)),
            ]]);

            $I->seeResponseCodeIs(200);
            Assert::assertSame(['small.txt'], $I->grabDataFromResponseByJsonPath('$.results[*].fileName'));
            Assert::assertSame(['big.txt'], $I->grabDataFromResponseByJsonPath('$.errors[*].fileName'));
            Assert::assertNotEmpty($I->grabDataFromResponseByJsonPath('$.errors[0].messages')[0]);
        } finally {
            $fileModule->settings->set('maxFileSize', $previousMaxSize);
        }
    }

    public function testUploadWithoutAnyFileIsARequestError(ApiTester $I)
    {
        $I->wantTo('get a validation error when the request carries no file at all');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('file');

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.files');
    }

    public function testDeleteOwnFile(ApiTester $I)
    {
        $I->wantTo('delete a file I uploaded myself');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('file', [], ['files' => [$this->tempFile('deleteme.txt', 'bye')]]);
        $I->seeResponseCodeIs(200);
        $id = (int)$I->grabDataFromResponseByJsonPath('$.results[0].id')[0];

        $I->sendDelete("file/$id");
        $I->seeResponseCodeIs(204);
        Assert::assertNull(File::findOne(['id' => $id]), 'the file record is gone');

        $I->sendDelete("file/$id");
        $I->seeResponseCodeIs(404);
    }

    public function testCannotDeleteSomebodyElsesFile(ApiTester $I)
    {
        $I->wantTo('be refused when deleting a file uploaded by another user');
        // The other user's file is seeded in-process rather than uploaded through a second
        // session: an identity cannot be switched mid-test (see the class docblock).
        $id = $this->seedFile(2);

        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendDelete("file/$id");
        $I->seeResponseCodeIs(403);
        Assert::assertNotNull(File::findOne(['id' => $id]), 'the file survived the refused delete');
    }

    public function testGuestsCannotUploadOrDelete(ApiTester $I)
    {
        $I->wantTo('be rejected as a guest');

        $I->sendPost('file', [], ['files' => [$this->tempFile('guest.txt', 'nope')]]);
        $I->seeResponseCodeIs(401);

        $I->sendDelete('file/1');
        $I->seeResponseCodeIs(401);
    }

    public function testWrongVerbsDoNotReachTheController(ApiTester $I)
    {
        $I->wantTo('find no GET route on the file endpoints');
        $I->amLoggedInAs(1);

        $I->sendGet('file');
        $I->seeResponseCodeIs(404);

        $I->sendGet('file/1');
        $I->seeResponseCodeIs(404);
    }
}
