<?php

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\notification\components\NotificationContext;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Mentioning;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\notifications\MentionedNotification;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Exception;

class MentionTest extends HumHubDbTestCase
{
    /**
     * @throws Exception
     */
    public function testPostMention()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 1]);

        $post = new Post(['message' => ' [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839c "url")']);
        $post->content->container = $space;
        $post->save();

        RichText::postProcess($post->message, $post);

        $this->assertHasNotification(MentionedNotification::class, $post);
        $this->assertMailSent(1);

        $notification = NotificationManager::load(Notification::findOne(['class' => MentionedNotification::class, 'user_id' => 1]));
        $this->assertStringContainsString('mentioned you in post', $notification->asWeb());
        $this->assertStringContainsString('just mentioned you in post "', $notification->asMailSubject());
        $this->assertSame($post->content->getUrl(), $notification->getUrl());
    }

    public function testMentionAuthor()
    {
        $this->becomeUser('User2');

        // Mention Admin in Space 1 (Admin is author of post)
        $comment = new Comment([
            'message' => 'Hi [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839c "url")',
            'content_id' => 7,
        ]);

        $comment->save();

        $this->assertHasNotification(MentionedNotification::class, $comment);

        // We expect only the Mentioned mail
        $this->assertMailSent(1);
    }

    public function testMentionNonAuthor()
    {
        $this->becomeUser('User2');

        // Mention User1 in post
        $comment = new Comment([
            'message' => 'Hi [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839d "url")',
            'content_id' => 7,
        ]);

        $comment->save();

        $this->assertHasNotification(MentionedNotification::class, $comment);

        // Commented mail for Admin and Mentioned mail for User1
        $this->assertMailSent(2);
    }

    public function testMentionInACommentIsAboutTheComment()
    {
        $this->becomeUser('User2');

        // Mention User1 in a comment
        $comment = new Comment([
            'message' => 'Hi [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839d "url")',
            'content_id' => 7,
        ]);
        $this->assertTrue($comment->save());

        $this->assertHasNotification(MentionedNotification::class, $comment, 3, 2);

        $record = Notification::findOne(['class' => MentionedNotification::class, 'user_id' => 2]);
        $this->assertSame(7, (int)$record->content_id);
        $this->assertNull($record->seen_at);
        $this->assertSame(1, (int)$record->listed);

        $notification = NotificationManager::load($record);
        $blocks = $notification->getBlocks(new NotificationContext(MailTarget::ID));
        $this->assertCount(2, $blocks);
        $this->assertSame(NotificationBlock::TYPE_CONTENT_PREVIEW, $blocks[0]->getType());
        $this->assertEquals(NotificationBlock::button('View online', $notification->getEntryUrl()), $blocks[1]);
        $mailRecord = $blocks[0]->getRecord();
        $this->assertInstanceOf(Comment::class, $mailRecord);
        $this->assertSame((int)$comment->id, (int)$mailRecord->id);
        $this->assertSame($comment->getUrl(), $notification->getUrl());
        $this->assertStringContainsString('mentioned you in comment', $notification->asWeb());
        $this->assertStringContainsString('just mentioned you in comment "Hi', $notification->asMailSubject());
    }
}
