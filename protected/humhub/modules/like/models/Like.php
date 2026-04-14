<?php

namespace humhub\modules\like\models;

use humhub\models\RecordMap;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use Yii;

/**
 * This is the model class for table "like".
 *
 * The followings are the available columns in table 'like':
 *
 * @property int $id
 * @property int $content_id
 * @property int $content_addon_record_id
 * @property string $created_at
 * @property int $created_by
 */
class Like extends ContentAddonActiveRecord
{
    protected $updateContentStreamSort = false;

    public static function tableName()
    {
        return 'like';
    }

    public function afterSave($insert, $changedAttributes): void
    {
        $this->automaticContentFollowing = Yii::$app->getModule('like')->autoFollowLikedContent;
        parent::afterSave($insert, $changedAttributes);
    }

    public function getContentOwnerObject(): ContentOwner
    {
        $contentAddon = null;
        if (!empty($this->content_addon_record_id)) {
            $contentAddon = RecordMap::getById($this->content_addon_record_id, ContentOwner::class);
        }

        return $contentAddon ?? $this->content;
    }

}
