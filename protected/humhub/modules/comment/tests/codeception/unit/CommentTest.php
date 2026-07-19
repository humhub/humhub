<?php

namespace tests\codeception\unit\modules\comment\components;

use humhub\models\RecordMap;
use humhub\modules\activity\models\Activity;
use humhub\modules\comment\notifications\NewComment;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\notification\models\Notification;
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
            'content_id' => 11,
        ]);

        $comment->save();

        $this->assertMailSent(1);
        $this->assertEqualsLastEmailSubject('Sara Tester commented post "User 2 Space 2 Post Private" in Space Space 2');
        $this->assertNotEmpty($comment->id);
        $this->assertNotEmpty($comment->content->getPolymorphicRelation()->getFollowersWithNotificationQuery());

        $this->assertNotNull(Activity::findOne(['content_addon_record_id' => RecordMap::getId($comment)]));
        $this->assertNotNull(Notification::findOne(['source_class' => Comment::class, 'source_pk' => $comment->id]));
    }

    public function testDeleteUser()
    {
        $this->becomeUser('User2');

        $comment = new Comment([
            'message' => 'User2 comment!',
            'content_id' => 11,
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
            'content_id' => 11,
        ]);

        $comment->save();

        $post = Post::findOne(['id' => 11]);
        $post->hardDelete();

        $this->assertNull(Comment::findOne(['id' => $comment->id]));
    }

    public function testNotificationHtmlForPostWithoutText()
    {
        $this->becomeUser('User2');

        $post = Post::findOne(['id' => 11]);
        $post->updateAttributes(['message' => '']);

        $comment = new Comment([
            'message' => 'User2 comment!',
            'content_id' => 11,
        ]);
        $comment->save();

        $html = NewComment::instance()->from(User::findOne(['id' => 3]))->about($comment)->html();

        $this->assertStringNotContainsString('[Deleted]', $html);
        $this->assertStringEndsWith('commented post.', $html);
    }

    public function testNotificationHtmlForDeletedRecord()
    {
        $this->becomeUser('User2');

        $comment = new Comment([
            'message' => 'User2 comment!',
            'content_id' => 11,
        ]);
        $comment->save();

        // Simulate an orphaned content record by removing the underlying post without cleanup
        Post::deleteAll(['id' => 11]);
        $comment = Comment::findOne(['id' => $comment->id]);

        $html = NewComment::instance()->from(User::findOne(['id' => 3]))->about($comment)->html();

        $this->assertStringContainsString('[Deleted]', $html);
    }

    public function testGetCommentLimited()
    {
        $this->becomeUser('User2');

        (new Comment([
            'message' => 'Test comment1',
            'content_id' => 11,
        ]))->save();

        (new Comment([
            'message' => 'Test comment2',
            'content_id' => 11,
        ]))->save();

        (new Comment([
            'message' => 'Test comment3',
            'content_id' => 11,
        ]))->save();

        $comments = CommentListService::create(Post::findOne(['id' => 11]))->getLimited(2);
        $this->assertCount(2, $comments);
        $this->assertEquals('Test comment2', $comments[0]->message);
        $this->assertEquals('Test comment3', $comments[1]->message);

    }

    public function testGetCommentLimitedWithHighlightedComment()
    {
        $this->becomeUser('User2');

        ($commentA = new Comment([
            'message' => 'Test comment A',
            'content_id' => 11,
        ]))->save();

        ($commentB = new Comment([
            'message' => 'Test comment B',
            'content_id' => 11,
        ]))->save();

        $post = Post::findOne(['id' => 11]);

        // Permalink of the older comment A must also include the newer comment B
        $comments = CommentListService::create($post)->getLimited(2, $commentA->id);
        $this->assertCount(2, $comments);
        $this->assertEquals('Test comment A', $comments[0]->message);
        $this->assertEquals('Test comment B', $comments[1]->message);

        // Permalink of the newest comment B must also include the older comment A
        $comments = CommentListService::create($post)->getLimited(2, $commentB->id);
        $this->assertCount(2, $comments);
        $this->assertEquals('Test comment A', $comments[0]->message);
        $this->assertEquals('Test comment B', $comments[1]->message);
    }

    public function testGetCommentCount()
    {
        $this->becomeUser('User2');

        $count = CommentListService::create(Post::findOne(['id' => 11]))->getCount();
        $this->assertEquals(0, $count);

        (new Comment([
            'message' => 'Test comment1',
            'content_id' => 11,
        ]))->save();

        ($comment2 = new Comment([
            'message' => 'Test comment2',
            'content_id' => 11,
        ]))->save();

        $count = CommentListService::create(Post::findOne(['id' => 11]))->getCount();
        $this->assertEquals(2, $count);

        (new Comment([
            'message' => 'Test comment2b',
            'content_id' => 11,
            'parent_comment_id' => $comment2->id,
        ]))->save();

        (new Comment([
            'message' => 'Test comment3',
            'content_id' => 11,
        ]))->save();

        // The content count includes the sub comment
        $count = CommentListService::create(Post::findOne(['id' => 11]))->getCount();
        $this->assertEquals(4, $count);

        $count = CommentListService::create($comment2)->getCount();
        $this->assertEquals(1, $count);

    }

}
