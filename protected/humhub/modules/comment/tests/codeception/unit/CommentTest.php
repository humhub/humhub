<?php

namespace tests\codeception\unit\modules\comment\components;

use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\post\models\Post;
use humhub\modules\comment\models\Comment;

class CommentTest extends HumHubDbTestCase
{

    use Specify;

    public function testCreateComment()
    {
        $this->becomeUser('User2');
        
        $comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::class,
            'object_id' => 11
        ]);

        $comment->save();

        $this->assertMailSent(1, 'Comment Notification Mail sent');
        $this->assertEqualsLastEmailSubject('Sara Tester commented post "User 2 Space 2 Post Private" in space Space 2');
        $this->assertNotEmpty($comment->id);
        $this->assertNotEmpty($comment->content->getPolymorphicRelation()->getFollowersWithNotificationQuery());
        
        $this->assertNotNull(\humhub\modules\activity\models\Activity::findOne(['object_model' => Comment::class, 'object_id' => $comment->id]));
        $this->assertNotNull(\humhub\modules\notification\models\Notification::findOne(['source_class' => Comment::class, 'source_pk' => $comment->id]));
    }

    public function testDeleteUser()
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
    }

    public function testGetCommentLimited()
    {
        $this->becomeUser('User2');

        (new Comment([
            'message' => 'Test comment1',
            'object_model' => Post::class,
            'object_id' => 11
        ]))->save();

        (new Comment([
            'message' => 'Test comment2',
            'object_model' => Post::class,
            'object_id' => 11
        ]))->save();

        (new Comment([
            'message' => 'Test comment3',
            'object_model' => Post::class,
            'object_id' => 11
        ]))->save();

        $comments = Comment::GetCommentsLimited(Post::class, 11, 2);
        $this->assertCount(2, $comments);
        $this->assertEquals('Test comment2', $comments[0]->message);
        $this->assertEquals('Test comment3', $comments[1]->message);

    }

    public function testGetCommentCount()
    {
        $this->becomeUser('User2');

        $count = Comment::GetCommentCount(Post::class, 11);
        $this->assertEquals(0, $count);

        Comment::flushCommentCache(Post::class, 11);

        (new Comment([
            'message' => 'Test comment1',
            'object_model' => Post::class,
            'object_id' => 11
        ]))->save();

        (new Comment([
            'message' => 'Test comment2',
            'object_model' => Post::class,
            'object_id' => 11
        ]))->save();

        (new Comment([
            'message' => 'Test comment3',
            'object_model' => Post::class,
            'object_id' => 11
        ]))->save();

        $count = Comment::GetCommentCount(Post::class, 11);
        $this->assertEquals(3, $count);
    }

}
