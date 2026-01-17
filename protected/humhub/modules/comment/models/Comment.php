<?php

namespace humhub\modules\comment\models;

use humhub\modules\comment\activities\NewCommentActivity as NewCommentActivity;
use humhub\modules\comment\live\NewComment as NewCommentLive;
use humhub\modules\comment\notifications\NewComment as NewCommentNotification;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property string $message
 * @property int $content_id
 * @property int $parent_comment_id
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property-read string $url
 * @property-read Comment $parentComment
 *
 * @since 0.5
 */
class Comment extends ContentAddonActiveRecord implements ContentOwner
{
    public ?int $child_count = null;

    public $fileList;

    public static function tableName()
    {
        return 'comment';
    }

    public function rules()
    {
        return [
            [['message'], 'required', 'message' => Yii::t('CommentModule.base', 'The comment must not be empty!')],
            [['fileList'], 'safe'],
        ];
    }

    public function beforeDelete()
    {
        foreach (static::findAll(['parent_comment_id' => $this->id]) as $comment) {
            $comment->delete();
        }

        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        $this->updateContentSearch();
        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes): void
    {
        // Update updated_at etc..
        $this->refresh();

        // Handle mentioned users
        // Execute before NewCommentNotification to avoid double notification when mentioned.
        $processResult = RichText::postProcess($this->message, $this, 'message');
        $mentionedUsers = (isset($processResult['mentioning'])) ? $processResult['mentioning'] : [];

        if ($insert) {
            $followerQuery = $this->content->getPolymorphicRelation()->getFollowersWithNotificationQuery();

            // Remove mentioned users from followers query to avoid double notification
            if (count($mentionedUsers) !== 0) {
                $followerQuery->andWhere([
                    'NOT IN',
                    'user.id',
                    array_map(function (User $user) {
                        return $user->id;
                    }, $mentionedUsers),
                ]);
            }

            NewCommentNotification::instance()->from($this->createdBy)->about($this)->sendBulk($followerQuery);
            NewCommentActivity::create($this, $this->createdBy);

            if ($this->content->container) {
                Yii::$app->live->send(new NewCommentLive([
                    'contentContainerId' => $this->content->container->id,
                    'visibility' => $this->content->visibility,
                    'contentId' => $this->content->id,
                    'commentId' => $this->id,
                ]));
            }
        }

        $this->updateContentSearch();

        $this->fileManager->attach($this->fileList);

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Force search update of underlying content object.
     * (This has also indexed the comments.)
     */
    private function updateContentSearch()
    {
        if ($this->content) {
            (new ContentSearchService($this->content))->update();
        }
    }

    public function getContentName()
    {
        return Yii::t('CommentModule.base', 'comment');
    }

    public function getContentDescription()
    {
        return $this->message;
    }

    public function canDelete($userId = ''): bool
    {
        if ($userId == '') {
            $userId = Yii::$app->user->id;
        }

        if ($this->created_by === $userId || Yii::$app->user->isAdmin()) {
            return true;
        }

        if ($this->content->container instanceof Space && $this->content->container->isAdmin($userId)) {
            return true;
        }

        return false;
    }

    /**
     * TODO: Unify with Content::isUpdated() see https://github.com/humhub/humhub/pull/4380
     * @returns bool true if this comment has been updated, otherwise false
     * @since 1.7
     */
    public function isUpdated()
    {
        return $this->created_at !== $this->updated_at && !empty($this->updated_at) && is_string($this->updated_at);
    }

    public function getUrl($scheme = true): string
    {
        if ($this->isNewRecord) {
            return $this->content->getUrl();
        }

        return Url::to(['/comment/perma', 'id' => $this->id], $scheme);
    }

    public function getParentComment(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Comment::class, ['id' => 'parent_comment_id']);
    }

    public function getContentOwnerObject(): ContentOwner
    {
        if ($this->parentComment) {
            return $this->parentComment;
        }

        return $this->content;
    }

    public function getChildCount(): int
    {
        if ($this->child_count === null) {
            $this->child_count = Comment::find()->andWhere(['parent_comment_id' => $this->id])->count();
        }
        return $this->child_count;
    }
}
