<?php

namespace tests\codeception\unit\modules\comment\components;

use humhub\modules\comment\widgets\Comments;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\post\models\Post;
use humhub\modules\comment\models\Comment;

class SubCommentTest extends HumHubDbTestCase
{
    /**
     * @var Post
     */
    public $post;

    /**
     * @var Comment
     */
    public $comment;

    /**
     * @var Comment
     */
    public $subComment;

    public function _before()
    {
        $this->becomeUser('User2');
        $this->post = Post::findOne(['id' => 11]);

        $this->comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::class,
            'object_id' => 11
        ]);

        $this->assertTrue($this->comment->save());

        $this->subComment = new Comment([
            'message' => 'Sub comment!',
            'object_model' => Comment::class,
            'object_id' => $this->comment->id
        ]);

        $this->assertTrue($this->subComment->save());
    }

    public function testSubCommentGetCommentedRecordReturnsRootContent()
    {
        $this->assertInstanceOf(Post::class, $this->subComment->getCommentedRecord());
        $this->assertEquals($this->post->id, $this->subComment->getCommentedRecord()->id);
    }

    public function testGetSubCommentsOfRootCommentReturnsSubComments()
    {
        $newSubComment = new Comment([
            'message' => 'New Sub comment!',
            'object_model' => Comment::class,
            'object_id' => $this->comment->id
         ]);

        $this->assertTrue($newSubComment->save());

        $subComments = Comment::findAll(['object_model' => Comment::class, 'object_id' => $this->comment->id]);
        $this->assertCount(2, $subComments);
        $this->assertEquals($this->subComment->id, $subComments[0]->id);
        $this->assertEquals($newSubComment->id, $subComments[1]->id);
    }

    /**
     * Rule not implemented
     * @skip
     */
    public function testSecondLevelSubCommentCannotBeSaved()
    {
        $newSubComment = new Comment([
            'message' => 'New Sub comment!',
            'object_model' => Comment::class,
            'object_id' => $this->subComment->id
        ]);

        $this->assertFalse($newSubComment->save());
    }

    /**
     * Rule not implemented
     * @skip
     */
    public function testSubCommentCannotBeSubCommentOfItself()
    {
        $this->subComment->object_id = $this->subComment->id;
        $this->assertFalse($this->subComment->save());
    }

   /* public function testDeleteUser()
    {
        $this->becomeUser('User2');

        $comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::class,
            'object_id' => 11
        ]);

        $comment->save();

        $user2 = User::findOne(['id' => 3]);
        $user2->delete();

        $this->assertNull(Comment::findOne(['id' => $comment->id]));
    }

    public function testDeleteCommentedContent()
    {
        $this->becomeUser('User2');

        $comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::class,
            'object_id' => 11
        ]);

        $comment->save();

        $post = Post::findOne(['id' => 11]);
        $post->delete();

        $this->assertNull(Comment::findOne(['id' => $comment->id]));
    }*/
}
