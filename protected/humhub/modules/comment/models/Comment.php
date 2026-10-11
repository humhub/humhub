<?php

namespace humhub\modules\comment\models;

use humhub\components\db\AfterCommit;
use humhub\modules\comment\activities\NewCommentActivity as NewCommentActivity;
use humhub\modules\comment\live\NewComment as NewCommentLive;
use humhub\modules\comment\notifications\CommentReplyNotification;
use humhub\modules\comment\notifications\NewCommentNotification;
use humhub\modules\comment\services\CommentPayloadCache;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\notification\components\NotificationSet;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\MentionedNotification;
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
            // `!` prefix: validated but NOT massively assignable — the parent id is always
            // set programmatically by the controllers, never from a request body.
            [['!parent_comment_id'], 'validateParentComment'],
        ];
    }

    /**
     * Comments nest at most one level: a reply's parent must be a root comment of the
     * same content. Enforced model-side so every write path (web, REST API, console)
     * is guarded — previously only the JSON controller checked this.
     *
     * @since 1.20
     */
    public function validateParentComment(string $attribute): void
    {
        if (empty($this->parent_comment_id)) {
            return;
        }

        $parent = static::findOne(['id' => $this->parent_comment_id, 'content_id' => $this->content_id]);

        if ($parent === null || $parent->parent_comment_id !== null) {
            $this->addError($attribute, Yii::t('CommentModule.base', 'Comments can only be nested one level deep.'));
        }
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
        if ($this->parent_comment_id !== null) {
            CommentReplyNotification::revokeReply($this);
        }
        $this->updateContentSearch();
        CommentPayloadCache::invalidateContent($this->content_id);
        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes): void
    {
        // Update updated_at etc..
        $this->refresh();

        // Retire the cached payloads of this content - a new, edited or deleted comment
        // changes its own window AND, for a reply, its root comment's embedded preview.
        // See CommentPayloadCache for what this does and does not cover.
        CommentPayloadCache::invalidateContent($this->content_id);

        if ($insert) {
            // The mentioned users are notified with the others, each user by one notification only
            $processResult = Mentioning::withoutNotification(fn() => RichText::postProcess($this->message, $this, 'message'));
            $this->sendNotifications($processResult['mentioning'] ?? []);
            NewCommentActivity::send($this, $this->createdBy);

            if ($this->content->container) {
                $event = new NewCommentLive([
                    'contentContainerId' => $this->content->container->id,
                    'visibility' => $this->content->visibility,
                    'contentId' => $this->content->id,
                    'commentId' => $this->id,
                ]);
                AfterCommit::run(static fn() => Yii::$app->live->send($event));
            }
        } else {
            // a mention added by an edit is notified as such
            RichText::postProcess($this->message, $this, 'message');
        }

        $this->updateContentSearch();

        $this->fileManager->attach($this->fileList);

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Notifies everyone concerned by this new comment once, by the best-matching notification:
     * the mentioned users, then the author of the replied-to comment, then the content's followers
     * (its author included).
     *
     * @param User[] $mentionedUsers
     */
    private function sendNotifications(array $mentionedUsers): void
    {
        $set = NotificationSet::create(source: $this, originator: $this->createdBy)
            ->add(MentionedNotification::class, $mentionedUsers);

        $parent = $this->parentComment;
        if ($parent !== null) {
            // About the replied-to comment, so the replies to it are grouped; each reply is a notification of its own
            $set->add(CommentReplyNotification::class, (int)$parent->created_by, source: $parent, payload: ['replyId' => $this->id], dedupe: false);
        }

        $set->add(NewCommentNotification::class, $this->content->getPolymorphicRelation()->getFollowersWithNotificationQuery())
            ->send();
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

    public function getUrl(bool $scheme = false): string
    {
        if (!$this->isNewRecord) {
            return Url::to(['/comment/perma', 'id' => $this->id], $scheme);
        }

        return parent::getUrl($scheme);
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
