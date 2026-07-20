<?php

namespace tests\codeception\unit\modules\comment\components;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\widgets\Comments;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class CommentsWidgetTest extends HumHubDbTestCase
{
    public function testShowNextPaginationOnSubCommentPermalink()
    {
        $this->becomeUser('User2');

        $roots = [];
        for ($i = 1; $i <= 5; $i++) {
            ($roots[$i] = new Comment([
                'message' => 'Root comment ' . $i,
                'content_id' => 11,
            ]))->save();
        }

        ($sub = new Comment([
            'message' => 'Sub comment',
            'content_id' => 11,
            'parent_comment_id' => $roots[2]->id,
        ]))->save();

        // Simulate a permalink to the sub comment
        Yii::$app->request->setQueryParams(['StreamQuery' => ['commentId' => (string)$sub->id]]);

        $html = Comments::widget(['content' => Post::findOne(['id' => 11])->content]);

        // Root list is anchored around the sub comment's parent (root 2), so root 4
        // is beyond the loaded range and must be reachable via the "Show next" link
        $this->assertStringContainsString('Root comment 2', $html);
        $this->assertStringContainsString('Show next', $html);
    }

    public function testAnchoredListIsFocusedAroundPermalinkedComment()
    {
        $this->becomeUser('User2');

        $roots = [];
        for ($i = 1; $i <= 8; $i++) {
            ($roots[$i] = new Comment([
                'message' => 'Root comment ' . $i,
                'content_id' => 11,
            ]))->save();
        }

        ($sub = new Comment([
            'message' => 'Sub comment',
            'content_id' => 11,
            'parent_comment_id' => $roots[5]->id,
        ]))->save();

        // Simulate a permalink to the sub comment
        Yii::$app->request->setQueryParams(['StreamQuery' => ['commentId' => (string)$sub->id]]);

        // Even in full view mode the anchored list must stay focused around the
        // anchor (commentsPreviewMax previous comments) instead of loading all
        // previous comments up to the view mode limit without any pagination
        $html = Comments::widget([
            'content' => Post::findOne(['id' => 11])->content,
            'viewMode' => Comments::VIEW_MODE_FULL,
        ]);

        $this->assertStringContainsString('Root comment 3', $html);
        $this->assertStringContainsString('Root comment 5', $html);
        $this->assertStringContainsString('Root comment 6', $html);
        $this->assertStringNotContainsString('Root comment 2', $html);
        $this->assertStringNotContainsString('Root comment 7', $html);
        $this->assertStringContainsString('Show previous', $html);
        $this->assertStringContainsString('Show next', $html);
    }

    public function testShowMoreCountsAllRemainingComments()
    {
        $this->becomeUser('User2');

        for ($i = 1; $i <= 9; $i++) {
            (new Comment([
                'message' => 'Root comment ' . $i,
                'content_id' => 11,
            ]))->save();
        }

        // Compact list shows the last 2 comments; the "Show previous" link must
        // count all 7 remaining comments, not just the next loadable page
        $html = Comments::widget(['content' => Post::findOne(['id' => 11])->content]);

        $this->assertStringContainsString('Root comment 8', $html);
        $this->assertStringContainsString('Show previous 7 comments', $html);
    }
}
