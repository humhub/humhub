<?php

namespace tests\codeception\unit\modules\comment\components;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\notifications\CommentDeleted;
use humhub\modules\comment\services\CommentDeleteService;
use humhub\modules\notification\models\Notification;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * @see CommentDeleteService
 */
class CommentDeleteServiceTest extends HumHubDbTestCase
{
    public function testPlainDeleteRemovesCommentAndReplies()
    {
        $this->becomeUser('User2');

        ($root = new Comment(['message' => 'Root', 'content_id' => 11]))->save();
        ($reply = new Comment(['message' => 'Reply', 'content_id' => 11, 'parent_comment_id' => $root->id]))->save();

        $this->assertTrue((new CommentDeleteService($root))->delete());

        $this->assertNull(Comment::findOne(['id' => $root->id]));
        $this->assertNull(Comment::findOne(['id' => $reply->id]));
        $this->assertNull(Notification::findOne(['class' => CommentDeleted::class]));
    }

    public function testDeleteWithNotificationInformsTheAuthor()
    {
        $this->becomeUser('User2');
        ($comment = new Comment(['message' => 'To be moderated', 'content_id' => 11]))->save();
        $authorId = $comment->created_by;

        // The moderator deletes with a notification to the author
        $this->becomeUser('Admin');
        $this->assertTrue((new CommentDeleteService($comment))->delete(true, 'Against the rules'));

        $this->assertNull(Comment::findOne(['id' => $comment->id]));

        $notification = Notification::findOne(['class' => CommentDeleted::class, 'user_id' => $authorId]);
        $this->assertNotNull($notification);
        $this->assertEquals(1, $notification->send_web_notifications);
        $this->assertStringContainsString('Against the rules', (string)$notification->payload);
    }
}
