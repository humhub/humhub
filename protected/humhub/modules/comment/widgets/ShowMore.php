<?php

namespace humhub\modules\comment\widgets;

/**
 * CommentsShowMoreWidget
 *
 * @package humhub.modules_core.comment
 * @since 0.11
 * @author luke
 */
class ShowMore extends \yii\base\Widget
{

    /**
     * Content Object
     */
    public $object;

    /**
     * @var \yii\data\Pagination;
     */
    public $pagination;

    /**
     * Executes the widget.
     */
    public function run()
    {
        
        if (!$this->pagination->totalCount || $this->pagination->pageCount == $this->pagination->page + 1) {
            return;
        }

        $showMoreUrl = \yii\helpers\Url::to([
            '/comment/comment/show',
            'contentModel' => get_class($this->object),
            'contentId' => $this->object->getPrimaryKey(),
            'page' => $this->pagination->page + 2
        ]);

        $moreCount = $this->pagination->pageSize;
        if ($this->pagination->pageCount == $this->pagination->page + 2) {
            $moreCount = $this->pagination->totalCount - $this->pagination->pageSize - $this->pagination->offset;
        }

        return $this->render('showMore', [
            'object' => $this->object,
            'pagination' => $this->pagination,
            'id' => $this->object->getUniqueId(),
            'showMoreUrl' => $showMoreUrl,
            'moreCount' => $moreCount
        ]);
    }

}