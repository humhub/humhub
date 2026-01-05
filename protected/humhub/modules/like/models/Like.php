<?php

namespace humhub\modules\like\models;

use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "like".
 *
 * The followings are the available columns in table 'like':
 *
 * @property int $id
 * @property int $content_id
 * @property string $object_model
 * @property int $object_id
 * @property string $created_at
 * @property int $created_by
 *
 * @mixin PolymorphicRelation
 * @since 0.5
 */
class Like extends ContentAddonActiveRecord
{
    protected $updateContentStreamSort = false;

    public static function tableName()
    {
        return 'like';
    }

    public function behaviors()
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

    public function afterSave($insert, $changedAttributes): void
    {
        $this->automaticContentFollowing = Yii::$app->getModule('like')->autoFollowLikedContent;
        parent::afterSave($insert, $changedAttributes);
    }

    public function getContentOwnerObject(): ContentOwner
    {
        // If the relation of this "Like" is e.g. bound to a "Comment" which implements the ContentOwner interface
        // return this. Otherwise, return "Content" as fallback.
        if ($this->polymorphicRelation instanceof ContentOwner) {
            return $this->polymorphicRelation;
        }

        return $this->content;
    }

}
