<?php

namespace tests\codeception\unit\modules\comment;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\widgets\Comment as CommentWidget;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class CommentWidgetTest extends HumHubDbTestCase
{
    public function testRenderCommentOfBlockedAuthor()
    {
        $this->becomeUser('User1');
        ($comment = new Comment(['message' => 'Message of blocked author', 'content_id' => 11]))->save();

        $this->becomeUser('User2');
        $comment->createdBy->blockForUser(Yii::$app->user->getIdentity());

        $html = CommentWidget::widget(['comment' => $comment]);

        $this->assertStringContainsString('comment-blocked-user', $html);
        $this->assertStringNotContainsString('Message of blocked author', $html);
    }
}
