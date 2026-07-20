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
    public const LIST_DIR_PREV = 'previous';
    public const LIST_DIR_NEXT = 'next';


    public function __construct(private readonly Content $content, private readonly ?Comment $parentComment)
    {
    }

    public static function create(ContentActiveRecord|Comment $object)
    {
        return new static(
            $object->content,
            ($object instanceof Comment) ? $object : null,
        );
    }

    /**
     * Returns the number of comments of the content, including sub comments.
     * With a parent comment given, only its sub comments are counted.
     */
    public function getCount(): int
    {
        $query = Comment::find()->andWhere(['content_id' => $this->content->id]);

        if ($this->parentComment) {
            $query->andWhere(['parent_comment_id' => $this->parentComment->id]);
        }

        return (int)$query->count();
    }

    public function getLimited(?int $limit, ?int $highlightCommentId = null): array
    {
        $limit ??= $this->getModule()->commentsPreviewMax;

        if ($this->parentComment?->id === $highlightCommentId) {
            // No need to find current comment in sub-comments when parent comment is the current
            $highlightCommentId = null;
        }

        $query = $this->getQuery()->limit($limit);

        // Force a specific comment in the results
        if ($highlightCommentId !== null) {
            $showComment = $this->getComment($highlightCommentId);
            if ($showComment === null) {
                Yii::error("Show comment with id $highlightCommentId not found", 'comment');
                return [];
            }
            $commentIds = array_merge(
                $this->getSiblingIds($showComment->id, $limit, self::LIST_DIR_PREV),
                [$showComment->id],
                $this->getSiblingIds($showComment->id, 1, self::LIST_DIR_NEXT),
            );
            $query->where(['IN', 'id', $commentIds]);
            $query->limit(count($commentIds));
        }

        return array_reverse($query->all());
    }

    /**
     * @return ActiveQuery<Comment>
     */
    public function getQuery(): ActiveQuery
    {
        $query = Comment::find();
        $this->addScopeQueryCondition($query);
        return $query;
    }

    private function addScopeQueryCondition(ActiveQuery $query): void
    {
        $query->addSelect('*, (select count(*) from comment sc where sc.parent_comment_id=comment.id) as child_count');
        $query->orderBy('created_at DESC, id DESC');

        $query->andWhere(['content_id' => $this->content->id]);

        if ($this->parentComment) {
            $query->andWhere(['parent_comment_id' => $this->parentComment->id]);
        } else {
            $query->andWhere('parent_comment_id is null');
        }
    }

    private function getComment($id): ?Comment
    {
        $query = Comment::find();
        $this->addScopeQueryCondition($query);

        return $query->andWhere(['id' => $id])->one();
    }

    public function getSiblings(int $commentId, int $limit = 5, string $sortOrder = self::LIST_DIR_PREV): array
    {
        $query = Comment::find()
            ->limit($limit);

        $this->addScopeQueryCondition($query);

        if ($sortOrder === self::LIST_DIR_NEXT) {
            $query->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC]);
            if ($commentId) {
                $query->andWhere(['>', 'id', $commentId]);
            }
            return $query->all();
        }

        $query->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
        if ($commentId) {
            $query->andWhere(['<', 'id', $commentId]);
        }
        return array_reverse($query->all());
    }

    public function getSiblingsCount(int $commentId, string $sortOrder = self::LIST_DIR_PREV): int
    {
        $query = Comment::find();
        $this->addScopeQueryCondition($query);

        if ($commentId) {
            $query->andWhere([$sortOrder === self::LIST_DIR_NEXT ? '>' : '<', 'id', $commentId]);
        }

        return (int)$query->count();
    }

    private function getSiblingIds(int $commentId, int $limit = 5, string $sortOrder = self::LIST_DIR_PREV): array
    {
        return array_map(
            fn($record) => $record->id,
            $this->getSiblings($commentId, $limit, $sortOrder),
        );
    }

    private function getModule(): Module
    {
        return Yii::$app->getModule('comment');
    }

}
