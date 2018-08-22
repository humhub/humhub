<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\models;

use Yii;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\like\notifications\NewLike;

/**
 * This is the model class for table "like".
 *
 * The followings are the available columns in table 'like':
 * @property integer $id
 * @property integer $target_user_id
 * @property string $object_model
 * @property integer $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @since 0.5
 */
class Like extends ContentAddonActiveRecord
{

    /**
     * @inheritdoc
     */
    protected $updateContentStreamSort = false;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return 'like';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\PolymorphicRelation::class,
                'mustBeInstanceOf' => [
                    \yii\db\ActiveRecord::class,
                ]
            ]
        ];
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['object_model', 'object_id'], 'required'],
            [['id', 'object_id', 'target_user_id'], 'integer'],
        ];
    }

    /**
     * Like Count for specifc model
     */
    public static function GetLikes($objectModel, $objectId)
    {
        $cacheId = "likes_" . $objectModel . "_" . $objectId;
        $cacheValue = Yii::$app->cache->get($cacheId);

        if ($cacheValue === false) {
            $newCacheValue = Like::findAll(['object_model' => $objectModel, 'object_id' => $objectId]);
            Yii::$app->cache->set($cacheId, $newCacheValue, Yii::$app->settings->get('cache.expireTime'));
            return $newCacheValue;
        } else {
            return $cacheValue;
        }
    }

    /**
     * After Save, delete LikeCount (Cache) for target object
     */
    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->cache->delete('likes_' . $this->object_model . "_" . $this->object_id);

        \humhub\modules\like\activities\Liked::instance()->about($this)->save();

        if ($this->getSource() instanceof ContentOwner && $this->getSource()->content->createdBy !== null) {
            // This is required for comments where $this->getSoruce()->createdBy contains the comment author.
            $target = isset($this->getSource()->createdBy) ? $this->getSource()->createdBy : $this->getSource()->content->createdBy;
            NewLike::instance()->from(Yii::$app->user->getIdentity())->about($this)->send($target);
        }

        $this->automaticContentFollowing = Yii::$app->getModule('like')->autoFollowLikedContent;

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Before Delete, remove LikeCount (Cache) of target object.
     * Remove activity
     */
    public function beforeDelete()
    {
        Yii::$app->cache->delete('likes_' . $this->object_model . "_" . $this->object_id);
        return parent::beforeDelete();
    }

}
