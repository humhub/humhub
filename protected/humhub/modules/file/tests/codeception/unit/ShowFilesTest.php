<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\content\models\Content;
use humhub\modules\file\models\File;
use humhub\modules\file\widgets\ShowFiles;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ShowFilesTest extends HumHubDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Yii::$app->getModule('file')->settings->set('excludeMediaFilesPreview', '1');
    }

    public function testMediaFilesOfThePreviewAreExcludedFromTheFileList()
    {
        $html = ShowFiles::widget(['object' => $this->createPostWithFiles(['sound.mp3', 'clip.mp4'])]);

        $this->assertStringContainsString('jp-jplayer', $html);
        $this->assertStringContainsString('<video', $html);
        $this->assertStringContainsString('data-exclude-media-files-preview="1"', $html);
    }

    public function testMediaFileWithoutPreviewKeepsTheFileList()
    {
        $html = ShowFiles::widget(['object' => $this->createPostWithFiles(['sound.wav'])]);

        $this->assertStringNotContainsString('jp-jplayer', $html);
        $this->assertStringContainsString('data-exclude-media-files-preview="0"', $html);
    }

    public function testMediaFileWithoutPreviewNextToPreviewedMediaKeepsTheFileList()
    {
        $html = ShowFiles::widget(['object' => $this->createPostWithFiles(['sound.mp3', 'movie.avi'])]);

        $this->assertStringContainsString('jp-jplayer', $html);
        $this->assertStringContainsString('data-exclude-media-files-preview="0"', $html);
    }

    public function testOtherFileNextToPreviewedMediaKeepsTheExclusion()
    {
        $html = ShowFiles::widget(['object' => $this->createPostWithFiles(['sound.mp3', 'document.pdf'])]);

        $this->assertStringContainsString('jp-jplayer', $html);
        $this->assertStringContainsString('data-exclude-media-files-preview="1"', $html);
    }

    public function testFileListIsNotExcludedWithoutPreview()
    {
        $html = ShowFiles::widget([
            'object' => $this->createPostWithFiles(['sound.mp3']),
            'preview' => false,
        ]);

        $this->assertStringNotContainsString('jp-jplayer', $html);
        $this->assertStringContainsString('data-exclude-media-files-preview="0"', $html);
    }

    private function createPostWithFiles(array $fileNames): Post
    {
        $this->becomeUser('Admin');

        $post = new Post(Space::findOne(1), Content::VISIBILITY_PUBLIC, ['message' => 'Post with attachments']);
        $this->assertTrue($post->save());

        foreach ($fileNames as $fileName) {
            $file = new File(['file_name' => $fileName, 'show_in_stream' => 1]);
            $this->assertTrue($file->save());
            $post->fileManager->attach($file);
        }

        return $post;
    }
}
