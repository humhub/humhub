<?php

namespace humhub\modules\comment\widgets;

/**
 * This widget is used to show a single comment.
 *
 * It will used by the CommentsWidget and the CommentController to show comments.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class Comment extends \yii\base\Widget
{

    /**
     * @var Comment object to display
     */
    public $comment = null;

    /**
     * Indicates the comment was just edited
     *
     * @var boolean
     */
    public $justEdited = false;

    /**
     * Executes the widget.
     */
    public function run()
    {

        $user = $this->comment->user;

        return $this->render('showComment', array(
                    'comment' => $this->comment,
                    'user' => $user,
                    'justEdited' => $this->justEdited,
                        )
        );
    }

}

?>