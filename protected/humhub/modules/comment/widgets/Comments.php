<?php

namespace humhub\modules\comment\widgets;

/**
 * This widget is used include the comments functionality to a wall entry.
 *
 * Normally it shows a excerpt of all comments, but provides the functionality
 * to show all comments.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class Comments extends \yii\base\Widget
{

    /**
     * Content Object
     */
    public $object;

    /**
     * Executes the widget.
     */
    public function run()
    {
        $modelName = $this->object->content->object_model;
        $modelId = $this->object->content->object_id;

        // Count all Comments
        $commentCount = \humhub\modules\comment\models\Comment::GetCommentCount($modelName, $modelId);
        $comments = \humhub\modules\comment\models\Comment::GetCommentsLimited($modelName, $modelId, 2);

        $isLimited = ($commentCount > 2);

        return $this->render('comments', [
            'object' => $this->object,
            'comments' => $comments,
            'modelName' => $modelName,
            'modelId' => $modelId,
            'id' => $this->object->getUniqueId(),
            'isLimited' => $isLimited,
            'total' => $commentCount
        ]);
    }

}
