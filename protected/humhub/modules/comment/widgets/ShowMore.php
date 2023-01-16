<?php

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\Comment;
use Yii;
use yii\base\Widget;
use yii\helpers\Url;

/**
 * CommentsShowMoreWidget
 *
 * @property-read int $count
 *
 * @since 0.11
 * @author luke
 */
class ShowMore extends Widget
{
    const TYPE_PREVIOUS = 'previous';
    const TYPE_NEXT = 'next';

    /**
     * Content Object
     */
    public $object;

    /**
     * @var int
     */
    public $pageSize;

    /**
     * @var string Type of loaded comments: 'previous', 'next'
     */
    public $type = self::TYPE_PREVIOUS;

    /**
     * @var int|null ID of the latest comment from previous query
     */
    public $commentId;

    /**
     * @var int Cached count of the next/previous comments
     */
    private $_count;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (!$this->count) {
            return '';
        }

        return $this->render('showMore', [
            'text' => $this->getText(),
            'showMoreUrl' => Url::to(['/comment/comment/show',
                'objectModel' => get_class($this->object),
                'objectId' => $this->object->getPrimaryKey(),
                'pageSize' => $this->pageSize,
                'commentId' => $this->commentId,
                'type' => $this->type,
            ]),
            'type' => $this->type,
        ]);
    }

    private function getText(): string
    {
        return $this->type === self::TYPE_PREVIOUS
            ? Yii::t('CommentModule.base', "Show previous {count} comments", ['{count}' => $this->count])
            : Yii::t('CommentModule.base', "Show next {count} comments", ['{count}' => $this->count]);
    }

    public function getCount(): int
    {
        if ($this->_count === null) {
            $this->_count = count(Comment::getMoreComments($this->object, $this->commentId, $this->type, $this->pageSize));
        }

        return $this->_count;
    }

}
