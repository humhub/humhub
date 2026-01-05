<?php

namespace humhub\modules\comment\services;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\Module;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use Yii;
use yii\db\ActiveQuery;

class CommentListService
{
    const LIST_DIR_PREV = 'previous';
    const LIST_DIR_NEXT = 'next';


    public function __construct(private readonly Content $content, private readonly ?Comment $parentComment)
    {
    }

    public static function create(ContentActiveRecord|Comment $object)
    {
        return new static(
            $object->content,
            ($object instanceof Comment) ? $object : null
        );
    }

    public function getCount(): int
    {
        $query = Comment::find();
        $this->addScopeQueryCondition($query);

        return $query->count();
    }

    public function getLimited(?int $limit, ?int $highlightCommentId = null): array
    {
        $limit ??= $this->getModule()->commentsPreviewMax;

        if ($this->parentComment?->id === $highlightCommentId) {
            // No need to find current comment in sub-comments when parent comment is the current
            $highlightCommentId = null;
        }

        $query = Comment::find();
        $query->limit($limit);
        $query->orderBy('created_at DESC, id DESC');
        $this->addScopeQueryCondition($query);

        // Force a specific comment in the results
        if ($highlightCommentId !== null) {
            $showComment = $this->getComment($highlightCommentId);
            if ($showComment === null) {
                Yii::error("Show comment with id $highlightCommentId not found", 'comment');
                return [];
            }
            $commentIds = array_merge(
                $this->getSiblingIds($showComment->id, $limit, SORT_DESC),
                [$showComment->id],
                $this->getSiblingIds($showComment->id, 1, SORT_ASC),
            );
            $query->where(['IN', 'id', $commentIds]);
            $query->limit(count($commentIds));
        }

        return array_reverse($query->all());
    }

    private function addScopeQueryCondition(ActiveQuery $query): void
    {
        $query->andWhere(['content_id' => $this->content->id]);

        if ($this->parentComment) {
            $query->andWhere(['parent_comment_id' => $this->parentComment->id]);
        } else {
            $query->andWhere('parent_comment_id is null');
        }
    }

    private function getComment($id): ?Comment
    {
        $query = Comment::find()->where(['id' => $id]);
        $this->addScopeQueryCondition($query);
        return $query->one();
    }

    public function getSiblings(int $commentId, int $limit = 5, string $sortOrder = self::LIST_DIR_PREV): array
    {
        $query = Comment::find()
            ->limit($limit);

        $this->addScopeQueryCondition($query);

        if ($sortOrder === self::LIST_DIR_NEXT) {
            $query->andWhere(['>', 'id', $commentId])->orderBy('created_at ASC');
            return $query->all();
        } else {
            $query->andWhere(['<', 'id', $commentId])->orderBy('created_at DESC');
            return array_reverse($query->all());
        }
    }

    private function getSiblingIds(int $commentId, int $limit = 5, string $sortOrder = self::LIST_DIR_PREV): array
    {
        return array_map(
            fn($record) => $record->id,
            $this->getSiblings($commentId, $limit, $sortOrder)
        );
    }

    private function getModule(): Module
    {
        return Yii::$app->getModule('comment');
    }

}
