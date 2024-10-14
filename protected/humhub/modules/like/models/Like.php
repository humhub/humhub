<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\models\Content;
use humhub\modules\like\activities\Liked;
use humhub\modules\like\interfaces\LikeNotificationInterface;
use humhub\modules\like\Module;
use humhub\modules\like\notifications\NewLike;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "like".
 *
 * The following are the available columns in table 'like':
 * @property int $id
 * @property int $target_user_id
 * @property string $object_model
 * @property int $object_id
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property-read null|Content $content
 * @property-read null|User $user
 * @property-read ContentActiveRecord|ContentAddonActiveRecord|null|ActiveRecord $source
 *
 * @since 0.5
 */
class Like extends ActiveRecord
{
    /**
     * Source object which this Like belongs to.
     */
    private ContentActiveRecord|ActiveRecord|ContentAddonActiveRecord|null $_source = null;

    /**
     * @return string the associated database table name
     */
    public static function tableName(): string
    {
        return 'like';
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => PolymorphicRelation::class,
                'mustBeInstanceOf' => [
                    ActiveRecord::class,
                ],
            ],
        ];
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(): array
    {
        return [
            [['object_model', 'object_id'], 'required'],
            [['id', 'object_id', 'target_user_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function validate($attributeNames = null, $clearErrors = true): bool
    {
        if ($this->source !== null && !$this->source instanceof ActiveRecord) {
            $this->addError('object_model', 'Like source must be instance of ContentActiveRecord, ContentAddonActiveRecord or ActiveRecord!');
        }

        return parent::validate($attributeNames, $clearErrors);
    }

    /**
     * Like Count for specifc model
     */
    public static function GetLikes($objectModel, $objectId)
    {
        return Yii::$app->cache->getOrSet(
            "likes_{$objectModel}_{$objectId}",
            function () use ($objectModel, $objectId) {
                return Like::find()
                    ->where([
                        'object_model' => $objectModel,
                        'object_id' => $objectId,
                    ])
                    ->with('user')
                    ->all();
            },
            Yii::$app->settings->get('cache.expireTime'),
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert): bool
    {
        if ($insert
            && $this->content
            && !$this->content->getStateService()->isPublished()
        ) {
            return false;
        }

        return parent::beforeSave($insert);
    }

    /**
     * After Save, delete LikeCount (Cache) for target object
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function afterSave($insert, $changedAttributes): void
    {
        $this->flushCache();

        if ($insert) {
            Liked::instance()->about($this)->save();

            // Notify
            $targetUser = null;
            if ($this->source instanceof ContentOwner) {
                // This is required for comments where $this->source->createdBy contains the comment author.
                $targetUser = $this->source->createdBy ?? $this->source->content->createdBy;
            } elseif ($this->source instanceof LikeNotificationInterface) {
                $targetUser = $this->source->createdBy;
            }
            if ($targetUser) {
                NewLike::instance()->from(Yii::$app->user->getIdentity())->about($this)->send($targetUser);
            }
        }

        if ($this->content) {
            $this->content->updateStreamSortTime();

            /** @var Module $module */
            $module = Yii::$app->getModule('like');
            if ($module->autoFollowLikedContent) {
                $this->source->follow($this->created_by);
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Before Delete, remove LikeCount (Cache) of target object.
     * Remove activity
     */
    public function beforeDelete(): bool
    {
        $this->flushCache();
        return parent::beforeDelete();
    }

    public function flushCache(): void
    {
        Yii::$app->cache->delete('likes_' . $this->object_model . '_' . $this->object_id);
    }

    /**
     * Returns the source of this Like.
     */
    public function getSource(): ContentAddonActiveRecord|ActiveRecord|ContentActiveRecord|null
    {
        if ($this->_source !== null) {
            return $this->_source;
        }

        if (!$this->object_model || !$this->object_id) {
            return null;
        }

        if (!class_exists($this->object_model)) {
            Yii::error('Source class of Like object not found (" . $this->object_model . ") not found!', 'like');
            return null;
        }

        $this->_source = $this->object_model::findOne(['id' => $this->object_id]);
        return $this->_source;
    }

    public function getContent(): ?Content
    {
        $content = $this->source->content ?? null;
        return $content instanceof Content ? $content : null;
    }

    public function canDelete($userId = null): bool
    {
        return $this->created_by === Yii::$app->user->id;
    }

    public function canView($user = null): bool
    {
        return !$this->content || $this->content->canView($user);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
}
