<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\comment\components;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\widgets\CommentLink;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * The popup mode's link opens the comments client-side (`comment.showComments`), by the
 * content's id alone - there is no server route rendering the popup.
 */
class CommentLinkTest extends HumHubDbTestCase
{
    public function testPopupLinkOpensTheSectionByContentId()
    {
        $this->becomeUser('User2');
        $content = Post::findOne(['id' => 11])->content;

        $html = CommentLink::widget(['content' => $content, 'mode' => CommentLink::MODE_POPUP]);

        $this->assertStringContainsString('data-action-click="comment.showComments"', $html);
        $this->assertStringContainsString('data-content-id="' . $content->id . '"', $html);
        $this->assertStringContainsString('data-modal-title="Comments"', $html);
        $this->assertStringNotContainsString('data-action-url', $html);
    }

    public function testCommentGivenAsObjectStandsForItsContent()
    {
        $this->becomeUser('User2');
        $content = Post::findOne(['id' => 11])->content;
        $comment = new Comment(['message' => 'Root', 'content_id' => $content->id]);
        $this->assertTrue($comment->save());

        $html = CommentLink::widget(['object' => $comment]);

        $this->assertStringContainsString('#comment_C' . $content->id . 'P', $html);
        $this->assertStringContainsString('Comment', $html);
        $this->assertStringNotContainsString('Reply', $html);
    }
}
