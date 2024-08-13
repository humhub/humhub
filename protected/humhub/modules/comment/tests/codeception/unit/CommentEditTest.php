<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\modules\comment\models\Comment;
use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\post\models\Post;

class CommentEditTest extends HumHubDbTestCase
{

    public function testNewCommentIsNotEdited()
    {
        $this->becomeUser('User2');
        $comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::class,
            'object_id' => 11
        ]);

        $this->assertTrue($comment->save());
        $this->assertFalse($comment->isUpdated());

        // Reload content
        $comment = Comment::findOne(['id' => $comment->id]);
        $this->assertFalse($comment->content->isUpdated());
    }

    public function testEditedContentIsEdited()
    {
        $this->becomeUser('User2');
        $comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::class,
            'object_id' => 11
        ]);

        $this->assertTrue($comment->save());

        // Wait a second in order to prevent created_at = edited_at
        sleep(1);

        // Reload content
        $comment = Comment::findOne(['id' => $comment->id]);
        $comment->message = 'Updated Message';
        $this->assertTrue($comment->save());

        // See https://github.com/humhub/humhub/issues/4381
        $comment->refresh();
        $this->assertTrue($comment->isUpdated());

        // Reload content
        $comment = Comment::findOne(['id' => $comment->id]);
        $this->assertTrue($comment->isUpdated());
    }

}
