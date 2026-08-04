<?php

namespace tests\codeception\unit\modules\content\notifications;

use humhub\modules\content\notifications\ContentDeleted;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class ContentDeletedTest extends HumHubDbTestCase
{
    /* @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore */
    public function _fixtures(): array
    {
        return [];
    }

    private function buildNotification(string $reason, string $contentTitle): ContentDeleted
    {
        $notification = new ContentDeleted();
        $notification->from(new User(['id' => 1, 'username' => 'admin']));
        $notification->payload = [
            'contentTitle' => $contentTitle,
            'reason' => $reason,
        ];

        return $notification;
    }

    /**
     * Regression test for the stored XSS via the admin deletion reason (issue #1311).
     * The reason is a raw form string and must be HTML encoded in the notification.
     */
    public function testDeletionReasonIsEncoded()
    {
        $html = $this->buildNotification('<script>alert(1)</script>', 'post "hello"')->html();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    /**
     * contentTitle is built by RichTextToPlainTextConverter which does NOT encode,
     * so html() must encode it to neutralize markup from the content body.
     */
    public function testContentTitleIsEncoded()
    {
        $html = $this->buildNotification('a reason', 'post "<img src=x onerror=alert(1)>"')->html();

        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
    }
}
