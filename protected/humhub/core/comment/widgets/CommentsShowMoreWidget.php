<?php

/**
 * CommentsShowMoreWidget 
 *
 * @package humhub.modules_core.comment
 * @since 0.11
 * @author luke
 */
class CommentsShowMoreWidget extends HWidget
{

    /**
     * Content Object
     */
    public $object;

    /**
     * @var CPagination
     */
    public $pagination;

    /**
     * Executes the widget.
     */
    public function run()
    {

        if ($this->pagination->getPageCount() == $this->pagination->getCurrentPage() + 1) {
            return;
        }

        $showMoreUrl = CHtml::normalizeUrl(Yii::app()->createUrl('comment/comment/show', array(
                            'contentModel' => get_class($this->object),
                            'contentId' => $this->object->getPrimaryKey(),
                            'page' => $this->pagination->getCurrentPage() + 2)
        ));

        
        $moreCount = $this->pagination->getPageSize();
        if ($this->pagination->getPageCount() == $this->pagination->getCurrentPage()+2) {
            $moreCount = $this->pagination->getItemCount() - $this->pagination->getPageSize() - $this->pagination->getOffset();
        }
        
        $this->render('showMore', array(
            'object' => $this->object,
            'pagination' => $this->pagination,
            'id' => get_class($this->object) . "_" . $this->object->getPrimaryKey(),
            'showMoreUrl' => $showMoreUrl,
            'moreCount' => $moreCount
        ));
    }

}

?>