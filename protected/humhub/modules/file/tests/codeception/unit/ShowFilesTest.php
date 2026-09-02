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
use yii\helpers\Json;

class ShowFilesTest extends HumHubDbTestCase
{
    public function testRendersNothingWithoutFiles()
    {
        $this->assertSame('', ShowFiles::widget(['object' => $this->createPost()]));
    }

    public function testRendersNothingWhenInactive()
    {
        $post = $this->createPost();
        $this->attachFile($post, 'notes.txt');

        $this->assertSame('', ShowFiles::widget(['object' => $post, 'active' => false]));
    }

    public function testRendersTheIslandMountPoint()
    {
        $post = $this->createPost();
        $this->attachFile($post, 'notes.txt');

        $html = ShowFiles::widget(['object' => $post]);

        $this->assertStringContainsString('<attached-files', $html);
        // The stream's inline edit removes `.stream-entry-addons > .hideOnEdit` (a direct
        // child selector), so the class has to sit on the mount tag itself.
        $this->assertStringContainsString('class="hideOnEdit"', $html);
        $this->assertStringContainsString('gallery-id="gallery-' . $post->getUniqueId() . '"', $html);
        $this->assertStringContainsString('preview="true"', $html);
        $this->assertStringContainsString('</attached-files>', $html);
    }

    public function testFilePropsCarryTheApiShapePlusServerSideHints()
    {
        $post = $this->createPost();
        $file = $this->attachFile($post, 'notes.txt');

        $props = $this->grabProps(ShowFiles::widget(['object' => $post]));

        $this->assertCount(1, $props['files']);
        $this->assertSame([
            'id',
            'guid',
            'mimeType',
            'size',
            'fileName',
            'mimeIcon',
            'url',
            'downloadUrl',
            'previewUrl',
        ], array_keys($props['files'][0]));
        $this->assertSame($file->guid, $props['files'][0]['guid']);
        $this->assertSame('notes.txt', $props['files'][0]['fileName']);
        // Plain download is all a .txt offers, so the entry links the file directly
        // instead of routing through the file view modal, and it is no search hit here.
        $this->assertArrayNotHasKey('viewUrl', $props['files'][0]);
        $this->assertArrayNotHasKey('highlight', $props['files'][0]);
    }

    public function testMediaExclusionFollowsTheModuleSettingAndOnlyAppliesWithPreviews()
    {
        $post = $this->createPost();
        $this->attachFile($post, 'notes.txt');

        Yii::$app->getModule('file')->settings->set('excludeMediaFilesPreview', '1');
        $this->assertStringContainsString('exclude-media="true"', ShowFiles::widget(['object' => $post]));

        // Without the media grid there is nothing the list would be duplicating.
        $html = ShowFiles::widget(['object' => $post, 'preview' => false]);
        $this->assertStringContainsString('preview="false"', $html);
        $this->assertStringContainsString('exclude-media="false"', $html);

        Yii::$app->getModule('file')->settings->set('excludeMediaFilesPreview', '0');
        $this->assertStringContainsString('exclude-media="false"', ShowFiles::widget(['object' => $post]));
    }

    private function createPost(): Post
    {
        self::becomeUser('Admin');

        $post = new Post(Space::findOne(1), Content::VISIBILITY_PUBLIC, ['message' => 'Post with attachments']);
        $this->assertTrue($post->save());

        return $post;
    }

    private function attachFile(Post $post, string $fileName): File
    {
        $file = new File();
        $file->file_name = $fileName;
        $this->assertTrue($file->save());

        $post->fileManager->attach($file);
        $file->refresh();

        return $file;
    }

    private function grabProps(string $html): array
    {
        $this->assertSame(1, preg_match('/ props="(.*?)"></', $html, $matches), 'no props attribute in: ' . $html);

        return Json::decode(html_entity_decode($matches[1], ENT_QUOTES));
    }
}
