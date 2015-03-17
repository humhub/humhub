<?php

/**
 * CommentsPaginationWidget 
 *
 * @package humhub.modules_core.comment
 * @since 0.11
 * @author luke
 */
class CommentsPaginationWidget extends HWidget
{

    /**
     * Content Object
     */
    public $object;
    public $pagination;

    /**
     * Executes the widget.
     */
    public function run()
    {

        if ($this->pagination->getPageCount() == 1) {
            return;
        }

        $nextUrl = "";
        if ($this->pagination->getCurrentPage() != 0) {
            $nextUrl = CHtml::normalizeUrl(Yii::app()->createUrl('comment/comment/show', array(
                                'contentModel' => get_class($this->object),
                                'contentId' => $this->object->getPrimaryKey(),
                                'page' => $this->pagination->getCurrentPage() - 2)
            ));
        }

        $prevUrl = "";
        if ($this->pagination->getPageCount() != $this->pagination->getCurrentPage() + 1) {
            $prevUrl = CHtml::normalizeUrl(Yii::app()->createUrl('comment/comment/show', array(
                                'contentModel' => get_class($this->object),
                                'contentId' => $this->object->getPrimaryKey(),
                                'page' => $this->pagination->getCurrentPage() + 2)
            ));
        }

        $showTo = $this->pagination->getItemCount() - $this->pagination->getOffset();
        $showFrom = $showTo - $this->pagination->getPagesize();
        if ($showFrom <= 0)
            $showFrom = 1;

        $this->render('pagination', array(
            'object' => $this->object,
            'pagination' => $this->pagination,
            'id' => get_class($this->object) . "_" . $this->object->getPrimaryKey(),
            'previousUrl' => $prevUrl,
            'nextUrl' => $nextUrl,
            'showFrom' => $showFrom,
            'showTo' => $showTo,
            'showTotal' => $this->pagination->getItemCount(),
        ));
    }

}

?>