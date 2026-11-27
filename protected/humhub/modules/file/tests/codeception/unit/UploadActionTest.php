<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\actions\UploadAction;
use humhub\modules\file\controllers\FileController;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileUpload;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class UploadActionTest extends HumHubDbTestCase
{
    public function testErrorResponseNamesTheReasonsOfTheUploadedFile()
    {
        $file = new FileUpload(['file_name' => 'movie.mp4']);
        $file->addError('uploadedFile', 'Only files with these extensions are allowed: pdf.');
        $file->addError('file_name', 'Not reported beside the reason of the uploaded file.');

        $response = $this->errorResponse($file);

        $this->assertTrue($response['error']);
        $this->assertSame(['Only files with these extensions are allowed: pdf.'], $response['errors']);
        $this->assertSame('movie.mp4', $response['name']);
    }

    public function testErrorResponseNamesTheReasonsOfOtherAttributes()
    {
        $file = new FileUpload(['file_name' => str_repeat('a', 300) . '.mp4', 'mime_type' => 'video/mp4']);
        $this->assertFalse($file->validate(['file_name', 'mime_type']));
        $file->addError('size', 'The file is too big.');

        $response = $this->errorResponse($file);

        $this->assertSame([
            $file->getFirstError('file_name'),
            'The file is too big.',
        ], $response['errors']);
        $this->assertStringContainsString('255', $response['errors'][0]);
    }

    public function testErrorResponseWithoutAReasonSaysTheFileCouldNotBeUploaded()
    {
        $file = new FileUpload(['file_name' => 'a&b.pdf']);

        $response = $this->errorResponse($file);

        $this->assertSame('File a&amp;b.pdf could not be uploaded!', $response['errors']);
        $this->assertSame('a&amp;b.pdf', $response['name']);
    }

    private function errorResponse(File $file): array
    {
        $action = new class ('upload', new FileController('file', Yii::$app->getModule('file'))) extends UploadAction {
            public function errorResponse(File $file): array
            {
                return $this->getErrorResponse($file);
            }
        };

        return $action->errorResponse($file);
    }
}
