<?php

/**
 * This widget is used to show a comment link inside the wall entry controls.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class CommentLinkWidget extends HWidget
{

    const MODE_INLINE = 'inline';
    const MODE_POPUP = 'popup';

    /**
     * Content Object
     */
    public $object;

    /**
     * Mode 
     * 
     * inline: Show comments on the same page with CommentsWidget (default)
     * popup: Open comments popup, display only link
     * 
     * @var type 
     */
    public $mode;

    /**
     * Executes the widget.
     */
    public function run()
    {

        if ($this->mode == "")
            $this->mode = self::MODE_INLINE;

        $this->render('link', array(
            'id' => $this->object->content->object_model . "_" . $this->object->content->object_id,
            'mode' => $this->mode,
            'objectModel' => $this->object->content->object_model,
            'objectId' => $this->object->content->object_id,
        ));
    }

    /**
     * Returns count of existing comments 
     * 
     * @return Int Comment Count
     */
    public function getCommentsCount()
    {
        return Comment::GetCommentCount(get_class($this->object), $this->object->getPrimaryKey());
    }

}

?>