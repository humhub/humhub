<?php

namespace humhub\modules\comment\widgets;

use humhub\components\Widget;
use humhub\modules\comment\assets\CommentVueAsset;
use humhub\modules\comment\helpers\IdHelper;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use humhub\modules\comment\services\CommentJsonService;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\widgets\VueComponent;
use Yii;

/**
 * Renders the `<comment-section>` Vue island (see `comment/vue/CommentSection.vue`) for a
 * content's comment thread - comments are no longer rendered as server HTML, only the
 * initial data window (see {@see CommentJsonService::serializeWindow()}) and the reusable
 * form shell (see {@see CommentFormShell}) travel with the page.
 *
 * @property-read int $limit
 * @property-read int $pageSize
 */
class Comments extends Widget
{
    public const VIEW_MODE_COMPACT = 'compact';
    public const VIEW_MODE_FULL = 'full';

    public ?Content $content = null;

    public ?CommentModel $parentComment = null;

    /**
     * @deprecated since 1.19, set {@see self::$content} (and {@see self::$parentComment} for
     * nested rendering) instead. Kept for backward compatibility - some modules still call
     * `Comments::widget(['object' => $x])` (the API before #7917 replaced polymorphic
     * `object` relations with `content_id`/`parent_comment_id`).
     * @var ContentActiveRecord|CommentModel|null
     */
    public $object = null;

    public ?StreamEntryOptions $renderOptions = null;

    public Module $module;

    public string $viewMode = self::VIEW_MODE_COMPACT;

    public function init()
    {
        parent::init();

        if ($this->object !== null && $this->content === null && $this->parentComment === null) {
            if ($this->object instanceof CommentModel) {
                $this->parentComment = $this->object;
            } else {
                $this->content = $this->object->content;
            }
        }

        if ($this->parentComment !== null) {
            $this->content = $this->parentComment->content;
        }

        $this->module = Yii::$app->getModule('comment');
    }

    public function run()
    {
        if (Yii::$app->user->isGuest && $this->module->guestHideComments) {
            return '';
        }

        $canComment = $this->module->canComment($this->content);
        $anchorCommentId = $this->getHighlightCommentId(true);

        // Anchored windows (permalinks) stay focused around the anchor with a small
        // window of previous comments; otherwise the view-mode-aware preview size
        // (see getLimit()) is used - exactly what the legacy HTML rendering did.
        $initial = CommentJsonService::create($this->parentComment ?? $this->content)->serializeWindow(
            commentId: $anchorCommentId,
            limit: $anchorCommentId ? $this->module->commentsPreviewMax : $this->getLimit(),
        );

        return VueComponent::widget([
            'name' => 'CommentSection',
            'assetBundle' => CommentVueAsset::class,
            'options' => [
                'id' => 'comment_' . IdHelper::getId($this->content, $this->parentComment),
            ],
            'props' => [
                'contentId' => $this->content->id,
                'initial' => $initial,
                'canComment' => $canComment,
                'formShellHtml' => $canComment ? CommentFormShell::widget(['content' => $this->content]) : null,
                'pageSize' => $this->getPageSize(),
                'anchorCommentId' => $this->getHighlightCommentId(false),
                // Mirrors comments.php's `d-none` class, only lifted (inline `.show()`)
                // when at least one comment is preloaded into the initial window.
                'collapsed' => empty($initial['comments']),
            ],
        ]);
    }

    private function isFullViewMode(): bool
    {
        return $this->viewMode === self::VIEW_MODE_FULL
            || (($this->renderOptions instanceof StreamEntryOptions) && $this->renderOptions->isViewContext(
                WallStreamEntryOptions::VIEW_CONTEXT_DETAIL,
            ));
    }

    public function getLimit(): int
    {
        return $this->isFullViewMode() ? $this->module->commentsPreviewMaxViewMode : $this->module->commentsPreviewMax;
    }

    public function getPageSize(): int
    {
        return $this->isFullViewMode(
        ) ? $this->module->commentsBlockLoadSizeViewMode : $this->module->commentsBlockLoadSize;
    }

    protected function getHighlightCommentId($returnParentId = false): ?int
    {
        $streamQuery = Yii::$app->request->getQueryParam('StreamQuery');
        if (empty($streamQuery['commentId'])) {
            return null;
        }

        $currentCommentId = (int)$streamQuery['commentId'];

        $highlightedComment = Yii::$app->runtimeCache->getOrSet(
            'getCurrentComment' . $currentCommentId,
            fn() => CommentModel::findOne(['id' => $currentCommentId, 'content_id' => $this->content->id]),
        );

        if (!$highlightedComment) {
            Yii::warning('Could not load highlight comment id: ' . $currentCommentId, 'comment');
            return null;
        } elseif ($returnParentId && !empty($highlightedComment->parent_comment_id) && empty($this->parentComment)) {
            // Highlighted comment has parent, but we're in root context. So return 'parentId' as highlighted instead.
            return $highlightedComment->parent_comment_id;
        } elseif ($highlightedComment->parent_comment_id !== $this->parentComment?->id) {
            // Skip highlight, highlighted comment doesn't belong to this level.
            return null;
        }

        return $currentCommentId;
    }
}
