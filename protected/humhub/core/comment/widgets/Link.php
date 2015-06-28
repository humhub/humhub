<?php

namespace humhub\core\comment\widgets;

/**
 * This widget is used to show a comment link inside the wall entry controls.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class Link extends \yii\base\Widget
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

        return $this->render('link', array(
                    'id' => $this->object->getUniqueId(),
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
        return \humhub\core\comment\models\Comment::GetCommentCount(get_class($this->object), $this->object->getPrimaryKey());
    }

}

?>