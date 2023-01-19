<?php

namespace humhub\modules\comment\widgets;

use yii\helpers\Url;

/**
 * CommentsShowMoreWidget
 *
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
            return '';
        }

        $showMoreUrl = Url::to([
            '/comment/comment/show',
            'objectModel' => get_class($this->object),
            'objectId' => $this->object->getPrimaryKey(),
            'pageSize' => $this->pagination->pageSize,
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
