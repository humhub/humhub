<?php

namespace tests\codeception\unit\modules\comment\notifications;

use humhub\modules\comment\notifications\CommentDeleted;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class CommentDeletedTest extends HumHubDbTestCase
{
    /* @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore */
    public function _fixtures(): array
    {
        return [];
    }

    private function buildNotification(string $reason, string $commentText): CommentDeleted
    {
        $notification = new CommentDeleted();
        $notification->from(new User(['id' => 1, 'username' => 'admin']));
        $notification->payload = [
            'commentText' => $commentText,
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
        $html = $this->buildNotification('<script>alert(1)</script>', 'a comment')->html();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    /**
     * The comment preview is already HTML encoded by RichTextToShortTextConverter,
     * so html() must not encode it a second time (no double encoding).
     */
    public function testCommentTextIsNotDoubleEncoded()
    {
        $html = $this->buildNotification('a reason', '&lt;b&gt;hi&lt;/b&gt;')->html();

        $this->assertStringContainsString('&lt;b&gt;hi&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('&amp;lt;b&amp;gt;', $html);
    }
}
