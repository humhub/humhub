<?php

namespace humhub\modules\comment\widgets;

use humhub\components\Widget;
use humhub\modules\comment\helpers\IdHelper;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use Yii;

/**
 * This widget is used include the comments functionality to a wall entry.
 *
 * Normally it shows a excerpt of all comments, but provides the functionality
 * to show all comments.
 *
 * @property-read int $limit
 * @property-read int $pageSize
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class Comments extends Widget
{
    public const VIEW_MODE_COMPACT = 'compact';
    public const VIEW_MODE_FULL = 'full';

    public ?Content $content = null;

    public ?CommentModel $parentComment = null;

    /**
     * @var StreamEntryOptions|null
     */
    public $renderOptions;

    /**
     * @var Module
     */
    public $module;

    /**
     * @var string
     */
    public $viewMode = self::VIEW_MODE_COMPACT;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->parentComment !== null) {
            $this->content = $this->parentComment->content;
        }

        $this->module = Yii::$app->getModule('comment');
    }

    /**
     * Executes the widget.
     */
    public function run()
    {

        $commentListService = new CommentListService($this->content, $this->parentComment);
        $highlightCommentId = $this->getHighlightCommentId();

        $comments = [];
        $commentCount = $commentListService->getCount();
        if ($commentCount !== 0) {
            $comments = $commentListService->getLimited($this->limit, $highlightCommentId);
        }

        $this->view->registerJsVar('comments_collapsed', $this->limit == 0);

        return $this->render('comments', [
            'content' => $this->content,
            'parentComment' => $this->parentComment,
            'comments' => $comments,
            'highlightCommentId' => $highlightCommentId,
            'id' => IdHelper::getId($this->content, $this->parentComment),
        ]);
    }

    private function isFullViewMode(): bool
    {
        return $this->viewMode === self::VIEW_MODE_FULL
            || (($this->renderOptions instanceof StreamEntryOptions) && $this->renderOptions->isViewContext(
                    WallStreamEntryOptions::VIEW_CONTEXT_DETAIL
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

    protected function getHighlightCommentId(): ?int
    {
        $streamQuery = Yii::$app->request->getQueryParam('StreamQuery');
        if (empty($streamQuery['commentId'])) {
            return null;
        }

        $currentCommentId = (int)$streamQuery['commentId'];

        $currentComment = Yii::$app->runtimeCache->getOrSet(
            'getCurrentComment' . $currentCommentId,
            fn() => CommentModel::findOne(['id' => $currentCommentId])
        );

        if (!$currentComment || $currentComment->parent_comment_id !== $this->parentComment?->id) {
            // The current comment is from another parent object
            return null;
        }

        return $currentCommentId;
    }
}
