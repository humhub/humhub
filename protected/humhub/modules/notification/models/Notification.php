<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\models;

use humhub\components\ActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Json;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property string $class
 * @property int $user_id the recipient
 * @property int|null $originator_id the user who caused the notification
 * @property int|null $content_id set when the source is a content or a content addon (e.g. a comment)
 * @property int|null $contentcontainer_id the content's container, or the container itself when it is the source
 * @property int|null $source_record_id {@see \humhub\models\RecordMap} id of any other source record
 * @property int|null $grouping_key id of the group head; equals `id` while ungrouped
 * @property int $priority see {@see \humhub\modules\notification\components\NotificationPriority}
 * @property int $listed 1 if the notification appears in the web list
 * @property string|array|null $payload JSON; an array is encoded on save, so after `save()` the attribute holds the JSON string
 * @property string|null $seen_at
 * @property string $created_at
 *
 * @property-read User $user
 * @property-read User|null $originator
 * @property-read Content|null $content
 * @property-read ContentContainer|null $contentContainer
 */
class Notification extends ActiveRecord
{
    /**
     * @var int|null size of the group this row represents, set by {@see ActiveQueryNotification::grouped()}
     */
    public ?int $group_count = null;

    /**
     * @var int|null newest id of the group, set by {@see ActiveQueryNotification::grouped()}
     */
    public ?int $group_max_id = null;

    /**
     * @var int|null 1 when any member of the group is unseen, set by {@see ActiveQueryNotification::grouped()}
     */
    public ?int $group_unseen = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class', 'user_id'], 'required'],
            [['user_id', 'originator_id', 'content_id', 'contentcontainer_id', 'source_record_id', 'grouping_key', 'priority', 'listed'], 'integer'],
            [['class'], 'string', 'max' => 255],
            [['payload', 'seen_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (is_array($this->payload)) {
            $this->payload = Json::encode($this->payload);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && $this->grouping_key === null) {
            $this->updateAttributes(['grouping_key' => $this->id]);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Keeps the grouping consistent: see {@see \humhub\modules\notification\services\GroupingService::beforeDelete()}.
     *
     * @inheritdoc
     * @since 1.20
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // The source may already be gone (integrity cleanup, cascading deletes); a notification
        // that cannot be loaded any more must still be deletable.
        try {
            $notification = NotificationManager::fromRecord($this);
        } catch (Throwable $e) {
            Yii::warning('Could not load notification #' . $this->id . ' for grouping before delete: ' . $e->getMessage(), 'notification');
            return true;
        }

        $notification->getGroupingService()->beforeDelete();

        return true;
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getOriginator(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'originator_id']);
    }

    public function getContent(): ActiveQuery
    {
        return $this->hasOne(Content::class, ['id' => 'content_id']);
    }

    public function getContentContainer(): ActiveQuery
    {
        return $this->hasOne(ContentContainer::class, ['id' => 'contentcontainer_id']);
    }

    /**
     * @inheritdoc
     * @return ActiveQueryNotification
     */
    public static function find(): ActiveQueryNotification
    {
        return new ActiveQueryNotification(static::class);
    }
}
