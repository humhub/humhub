<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace post\functional;

use humhub\modules\file\models\File;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use PHPUnit\Framework\Assert;
use post\FunctionalTester;

/**
 * Files hidden in the stream (uploaded by the rich text editor) belong to a new post only while its message references them
 */
class PostHiddenFilesCest
{
    public function testCreateAttachesHiddenFilesOnlyWhenReferenced(FunctionalTester $I)
    {
        $I->wantTo('see that a hidden file missing in the message is not attached to the new post');
        // User1 is the admin of space 2, so he may create posts there
        $I->amUser1();

        $referenced = $this->createFile(2, 0);
        $unreferenced = $this->createFile(2, 0);
        $visible = $this->createFile(2, 1);

        $message = 'Created with a hidden file ![](file-guid:' . $referenced->guid . ')';
        $I->sendAjaxPostRequest(Space::findOne(['id' => 2])->createUrl('/post/post/post'), [
            'Post' => ['message' => $message],
            'fileList' => [$referenced->guid, $unreferenced->guid, $visible->guid],
        ]);

        $post = Post::findOne(['message' => $message]);
        Assert::assertNotNull($post);
        Assert::assertTrue(File::findOne(['guid' => $referenced->guid])->isAssignedTo($post));
        Assert::assertTrue(File::findOne(['guid' => $visible->guid])->isAssignedTo($post));
        Assert::assertFalse(File::findOne(['guid' => $unreferenced->guid])->isAssigned());
    }

    private function createFile(int $userId, int $showInStream): File
    {
        $file = new File();
        $file->file_name = 'test.png';
        $file->save();
        $file->updateAttributes(['created_by' => $userId, 'show_in_stream' => $showInStream]);

        return $file;
    }
}
