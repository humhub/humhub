<?php

namespace tests\codeception\unit\modules\comment\components;

use humhub\models\RecordMap;
use humhub\modules\activity\models\Activity;
use humhub\modules\comment\notifications\NewCommentNotification;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\MentionedNotification;
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\post\models\Post;
use humhub\modules\comment\models\Comment;
use humhub\modules\space\models\Space;

class CommentTest extends HumHubDbTestCase
{
    use Specify;

    public function testCreateComment()
    {
        // Post 11 is a private Post in Space 2 (id 2), followed by Admin (id 1).
        // Admin needs to be a member of Space 2 to actually be allowed to view the
        // private Post, otherwise the notification must not be sent to him.
        Space::findOne(['id' => 2])->addMember(1, 1, true);

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
        $this->assertHasNotification(NewCommentNotification::class, $comment, 3, 1);
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

        $html = $this->buildNotification($comment)->asWeb();

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

        $notification = $this->buildNotification($comment);

        // Simulate an orphaned content record by removing the underlying post without cleanup
        Post::deleteAll(['id' => 11]);
        $notification->record->refresh();
        $notification = NotificationManager::fromRecord($notification->record);

        $html = $notification->asWeb();

        $this->assertStringContainsString('[Deleted]', $html);
    }

    public function testCommentNotificationIsSkippedForAMentionedUser()
    {
        // post 2: public post of Admin, who follows it
        $this->becomeUser('User2');
        $comment = new Comment(['message' => 'User2 comment!', 'content_id' => Post::findOne(['id' => 2])->content->id]);
        $this->assertTrue($comment->save());
        $this->assertHasNotification(NewCommentNotification::class, $comment, 3, 1);

        // Admin is mentioned in the comment: the mention notification covers it
        $mention = new Comment([
            'message' => 'Hi [Admin](mention:01e50e0d-82cd-41fc-8b0c-552392f5839c "Admin")',
            'content_id' => Post::findOne(['id' => 2])->content->id,
        ]);
        $this->assertTrue($mention->save());

        $this->assertHasNotification(MentionedNotification::class, $mention, 3, 1);
        $this->assertHasNoNotification(NewCommentNotification::class, $mention, null, 1);
    }

    public function testCommentsOnOneContentGroup()
    {
        // post 2: public post of Admin, who follows it
        $contentId = Post::findOne(['id' => 2])->content->id;

        $this->becomeUser('User2');
        $first = new Comment(['message' => 'First', 'content_id' => $contentId]);
        $this->assertTrue($first->save());
        $second = new Comment(['message' => 'Second', 'content_id' => $contentId]);
        $this->assertTrue($second->save());

        $row = Notification::find()->forUser(1)->grouped()->andWhere(['notification.class' => NewCommentNotification::class])->one();
        $notification = NotificationManager::load($row);
        $this->assertSame(2, $notification->groupCount);
        // the mail previews the comment, the sentence names the commented post
        $this->assertSame($second->id, $notification->getPreviewRecord()->id);
        $this->assertStringContainsString(Post::findOne(['id' => 2])->message, $notification->asWeb());
        $this->assertStringContainsString('just commented your', $notification->asMailSubject());
        $this->assertSame($second->getUrl(), $notification->getUrl());
    }

    public function testSubjectForANonOwnerOutsideOfASpace()
    {
        // post 2: Admin's post on their profile; User1 is not its author
        $this->becomeUser('User2');
        $comment = new Comment(['message' => 'User2 comment!', 'content_id' => Post::findOne(['id' => 2])->content->id]);
        $this->assertTrue($comment->save());

        $record = new Notification([
            'class' => NewCommentNotification::class,
            'user_id' => 2,
            'originator_id' => 3,
            'content_id' => $comment->content->id,
            'contentcontainer_id' => $comment->content->contentcontainer_id,
            'source_record_id' => RecordMap::getId($comment),
        ]);
        $this->assertTrue($record->save());

        $this->assertSame(
            User::findOne(['id' => 3])->displayName . ' commented post "' . Post::findOne(['id' => 2])->message . '"',
            NotificationManager::fromRecord($record)->asMailSubject(),
        );
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

        // A single comment beyond the limit is included directly instead of
        // being hidden behind a "Show previous 1 comments" link
        $comments = CommentListService::create(Post::findOne(['id' => 11]))->getLimited(2);
        $this->assertCount(3, $comments);
        $this->assertEquals('Test comment1', $comments[0]->message);
        $this->assertEquals('Test comment3', $comments[2]->message);

        (new Comment([
            'message' => 'Test comment4',
            'content_id' => 11,
        ]))->save();

        // With two comments beyond the limit the list is cut to the limit
        $comments = CommentListService::create(Post::findOne(['id' => 11]))->getLimited(2);
        $this->assertCount(2, $comments);
        $this->assertEquals('Test comment3', $comments[0]->message);
        $this->assertEquals('Test comment4', $comments[1]->message);

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

    public function testParentCommentValidation()
    {
        $this->becomeUser('User2');

        ($root = new Comment([
            'message' => 'Root',
            'content_id' => 11,
        ]))->save();

        // Reply to a root comment is fine
        $reply = new Comment([
            'message' => 'Reply',
            'content_id' => 11,
            'parent_comment_id' => $root->id,
        ]);
        $this->assertTrue($reply->save());

        // One nesting level only: a reply cannot be the parent of another comment
        $nested = new Comment([
            'message' => 'Nested',
            'content_id' => 11,
            'parent_comment_id' => $reply->id,
        ]);
        $this->assertFalse($nested->save());
        $this->assertArrayHasKey('parent_comment_id', $nested->getErrors());

        // The parent must belong to the same content
        $foreignParent = new Comment([
            'message' => 'Foreign parent',
            'content_id' => 12,
            'parent_comment_id' => $root->id,
        ]);
        $this->assertFalse($foreignParent->save());
        $this->assertArrayHasKey('parent_comment_id', $foreignParent->getErrors());

        // A dangling parent id is rejected too
        $danglingParent = new Comment([
            'message' => 'Dangling parent',
            'content_id' => 11,
            'parent_comment_id' => 99999,
        ]);
        $this->assertFalse($danglingParent->save());
        $this->assertArrayHasKey('parent_comment_id', $danglingParent->getErrors());
    }

    /**
     * The new comment notification of Admin about the given comment of User2.
     */
    private function buildNotification(Comment $comment): NewCommentNotification
    {
        $record = new Notification([
            'class' => NewCommentNotification::class,
            'user_id' => 1,
            'originator_id' => 3,
            'content_id' => $comment->content->id,
            'contentcontainer_id' => $comment->content->contentcontainer_id,
            'source_record_id' => RecordMap::getId($comment),
        ]);
        $this->assertTrue($record->save());

        return NotificationManager::fromRecord($record);
    }
}
