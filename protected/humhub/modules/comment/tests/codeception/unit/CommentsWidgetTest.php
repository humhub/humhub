<?php

namespace tests\codeception\unit\modules\comment\components;

use DOMDocument;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\widgets\Comments;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * `Comments` renders a `<comment-section>` Vue island (see
 * `humhub\modules\comment\services\CommentJsonService`) instead of server-rendered comment
 * HTML - these tests assert on the mount element and its decoded props instead of on comment
 * text/"Show previous/next" link markup. The underlying window/pagination SEMANTICS these
 * cases pin (anchored windows, "keep one leftover" cut-off) are exercised in more detail at
 * the service level by `CommentJsonServiceTest`.
 */
class CommentsWidgetTest extends HumHubDbTestCase
{
    public function testIslandMountPointHasLegacyIdFormat()
    {
        $this->becomeUser('User2');

        $props = $this->islandProps(Comments::widget(['content' => Post::findOne(['id' => 11])->content]));

        $this->assertSame('comment_C11P', $props['id']);
        $this->assertSame('11', $props['content-id']);
    }

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

        $props = $this->islandProps(Comments::widget(['content' => Post::findOne(['id' => 11])->content]));

        // Root list is anchored around the sub comment's parent (root 2), so root 4
        // is beyond the loaded range and must be reachable via the "Show next" pagination.
        // (The highlighted sub comment itself is one level down, so the root-level island's
        // own `anchorCommentId` prop - used for the persistent CSS highlight - stays unset;
        // see Comments::getHighlightCommentId().)
        $messages = $this->plainMessages($props['initial']);
        $this->assertContains('Root comment 2', $messages);
        $this->assertNotContains('Root comment 4', $messages);
        $this->assertGreaterThan(0, $props['initial']['nextCount']);
        $this->assertArrayNotHasKey('anchor-comment-id', $props);
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
        $props = $this->islandProps(Comments::widget([
            'content' => Post::findOne(['id' => 11])->content,
            'viewMode' => Comments::VIEW_MODE_FULL,
        ]));

        $this->assertSame(
            ['Root comment 3', 'Root comment 4', 'Root comment 5', 'Root comment 6'],
            $this->plainMessages($props['initial']),
        );
        $this->assertGreaterThan(0, $props['initial']['prevCount']);
        $this->assertGreaterThan(0, $props['initial']['nextCount']);
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

        // Compact list shows the last 2 comments; the "Show previous" count must
        // reflect all 7 remaining comments, not just the next loadable page.
        $props = $this->islandProps(Comments::widget(['content' => Post::findOne(['id' => 11])->content]));

        $this->assertSame(['Root comment 8', 'Root comment 9'], $this->plainMessages($props['initial']));
        $this->assertSame(7, $props['initial']['prevCount']);
    }

    public function testCollapsedWhenNoCommentIsPreloaded()
    {
        $this->becomeUser('User2');

        $module = Yii::$app->getModule('comment');
        $originalMax = $module->commentsPreviewMax;
        $module->commentsPreviewMax = 0;

        try {
            (new Comment(['message' => 'Root comment 1', 'content_id' => 11]))->save();

            $props = $this->islandProps(Comments::widget(['content' => Post::findOne(['id' => 11])->content]));

            $this->assertSame([], $props['initial']['comments']);
            $this->assertSame('true', $props['collapsed']);
        } finally {
            $module->commentsPreviewMax = $originalMax;
        }
    }

    public function testNotCollapsedWhenACommentIsPreloaded()
    {
        $this->becomeUser('User2');
        (new Comment(['message' => 'Root comment 1', 'content_id' => 11]))->save();

        $props = $this->islandProps(Comments::widget(['content' => Post::findOne(['id' => 11])->content]));

        $this->assertNotEmpty($props['initial']['comments']);
        $this->assertSame('false', $props['collapsed']);
    }

    /**
     * @return string[] plain-text messages of the comments in a serialized window, in order
     */
    private function plainMessages(array $window): array
    {
        return array_map(
            fn(array $comment) => strip_tags((string)$comment['messageOutput']),
            $window['comments'],
        );
    }

    /**
     * Parses a `Comments::widget()` result into the mount element's own attributes plus the
     * JSON-encoded `props` attribute (see `humhub\widgets\VueComponent::run()`), merged into a
     * single associative array (`id`, `content-id`, `can-comment`, `page-size`,
     * `anchor-comment-id`, `collapsed` as rendered attribute strings; `initial`/`formShellHtml`
     * decoded from `props`).
     */
    private function islandProps(string $html): array
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><body>' . $html . '</body>');
        libxml_use_internal_errors(false);

        $tag = $dom->getElementsByTagName('comment-section')->item(0);
        $this->assertNotNull($tag, 'Expected a <comment-section> island, got: ' . $html);

        $props = [];
        foreach ($tag->attributes as $attribute) {
            $props[$attribute->name] = $attribute->value;
        }

        if (isset($props['props'])) {
            $props += json_decode($props['props'], true);
            unset($props['props']);
        }

        return $props;
    }
}
