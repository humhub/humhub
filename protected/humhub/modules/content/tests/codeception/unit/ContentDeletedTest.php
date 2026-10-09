<?php

namespace tests\codeception\unit\modules\content\notifications;

use humhub\modules\content\notifications\ContentDeletedNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use tests\codeception\_support\HumHubDbTestCase;

class ContentDeletedTest extends HumHubDbTestCase
{
    private function buildNotification(string $reason, string $contentTitle): ContentDeletedNotification
    {
        $record = new Notification([
            'class' => ContentDeletedNotification::class,
            'user_id' => 2,
            'originator_id' => 1,
            'payload' => [
                'contentTitle' => $contentTitle,
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
        $html = $this->buildNotification('<script>alert(1)</script>', 'post "hello"')->asWeb();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    /**
     * contentTitle is plain text (RichTextToPlainTextConverter does NOT encode),
     * so the web sentence must encode it to neutralize markup from the content body.
     */
    public function testContentTitleIsEncoded()
    {
        $notification = $this->buildNotification('a reason', 'post "<img src=x onerror=alert(1)>"');
        $html = $notification->asWeb();

        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        // plain text channels carry it unencoded
        $this->assertStringContainsString('post "<img src=x onerror=alert(1)>"', $notification->asMailSubject());
        $this->assertSame([], $notification->getActions());
    }
}
