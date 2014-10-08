<?php

/**
 * This widget is used include the comments functionality to a wall entry.
 *
 * Normally it shows a excerpt of all comments, but provides the functionality
 * to show all comments.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class CommentsWidget extends HWidget
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
        
        // Indicates that the number of comments was limited
        $isLimited = false;

        // Count all Comments
        $commentCount = Comment::GetCommentCount($modelName, $modelId);
        $comments = Comment::GetCommentsLimited($modelName, $modelId, 2);

        if ($commentCount > 2)
            $isLimited = true;

        $this->render('comments', array(
            'object' => $this->object,
            
            'comments' => $comments,
            'modelName' => $modelName,
            'modelId' => $modelId,
            'id' => $modelName . "_" . $modelId,
            'isLimited' => $isLimited,
            'total' => $commentCount
                )
        );
    }

}

?>