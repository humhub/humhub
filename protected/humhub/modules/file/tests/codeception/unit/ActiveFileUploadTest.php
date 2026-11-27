<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\models\File;
use humhub\modules\file\widgets\ActiveFileUpload;
use humhub\widgets\form\ActiveForm;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\DynamicModel;
use yii\helpers\Json;

class ActiveFileUploadTest extends HumHubDbTestCase
{
    public function testThePreviewStartsWithTheImageOfTheField()
    {
        $file = $this->createFile('cover.jpg');

        $preview = $this->grabPreview($this->renderField($file->guid));

        $this->assertStringContainsString('data-ui-widget="ActiveFileUpload.Preview"', $preview);
        $this->assertStringContainsString('class="img-uploader-preview mt-3"', $preview);
        $this->assertStringNotContainsString('display:none', $preview);

        $files = $this->grabInitData($preview);
        $this->assertCount(1, $files);
        $this->assertSame($file->guid, $files[0]['guid']);
        $this->assertSame('cover.jpg', $files[0]['name']);
        $this->assertArrayHasKey('thumbnailUrl', $files[0]);
    }

    public function testThePreviewHasNoFileRows()
    {
        $html = $this->renderField($this->createFile('cover.jpg')->guid);

        $this->assertStringNotContainsString('<upload-list', $html);
        $this->assertStringNotContainsString('post-file-list', $html);
    }

    public function testThePreviewIsHiddenWithoutAFile()
    {
        $preview = $this->grabPreview($this->renderField(null));

        $this->assertStringContainsString('display:none', $preview);
        $this->assertStringNotContainsString('data-ui-init', $preview);
    }

    private function renderField(?string $guid): string
    {
        $model = new DynamicModel(['coverImageGuid' => $guid]);

        // The form's own output is not needed
        ob_start();
        $form = ActiveForm::begin(['action' => '/']);
        $html = ActiveFileUpload::widget([
            'id' => 'cover',
            'model' => $model,
            'attribute' => 'coverImageGuid',
            'field' => $form->field($model, 'coverImageGuid'),
            'accept' => 'image/*',
        ]);
        ActiveForm::end();
        ob_end_clean();

        return $html;
    }

    private function grabPreview(string $html): string
    {
        $this->assertSame(1, preg_match('/<div id="cover-preview"[^>]*>/', $html, $matches), 'no preview in: ' . $html);

        return $matches[0];
    }

    private function grabInitData(string $preview): array
    {
        $this->assertSame(1, preg_match("/ data-ui-init='([^']*)'/", $preview, $matches), 'no init data in: ' . $preview);

        return Json::decode(html_entity_decode($matches[1], ENT_QUOTES));
    }

    private function createFile(string $fileName): File
    {
        self::becomeUser('Admin');

        $file = new File();
        $file->file_name = $fileName;
        $this->assertTrue($file->save());
        $file->refresh();

        return $file;
    }
}
