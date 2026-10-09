<?php

namespace tests\codeception\unit\modules\comment\notifications;

use humhub\modules\comment\notifications\CommentDeletedNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use tests\codeception\_support\HumHubDbTestCase;

class CommentDeletedTest extends HumHubDbTestCase
{
    private function buildNotification(string $reason, string $commentText): CommentDeletedNotification
    {
        $record = new Notification([
            'class' => CommentDeletedNotification::class,
            'user_id' => 2,
            'originator_id' => 1,
            'payload' => [
                'commentText' => $commentText,
                'reason' => $reason,
            ],
        ]);

        return NotificationManager::fromRecord($record);
    }

    /**
     * Regression test for the stored XSS via the admin deletion reason (issue #1311).
     * The reason is a raw form string and must be HTML encoded in the notification.
     */
    public function testDeletionReasonIsEncoded()
    {
        $html = $this->buildNotification('<script>alert(1)</script>', 'a comment')->asWeb();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    /**
     * The comment preview is stored as plain text: the web sentence encodes it once, the
     * plain text channels carry it as is.
     */
    public function testCommentTextIsEncodedOnce()
    {
        $notification = $this->buildNotification('a reason', '<b>hi</b>');
        $html = $notification->asWeb();

        $this->assertStringContainsString('&lt;b&gt;hi&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('&amp;lt;b&amp;gt;', $html);
        $this->assertStringContainsString('<b>hi</b>', $notification->asMailSubject());
        $this->assertSame([], $notification->getActions());
    }
}
