<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\libs\UUID;
use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Throwable;

class RichTextPostProcessTest extends HumHubDbTestCase
{
    private function addTestOEmbedRecords()
    {
        (new UrlOembed([
            'url' => 'https://www.youtube.com/watch?v=yt1',
            'preview' => 'yt1',
        ]))->save();

        (new UrlOembed([
            'url' => 'https://www.youtube.com/watch?v=yt2',
            'preview' => 'yt2',
        ]))->save();
    }

    public function testProcessSingleOembed()
    {
        $this->addTestOEmbedRecords();

        $post = Post::findOne(['id' => 1]);
        $text = '[https://www.youtube.com/watch?v=yt1](oembed:https://www.youtube.com/watch?v=yt1)';
        $result = RichText::postProcess($text, $post);
        static::assertNotEmpty($result['oembed']);
        static::assertCount(1, $result['oembed']);
        static::assertEquals('https://www.youtube.com/watch?v=yt1', $result['oembed'][0]);
    }

    public function testProcessNoneOembed()
    {
        $this->addTestOEmbedRecords();

        $post = Post::findOne(['id' => 1]);
        $text = '[Normal link](https://www.youtube.com/watch?v=yt1)';
        $result = RichText::postProcess($text, $post);
        static::assertEmpty($result['oembed']);
    }

    public function testProcessMultipleOembed()
    {
        $this->addTestOEmbedRecords();

        $post = Post::findOne(['id' => 1]);
        $text = '[https://www.youtube.com/watch?v=yt1](oembed:https://www.youtube.com/watch?v=yt1)\n\n[https://www.youtube.com/watch?v=yt2](oembed:https://www.youtube.com/watch?v=yt2)';
        $result = RichText::postProcess($text, $post);
        static::assertNotEmpty($result['oembed']);
        static::assertCount(2, $result['oembed']);
        static::assertEquals('https://www.youtube.com/watch?v=yt1', $result['oembed'][0]);
        static::assertEquals('https://www.youtube.com/watch?v=yt2', $result['oembed'][1]);
    }

    public function testProcessInvalidOembed()
    {
        $post = Post::findOne(['id' => 1]);
        $text = '[https://www.nonexisting.com/](oembed:https://www.nonexisting.com/)';
        $result = RichText::postProcess($text, $post);
        static::assertEmpty($result['oembed']);
    }

    public function testProcessSingleMentioning()
    {
        $post = Post::findOne(['id' => 1]);
        $guid = User::findOne(['id' => 1])->guid;

        $text = "[](mention:${guid})";

        $result = RichText::postProcess($text, $post);
        static::assertNotEmpty($result['mention']);
        static::assertCount(1, $result['mention']);
    }

    public function testProcessMultipleMentioning()
    {
        $post = Post::findOne(['id' => 1]);
        $guid = User::findOne(['id' => 1])->guid;
        $guid2 = User::findOne(['id' => 2])->guid;

        $text = "[](mention:${guid}) and [](mention:${guid2})";

        $result = RichText::postProcess($text, $post);

        static::assertNotEmpty($result['mention']);
        static::assertCount(2, $result['mention']);
        static::assertEquals($guid, $result['mention'][0]->guid);
        static::assertEquals($guid2, $result['mention'][1]->guid);
    }

    public function testProcessInvalidMentioning()
    {
        $post = Post::findOne(['id' => 1]);

        $text = "[](mention:invalid)";

        $result = RichText::postProcess($text, $post);

        static::assertEmpty($result['mention']);
    }

    public function testProcessSingleFile()
    {
        $post = Post::findOne(['id' => 1]);

        $guid = UUID::v4();
        $file = new File([
            'guid' => $guid,
            'file_name' => 'text.txt',
            'hash_sha1' => 'xxx',
            'title' => 'Test File',
            'mime_type' => 'text/plain',
            'size' => 302176,
        ]);

        try {
            $file->save();
        } catch (Throwable $e) {
            // Need to catch since hash saving will fail
        }

        $text = '[](file-guid:' . $guid . ')';

        $result = RichText::postProcess($text, $post);

        static::assertNotEmpty($result['file-guid']);
        static::assertCount(1, $result['file-guid']);
        static::assertEquals($guid, $result['file-guid'][0]);

        $file->refresh();

        static::assertEquals($file->object_id, $post->id);
        static::assertEquals($file->object_model, Post::class);
    }

    public function testProcessMultipleFiles()
    {
        $post = Post::findOne(['id' => 1]);

        $guid = UUID::v4();
        $file = new File([
            'guid' => $guid,
            'file_name' => 'text.txt',
            'hash_sha1' => 'xxx',
            'title' => 'Test File',
            'mime_type' => 'text/plain',
            'size' => 302176,
        ]);

        $guid2 = UUID::v4();
        $file2 = new File([
            'guid' => $guid2,
            'file_name' => 'text2.txt',
            'hash_sha1' => 'xxx',
            'title' => 'Test File2',
            'mime_type' => 'text/plain',
            'size' => 302176,
        ]);

        try {
            $file->save();
        } catch (Throwable $e) {
            // Need to catch since hash saving will fail
        }

        try {
            $file2->save();
        } catch (Throwable $e) {
            // Need to catch since hash saving will fail
        }

        $text = '[](file-guid:' . $guid . ') and [](file-guid:' . $guid2 . ')';

        $result = RichText::postProcess($text, $post);

        static::assertNotEmpty($result['file-guid']);
        static::assertCount(2, $result['file-guid']);
        static::assertEquals($guid, $result['file-guid'][0]);
        static::assertEquals($guid2, $result['file-guid'][1]);

        $file->refresh();


        static::assertEquals($file->object_id, $post->id);
        static::assertEquals($file->object_model, Post::class);

        $file2->refresh();

        static::assertEquals($file2->object_id, $post->id);
        static::assertEquals($file2->object_model, Post::class);
    }

    public function testProcessInvalidFile()
    {
        $post = Post::findOne(['id' => 1]);

        $text = "[](file-guid:doesNotExist)";

        $result = RichText::postProcess($text, $post);
        static::assertEmpty($result['file-guid']);
    }

    public function testProcessDataImage()
    {
        $post = Post::findOne(['id' => 1]);

        $text = "[](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==)";

        $result = RichText::postProcess($text, $post);

        $files = $post->fileManager->findAll();
        static::assertCount(1, $files);
        static::assertNotEmpty($result['file-guid']);
        static::assertCount(1, $result['file-guid']);

        $guid = $files[0]->guid;
        $filename = $files[0]->file_name;
        static::assertEquals($guid, $result['file-guid'][0]);
        static::assertEquals("[${filename}](file-guid:${guid} \"${filename}\")", $result['text']);
    }

    public function testProcessDataImageWithSize()
    {
        $post = Post::findOne(['id' => 1]);

        $text = "[](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg== x150)";

        $result = RichText::postProcess($text, $post);

        $files = $post->fileManager->findAll();
        static::assertCount(1, $files);
        static::assertNotEmpty($result['file-guid']);
        static::assertCount(1, $result['file-guid']);

        $guid = $files[0]->guid;
        $filename = $files[0]->file_name;
        static::assertEquals($guid, $result['file-guid'][0]);
        static::assertEquals("[${filename}](file-guid:${guid} \"${filename}\" x150)", $result['text']);
    }
}
