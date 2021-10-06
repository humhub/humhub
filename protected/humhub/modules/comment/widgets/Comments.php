<?php

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\components\Widget;
use Yii;

/**
 * This widget is used include the comments functionality to a wall entry.
 *
 * Normally it shows a excerpt of all comments, but provides the functionality
 * to show all comments.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class Comments extends Widget
{

    /**
     * @var Comment|ContentActiveRecord
     */
    public $object;

    /**
     * Executes the widget.
     */
    public function run()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        $objectModel = get_class($this->object);
        $objectId = $this->object->getPrimaryKey();

        $streamQuery = Yii::$app->request->getQueryParam('StreamQuery');
        $currentCommentId = empty($streamQuery['commentId']) ? null : $streamQuery['commentId'];

        // Count all Comments
        $commentCount = CommentModel::GetCommentCount($objectModel, $objectId);
        $comments = [];
        if ($commentCount !== 0) {
            $comments = CommentModel::GetCommentsLimited($objectModel, $objectId, $module->commentsPreviewMax, $currentCommentId);
        }

        $isLimited = ($commentCount > $module->commentsPreviewMax);

        return $this->render('comments', [
            'object' => $this->object,
            'comments' => $comments,
            'currentCommentId' => $currentCommentId,
            'objectModel' => $objectModel,
            'objectId' => $objectId,
            'id' => $this->object->getUniqueId(),
            'isLimited' => $isLimited,
            'total' => $commentCount
        ]);
    }
}
