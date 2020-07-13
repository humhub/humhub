<?php

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\components\Widget;

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
        $objectModel = get_class($this->object);
        $objectId = $this->object->getPrimaryKey();

        // Count all Comments
        $commentCount = CommentModel::GetCommentCount($objectModel, $objectId);
        $comments = CommentModel::GetCommentsLimited($objectModel, $objectId, 2);

        $isLimited = ($commentCount > 2);

        return $this->render('comments', [
            'object' => $this->object,
            'comments' => $comments,
            'objectModel' => $objectModel,
            'objectId' => $objectId,
            'id' => $this->object->getUniqueId(),
            'isLimited' => $isLimited,
            'total' => $commentCount
        ]);
    }
}
