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
class CommentsWidget extends HWidget {

    /**
     * Model Name (e.g. Post) to identify which posts we shall show
     *
     * @var String
     */
    public $modelName = "";

    /**
     * The primary key of the Model
     *
     * @var Integer
     */
    public $modelId = "";

    /**
     * Executes the widget.
     */
    public function run() {

        // Indicates that the number of comments was limited
        $isLimited = false;

        // Count all Comments
        $commentCount = Comment::GetCommentCount($this->modelName, $this->modelId);
        $comments = Comment::GetCommentsLimited($this->modelName, $this->modelId, 2);

        if ($commentCount > 2)
            $isLimited = true;

        $this->render('comments', array(
            'comments' => $comments,
            'modelName' => $this->modelName,
            'modelId' => $this->modelId,
            'id' => $this->modelName . "_" . $this->modelId,
            'isLimited' => $isLimited,
            'total' => $commentCount
                )
        );
    }

}

?>