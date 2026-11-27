<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\models\File;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\Upload;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Json;

class FilePreviewTest extends HumHubDbTestCase
{
    public function testRendersTheContainerOfTheWidgetWithTheIslandInside()
    {
        $html = FilePreview::widget([
            'id' => 'post-files',
            'items' => [$this->createFile('notes.txt')],
            'options' => ['style' => 'margin-top:10px'],
        ]);

        $this->assertMatchesRegularExpression('/^<div id="post-files" class="post-file-list"[^>]* data-ui-widget="file.Preview"/', $html);
        // Shown, it has a row
        $this->assertStringContainsString('style="margin-top:10px"', $html);
        $this->assertStringNotContainsString('display:none', $html);
        $this->assertMatchesRegularExpression('/<upload-list[^>]* data-upload-bridge[ >]/', $html);
        $this->assertStringEndsWith('</upload-list></div>', $html);
    }

    public function testHidesTheContainerWithoutFiles()
    {
        $html = FilePreview::widget(['id' => 'post-files']);

        $this->assertStringContainsString('style="display:none"', $html);
        $this->assertSame([], $this->grabProps($html)['items']);
    }

    public function testHonoursTheJsWidgetAndTheOptions()
    {
        $html = FilePreview::widget([
            'id' => 'image-preview',
            'jsWidget' => 'custom_pages.template.ImagePreview',
            'options' => ['class' => 'col-md-8 previewContainer'],
        ]);

        $this->assertStringContainsString('data-ui-widget="custom_pages.template.ImagePreview"', $html);
        $this->assertStringContainsString('class="col-md-8 previewContainer"', $html);
    }

    public function testTheIslandStartsWithTheFilesInTheApiShapeKeyedByGuid()
    {
        $file = $this->createFile('notes.txt');

        $props = $this->grabProps(FilePreview::widget(['items' => [$file->guid, null]]));

        $this->assertCount(1, $props['items']);
        $this->assertSame($file->guid, $props['items'][0]['key']);
        $this->assertSame([
            'id',
            'guid',
            'mimeType',
            'size',
            'fileName',
            'mimeIcon',
            'icon',
            'url',
            'downloadUrl',
            'previewUrl',
            'displayUrl',
            'width',
            'height',
        ], array_keys($props['items'][0]['file']));
        $this->assertSame('notes.txt', $props['items'][0]['file']['fileName']);
    }

    public function testRemoveInAFormDownloadOtherwise()
    {
        $file = $this->createFile('notes.txt');

        $form = FilePreview::widget(['items' => [$file], 'edit' => true]);
        $this->assertStringContainsString('action="remove"', $form);
        // A form's widget answers the island's remove requests even without an upload button
        $this->assertStringContainsString(' data-ui-init', $form);
        $this->assertStringContainsString(' data-file-edit', $form);

        $list = FilePreview::widget(['items' => [$file]]);
        $this->assertStringContainsString('action="download"', $list);
        $this->assertStringNotContainsString('data-ui-init', $list);
    }

    public function testThePopoverPositionReachesTheRows()
    {
        $file = $this->createFile('photo.jpg');

        $this->assertStringContainsString('popover-placement="right"', FilePreview::widget(['items' => [$file]]));
        $this->assertStringContainsString('popover-placement="top"', FilePreview::widget(['items' => [$file], 'popoverPosition' => 'top']));
        // No preview on hover
        $this->assertStringContainsString('popover-placement=""', FilePreview::widget(['items' => [$file], 'preventPopover' => true]));
    }

    public function testMediaFilesAreOnlyExcludedWithoutEdit()
    {
        $items = [$this->createFile('photo.jpg'), $this->createFile('notes.txt')];

        $list = $this->grabProps(FilePreview::widget(['items' => $items, 'excludeMediaFilesPreview' => true]));
        $this->assertSame(['notes.txt'], array_map(fn($item) => $item['file']['fileName'], $list['items']));

        $form = $this->grabProps(FilePreview::widget(['items' => $items, 'excludeMediaFilesPreview' => true, 'edit' => true]));
        $this->assertSame(['photo.jpg', 'notes.txt'], array_map(fn($item) => $item['file']['fileName'], $form['items']));

        // Nothing left to show
        $this->assertStringContainsString('display:none', FilePreview::widget([
            'items' => [$items[0]],
            'excludeMediaFilesPreview' => true,
        ]));
    }

    public function testTheUploadHelperRendersTheFilesOfAForm()
    {
        $file = $this->createFile('notes.txt');

        $html = Upload::withName('fileList[]', ['id' => 'task-files'])->preview(['items' => [$file]]);

        $this->assertStringContainsString('id="task-files_preview"', $html);
        $this->assertStringContainsString('action="remove"', $html);
        $this->assertSame($file->guid, $this->grabProps($html)['items'][0]['key']);
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

    private function grabProps(string $html): array
    {
        $this->assertSame(1, preg_match('/<upload-list[^>]* props="([^"]*)"/', $html, $matches), 'no props attribute in: ' . $html);

        return Json::decode(html_entity_decode($matches[1], ENT_QUOTES));
    }
}
