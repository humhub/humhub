<?php

namespace humhub\modules\like\services;

use humhub\models\RecordMap;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\Content;
use humhub\modules\like\activities\LikeActivity as LikedActivity;
use humhub\modules\like\models\Like;
use humhub\modules\like\notifications\NewLike as NewLikeNotification;
use humhub\modules\like\permissions\CanLike;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

class LikeService
{
    private readonly Content $content;
    private ?ContentAddonActiveRecord $contentAddon = null;
    private readonly ?User $user;
    private ?int $_count = null;
    private ?bool $_hasLiked = null;


    public function __construct(ContentProvider $object, ?User $user = null)
    {
        $this->content = $object->content;

        if ($object instanceof ContentAddonActiveRecord) {
            $this->contentAddon = $object;
        }

        $this->user = $user ?? Yii::$app->getUser()->identity ?? null;
    }

    public function canLike(): bool
    {
        if (!(Yii::$app->getModule('like'))->isEnabled || !$this->user) {
            return false;
        }

        if (!$this->content->getStateService()->isPublished() || $this->content->isArchived()) {
            return false;
        }

        if (isset($this->content->container) && !$this->content->container->can(new CanLike())) {
            return false;
        }

        return true;
    }

    public function hasLiked(): bool
    {
        if (!$this->user) {
            return false;
        }

        if ($this->_hasLiked === null) {
            $query = Like::find();
            $this->addScopeQueryCondition($query);
            $query->andWhere(['created_by' => $this->user->id]);

            $this->_hasLiked = ($query->count() !== 0);
        }

        return $this->_hasLiked;
    }

    public function like(): bool
    {
        if (!$this->user) {
            return false;
        }

        $like = $this->getCurrentLikeRecord();

        if (!$like) {
            $record = new Like();
            $record->content_id = $this->content->id;
            if ($this->contentAddon) {
                $record->content_addon_record_id = RecordMap::getId($this->contentAddon);
            } else {
                $record->content_addon_record_id = new Expression('NULL');
            }

            if ($record->save()) {
                $this->reset();

                $author = $this->contentAddon->createdBy ?? $this->content->createdBy;
                NewLikeNotification::instance()->from($this->user)->about($record)->send($author);

                ActivityManager::dispatch(LikedActivity::class, $record, $record->createdBy);

                return true;
            }
        }


        return false;
    }

    public function unlike(): bool
    {
        if (!$this->user) {
            return false;
        }

        $like = $this->getCurrentLikeRecord();
        if ($like) {
            $like->delete();
            $this->reset();
            return true;
        }

        return false;
    }

    private function getCurrentLikeRecord(): ?Like
    {
        if (!$this->user) {
            return null;
        }

        $query = Like::find();
        $query->andWhere(['created_by' => $this->user->id]);
        $this->addScopeQueryCondition($query);
        return $query->one();
    }

    public function getCount(): int
    {
        if ($this->_count === null) {
            $query = Like::find();
            $this->addScopeQueryCondition($query);
            $this->_count = $query->count();
        }

        return $this->_count;
    }

    public function getUserQuery(): ActiveQueryUser
    {
        $query = User::find();
        $query->leftJoin('like', 'like.created_by=user.id');
        $this->addScopeQueryCondition($query);
        // `like.id DESC` is a tiebreaker for likes sharing the same `created_at` (its
        // datetime column only has one-second resolution) - without it, offset/limit
        // pagination over ties has no stable order and can show the same liker twice
        // or skip one across two pages (see the like module's user-list JSON endpoint,
        // `LikeController::actionUserList()`).
        $query->orderBy('like.created_at DESC, like.id DESC');

        return $query;
    }

    public function addScopeQueryCondition(ActiveQuery $query): void
    {
        $query->andWhere(['like.content_id' => $this->content->id]);

        if ($this->contentAddon) {
            $query->andWhere(['like.content_addon_record_id' => RecordMap::getId($this->contentAddon)]);
        } else {
            $query->andWhere('like.content_addon_record_id IS NULL');
        }
    }

    private function getCacheKey(): string
    {
        return sprintf('like.%d.%d.%d', $this->content->id, $this->contentAddon->id, $this->user->id ?? 0);
    }

    private function reset(): void
    {
        $this->_count = null;
        $this->_hasLiked = null;
    }
}
