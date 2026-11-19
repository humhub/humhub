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

class PostEmptyMessageTest extends HumHubDbTestCase
{
    public function testEditFormRejectsEmptyMessageWithHiddenFileOnly()
    {
        $this->becomeUser('User2');

        $post = $this->createPost('Text with a file');
        $hidden = $this->createFile(['show_in_stream' => 0]);
        $post->fileManager->attach($hidden);
        $post = Post::findOne(['id' => $post->id]);

        // The embedded image was removed from the text, but the hidden file is still attached to the post
        $form = new PostEditForm(['post' => $post, 'fileList' => [$hidden->guid]]);
        $post->message = '';
        $this->assertNotTrue($form->validate());
        $this->assertTrue($post->hasErrors('message'));
    }

    public function testEditFormAllowsEmptyMessageWithVisibleFile()
    {
        $this->becomeUser('User2');

        $post = $this->createPost('Text with a file');
        $visible = $this->createFile(['show_in_stream' => 1]);
        $post->fileManager->attach($visible);
        $post = Post::findOne(['id' => $post->id]);

        $form = new PostEditForm(['post' => $post, 'fileList' => []]);
        $post->message = '';
        $this->assertTrue($form->validate());
    }

    private function createPost(string $message): Post
    {
        $post = new Post(['message' => $message]);
        $post->silentContentCreation = true;
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $this->assertTrue($post->save());

        return $post;
    }

    private function createFile(array $attributes = []): File
    {
        $file = new File();
        $file->file_name = 'test.txt';
        $this->assertTrue($file->save());
        $file->updateAttributes(array_merge(['created_by' => 3], $attributes));

        return $file;
    }
}
