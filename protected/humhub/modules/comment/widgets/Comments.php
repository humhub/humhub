<?php

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\assets\CommentVueAsset;
use humhub\modules\comment\helpers\IdHelper;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use humhub\modules\comment\serializers\CommentSerializer;
use humhub\modules\comment\services\CommentPayloadCache;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\widgets\FileHandlerButtonDropdown;
use humhub\modules\like\serializers\LikeSerializer;
use humhub\widgets\Icon;
use humhub\widgets\VueWidget;
use Yii;

/**
 * Renders the `<comment-section>` Vue island (see `comment/vue/CommentSection.vue`) for a
 * content's comment thread - comments are no longer rendered as server HTML, only the
 * initial data window ({@see CommentPayloadCache::window()}, exactly what the island's own
 * API fetches return) and the reusable form shell (see {@see CommentFormShell}) travel
 * with the page.
 *
 * @property-read int $limit
 * @property-read int $pageSize
 */
class Comments extends VueWidget
{
    public const VIEW_MODE_COMPACT = 'compact';
    public const VIEW_MODE_FULL = 'full';

    public ?Content $content = null;

    /**
     * @deprecated since 1.20, set {@see self::$content} instead. Kept for backward
     * compatibility - some modules still call `Comments::widget(['object' => $x])` (the API
     * before #7917 replaced polymorphic `object` relations with `content_id`). A comment
     * given here stands for its content: a section always shows a content's whole thread,
     * the island renders the replies itself.
     * @var ContentActiveRecord|CommentModel|null
     */
    public $object = null;

    public ?StreamEntryOptions $renderOptions = null;

    public Module $module;

    public string $viewMode = self::VIEW_MODE_COMPACT;

    protected string $component = 'CommentSection';

    protected ?string $assetBundle = CommentVueAsset::class;

    public function init()
    {
        parent::init();

        if ($this->object !== null && $this->content === null) {
            $this->content = $this->object->content;
        }

        $this->module = Yii::$app->getModule('comment');
    }

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (Yii::$app->user->isGuest && $this->module->guestHideComments) {
            return false;
        }

        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     */
    protected function getOptions(): array
    {
        return ['id' => 'comment_' . IdHelper::getId($this->content, null)];
    }

    /**
     * @inheritdoc
     */
    protected function getProps(): array
    {
        $anchorCommentId = $this->getHighlightCommentId(true);

        // Anchored windows (permalinks) stay focused around the anchor with a small
        // window of previous comments; otherwise the view-mode-aware preview size
        // (see getLimit()) is used - exactly what the legacy HTML rendering did.
        //
        // Serialized by the same code the island's own API calls go through
        // (`CommentSerializer`), so embedding the first window here purely saves the
        // island its initial request - shape and semantics are identical.
        $initial = CommentPayloadCache::window(
            $this->content,
            null,
            commentId: $anchorCommentId,
            limit: $anchorCommentId ? $this->module->commentsPreviewMax : $this->getLimit(),
        );

        return [
            'contentId' => $this->content->id,
            'initial' => $initial,
            // The like states of the embedded window, inlined rather than fetched: the
            // window payload itself is caller-neutral (and therefore cacheable, see
            // `docs/develop/concept-api.md`), but THIS page render is per user anyway, so
            // handing them over here saves the island its first `like/states` request and
            // renders the like links complete on first paint.
            'initialLikeStates' => LikeSerializer::statesByRecordId(CommentSerializer::recordIds($initial)),
            ...self::formProps($this->content),
            'pageSize' => $this->getPageSize(),
            'anchorCommentId' => $this->getHighlightCommentId(false),
            // Mirrors comments.php's `d-none` class, only lifted (inline `.show()`)
            // when at least one comment is preloaded into the initial window.
            'collapsed' => empty($initial['results']),
            // Same call `file\widgets\ShowFiles` makes for a wall entry: an attachment
            // the preview grid already shows is left out of the list below it. The
            // setting is the file module's, the shape rendering it is the shared
            // `<AttachedFiles>` component.
            'excludeMediaFiles' => (bool)Yii::$app->getModule('file')->settings->get('excludeMediaFilesPreview'),
        ];
    }

    /**
     * What the section's comment form needs, for a content: whether the current user may
     * comment and, if so, the form's shell, the settings of its upload field and its submit
     * icon. The same props whether the widget embeds them or the island fetches them for a
     * section it was mounted for by `contentId` alone (`comment/comment/form`, see
     * {@see \humhub\modules\comment\controllers\CommentController::actionForm()}).
     *
     * @since 1.20
     */
    public static function formProps(Content $content): array
    {
        $canComment = (bool)Yii::$app->getModule('comment')->canComment($content);

        return [
            'canComment' => $canComment,
            'formShellHtml' => $canComment ? CommentFormShell::widget(['content' => $content]) : null,
            // Settings of the form's Vue-native upload field (`UploadField`), which
            // replaced the shell's former server-rendered upload composition. The handler
            // entries stay server-rendered: they are menu entries a module contributed,
            // carrying legacy `data-action-click` attributes - see that component's
            // docblock, "Legacy file handlers".
            'uploadOptions' => $canComment ? [
                'max' => (int)Yii::$app->getModule('content')->maxAttachedFiles,
                'handlersHtml' => FileHandlerButtonDropdown::widget([
                    'handlers' => FileHandlerCollection::getByType(
                        [FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_CREATE],
                    ),
                    'itemsOnly' => true,
                ]),
            ] : null,
            // Server-rendered icon HTML for CommentForm.vue's submit button, reproducing
            // the legacy `Button::accent()->icon('send')` markup (see that component's own
            // docblock) - rendered here rather than hardcoded client-side since the icon
            // provider (FontAwesome by default) is pluggable/configurable.
            'submitIconHtml' => $canComment ? Icon::get('send')->asString() : null,
        ];
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
        } elseif ($returnParentId && !empty($highlightedComment->parent_comment_id)) {
            // Highlighted comment has parent, but we're in root context. So return 'parentId' as highlighted instead.
            return $highlightedComment->parent_comment_id;
        } elseif ($highlightedComment->parent_comment_id !== null) {
            // Skip highlight, highlighted comment doesn't belong to this level.
            return null;
        }

        return $currentCommentId;
    }
}
