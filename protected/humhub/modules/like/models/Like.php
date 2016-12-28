<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\models;

use Yii;

use humhub\modules\content\components\ContentAddonActiveRecord;

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
 * @package humhub.modules_core.like.models
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
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array(['object_model', 'object_id'], 'required'),
            array(['id', 'object_id', 'target_user_id'], 'integer'),
        );
    }

    /**
     * Like Count for specifc model
     */
    public static function GetLikes($objectModel, $objectId)
    {
        $cacheId = "likes_" . $objectModel . "_" . $objectId;
        $cacheValue = Yii::$app->cache->get($cacheId);

        if ($cacheValue === false) {
            $newCacheValue = Like::findAll(array('object_model' => $objectModel, 'object_id' => $objectId));
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

        $activity = new \humhub\modules\like\activities\Liked();
        $activity->source = $this;
        $activity->create();
        
        // source itsself does not need to have creadedBy attribute
        if ($this->getSource()->hasAttribute('content') && $this->getSource()->content->createdBy !== null) {
            $notification = new \humhub\modules\like\notifications\NewLike();
            $notification->source = $this;
            $notification->originator = $this->user;
            $notification->send($this->getSource()->content->createdBy);
        }

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
