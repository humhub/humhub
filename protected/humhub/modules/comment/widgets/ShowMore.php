<?php

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\content\controllers\SearchController;
use humhub\modules\content\models\Content;
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
    public ?Content $content;
    public ?CommentModel $parentComment;

    public int $pageSize = 5;
    public string $direction;
    public ?int $commentId;
    private ?int $_count = null;

    public function run()
    {
        if (empty($this->commentId) || !$this->count) {
            return '';
        }

        return $this->render('showMore', [
            'text' => $this->getText(),
            'showMoreUrl' => Url::to([
                '/comment/comment/show',
                'contentId' => $this->content->id,
                'parentCommentId' => $this->parentComment->id ?? '',
                'pageSize' => $this->pageSize,
                'commentId' => $this->commentId,
                'direction' => $this->direction,
            ]),
            'direction' => $this->direction,
            'linkStyleClass' => $this->getLinkStyleClass(),
        ]);
    }

    private function getText(): string
    {
        return $this->direction === CommentListService::LIST_DIR_PREV
            ? Yii::t('CommentModule.base', "Show previous {count} comments", ['{count}' => $this->count])
            : Yii::t('CommentModule.base', "Show next {count} comments", ['{count}' => $this->count]);
    }

    public function getCount(): int
    {
        if ($this->_count === null) {
            $this->_count = count(
                (new CommentListService($this->content, $this->parentComment))->getSiblings(
                    $this->commentId,
                    $this->pageSize,
                    $this->direction,
                ),
            );
        }


        return $this->_count;
    }

    protected function getLinkStyleClass(): ?string
    {
        // Highlight it on Content Search page
        return Yii::$app->controller instanceof SearchController && Yii::$app->controller->action->id === 'results'
            ? 'highlight'
            : null;
    }
}
