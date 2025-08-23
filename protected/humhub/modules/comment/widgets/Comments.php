<?php

namespace humhub\modules\comment\widgets;

use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\Widget;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use humhub\modules\content\components\ContentActiveRecord;
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

    /**
     * @var Comment|ContentActiveRecord
     */
    public $object;

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

        $this->module = Yii::$app->getModule('comment');
    }

    /**
     * Executes the widget.
     */
    public function run()
    {
        $objectModel = PolymorphicRelation::getObjectModel($this->object);
        $objectId = $this->object->getPrimaryKey();
        $currentCommentId = $this->getCurrentCommentId();

        // Count all Comments
        $commentCount = CommentModel::GetCommentCount($objectModel, $objectId);
        $comments = [];
        if ($commentCount !== 0) {
            $comments = CommentModel::GetCommentsLimited($objectModel, $objectId, $this->limit, $currentCommentId);
        }

        $this->view->registerJsVar('comments_collapsed', $this->limit == 0);

        return $this->render('comments', [
            'object' => $this->object,
            'comments' => $comments,
            'currentCommentId' => $currentCommentId,
            'id' => $this->object->getUniqueId(),
        ]);
    }

    private function isFullViewMode(): bool
    {
        return $this->viewMode === self::VIEW_MODE_FULL
            || (($this->renderOptions instanceof StreamEntryOptions) && $this->renderOptions->isViewContext(WallStreamEntryOptions::VIEW_CONTEXT_DETAIL));
    }

    public function getLimit(): int
    {
        return $this->isFullViewMode() ? $this->module->commentsPreviewMaxViewMode : $this->module->commentsPreviewMax;
    }

    public function getPageSize(): int
    {
        return $this->isFullViewMode() ? $this->module->commentsBlockLoadSizeViewMode : $this->module->commentsBlockLoadSize;
    }

    protected function getCurrentCommentId(): ?int
    {
        $streamQuery = Yii::$app->request->getQueryParam('StreamQuery');
        if (empty($streamQuery['commentId'])) {
            return null;
        }

        $currentCommentId = (int) $streamQuery['commentId'];

        $currentComment = Yii::$app->runtimeCache->getOrSet('getCurrentComment' . $currentCommentId, function () use ($currentCommentId) {
            return CommentModel::findOne(['id' => $currentCommentId]);
        });

        if (!$currentComment
            || $currentComment->object_id !== $this->object?->id
            || $currentComment->object_model !== get_class($this->object)) {
            // The current comment is from another parent object
            return null;
        }

        return $currentCommentId;
    }
}
