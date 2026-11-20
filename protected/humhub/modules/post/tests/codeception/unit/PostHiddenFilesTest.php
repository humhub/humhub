<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\post;

use humhub\modules\file\models\File;
use humhub\modules\post\models\forms\PostEditForm;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Files hidden in the stream (uploaded by the rich text editor) belong to a post only while its message references them
 */
class PostHiddenFilesTest extends HumHubDbTestCase
{
    public function testEditDeletesHiddenFileRemovedFromMessage()
    {
        $this->becomeUser('User2');

        $file = $this->createFile(['show_in_stream' => 0]);
        $post = $this->createPost('Text ![](file-guid:' . $file->guid . ')');
        $this->assertTrue($this->isAttached($post, $file));

        $this->edit($post, 'Text without the image');

        $this->assertNull(File::findOne(['guid' => $file->guid]));
    }

    public function testEditKeepsHiddenFileStillInMessage()
    {
        $this->becomeUser('User2');

        $file = $this->createFile(['show_in_stream' => 0]);
        $post = $this->createPost('Text ![](file-guid:' . $file->guid . ')');

        $this->edit($post, 'Changed text ![](file-guid:' . $file->guid . ')');

        $this->assertNotNull(File::findOne(['guid' => $file->guid]));
        $this->assertTrue($this->isAttached(Post::findOne(['id' => $post->id]), $file));
    }

    public function testEditDoesNotAttachHiddenFileMissingInMessage()
    {
        $this->becomeUser('User2');

        $post = $this->createPost('Text');
        $hidden = $this->createFile(['show_in_stream' => 0]);
        $visible = $this->createFile(['show_in_stream' => 1]);

        $this->edit($post, 'Text', [$hidden->guid, $visible->guid]);

        $post = Post::findOne(['id' => $post->id]);
        $this->assertFalse($this->isAttached($post, $hidden));
        $this->assertTrue($this->isAttached($post, $visible));
    }

    private function edit(Post $post, string $message, array $fileList = []): void
    {
        $post = Post::findOne(['id' => $post->id]);
        $form = new PostEditForm(['post' => $post, 'fileList' => $fileList]);
        $post->message = $message;
        $this->assertTrue($form->save());
    }

    private function isAttached(Post $post, File $file): bool
    {
        return File::findOne(['guid' => $file->guid])?->isAssignedTo($post) ?? false;
    }

    private function createPost(string $message): Post
    {
        $post = new Post(['message' => $message]);
        $post->silentContentCreation = true;
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $this->assertTrue($post->save());

        return Post::findOne(['id' => $post->id]);
    }

    private function createFile(array $attributes = []): File
    {
        $file = new File();
        $file->file_name = 'test.png';
        $this->assertTrue($file->save());
        $file->updateAttributes(array_merge(['created_by' => 3], $attributes));

        return $file;
    }
}
