<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\SocialActivity;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\notification\jobs\SendBulkNotification;
use humhub\modules\notification\jobs\SendNotification;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\Html;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\mail\MessageInterface;

/**
 * A BaseNotification class describes the behaviour and the type of a Notification.
 * A BaseNotification is created and can be sent to one or multiple users over different targets.
 *
 * The BaseNotification can should be created like this:
 *
 * MyNotification::instance()->from($originator)->about($source)->sendBulk($activeQueryUser);
 *
 * This will send Notifications to different notification targets by using a queue.
 *
 * @property Notification $record
 * @author luke
 */
abstract class BaseNotification extends SocialActivity
{
    /**
     * @var bool automatically mark notification as seen after click on it
     */
    public $markAsSeenOnClick = true;

    /**
     * @var int number of combined notifications
     */
    public $groupCount = 0;

    /**
     * @since 1.2.3
     * @see NotificationManager
     * @var bool do not send this notification also to the originator
     */
    public $suppressSendToOriginator = true;

    /**
     * @var string the group key
     */
    protected $_groupKey = null;

    /**
     * @var NotificationCategory cached category instance
     */
    protected $_category = null;

    /**
     * @inheritdoc
     */
    public $recordClass = Notification::class;

    /**
     * Additional user data available for the notification
     *
     * @var array|null
     * @since 1.11
     */
    public $payload = null;

    /**
     * Priority flag, if set to true, this Notification type will be marked as high priority.
     * This can be used by a given BaseTarget while handling a Notification.
     *
     * A MobileTargetProvider for example could use this flag for Android devices to wake up the device out of doze mode.
     *
     * @var bool if set to true marks this notification type as high priority.
     * @since 1.2.3
     */
    public $priority = false;

    /**
     * Returns the notification category instance. If no category class is set (default) the default notification settings
     * can't be overwritten.
     *
     * The category instance is cached, once created.
     *
     * If the Notification configuration should be configurable subclasses have to overwrite this method.
     *
     * @return NotificationCategory
     */
    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = $this->category();
        }

        return $this->_category;
    }

    /**
     * Returns a new NotificationCategory instance.
     *
     * This function should be overwritten by subclasses to append this BaseNotification
     * to the returned category. If no category instance is returned, the BaseNotification behavriour (targets) will not be
     * configurable.
     *
     * @return NotificationCategory
     */
    protected function category()
    {
        return null;
    }

    /**
     * Checks if notification is still valid before sending
     *
     * @return bool
     *
     * @since 1.15
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getViewParams($params = [])
    {
        if ($this->hasContent() && $this->getContent()->updated_at instanceof Expression) {
            $this->getContent()->refresh();
            $date = $this->getContent()->updated_at;
        } elseif ($this->hasContent()) {
            $date = $this->getContent()->updated_at;
        } else {
            $date = null;
        }

        if (!empty($this->record->payload)) {
            $this->payload = Json::decode($this->record->payload);
        }

        if ($this->hasContent()) {
            $url = Url::to(['/notification/entry', 'id' => $this->record->id, 'cId' => $this->getContent()->id], true);
            $relativeUrl = Url::to(['/notification/entry', 'id' => $this->record->id, 'cId' => $this->getContent()->id], false);
        } else {
            $url = Url::to(['/notification/entry', 'id' => $this->record->id], true);
            $relativeUrl = Url::to(['/notification/entry', 'id' => $this->record->id], false);
        }

        $result = [
            'url' => $url,
            'relativeUrl' => $relativeUrl,
            'date' => $date,
            'isNew' => !$this->record->seen,
        ];

        return ArrayHelper::merge(parent::getViewParams($result), $params);
    }

    /**
     * Sends this notification to a set of users.
     *
     * Note: For compatibility reasons this method also allows to pass an array of user objects.
     * This support will removed in future versions.
     *
     * @param ActiveQueryUser|array|User[] $query the user query
     * @throws InvalidConfigException
     */
    public function sendBulk($query)
    {
        if (empty($this->moduleId)) {
            throw new InvalidConfigException('No moduleId given for "' . get_class($this) . '"');
        }

        if (!$query instanceof ActiveQueryUser) {
            /** @var array $query */
            Yii::debug('BaseNotification::sendBulk - pass ActiveQueryUser instead of array!', 'notification');

            // Migrate given array to ActiveQueryUser
            $query = User::find()->where(['IN', 'user.id', array_map(function ($user) {
                if ($user instanceof User) {
                    return $user->id;
                }
                // User id
                return $user;
            }, $query)]);
        }

        Yii::$app->queue->push(new SendBulkNotification(['notification' => $this, 'query' => $query]));
    }

    /**
     * Sends this notification to all notification targets of the given User.
     * This function will not send notifications to the originator itself.
     *
     * @param User $user
     * @throws InvalidConfigException
     */
    public function send(User $user)
    {
        if (empty($this->moduleId)) {
            throw new InvalidConfigException('No moduleId given for "' . get_class($this) . '"');
        }

        if ($this->suppressSendToOriginator && $this->isOriginator($user)) {
            return;
        }

        if ($this->isBlockedFromUser($user)) {
            return;
        }

        if ($this->isBlockedForUser($user)) {
            return;
        }

        Yii::$app->queue->push(new SendNotification(['notification' => $this, 'recipientId' => $user->id]));
    }

    /**
     * Returns a non html encoded mail subject which will be used in the notification e-mail
     *
     * @return string the subject
     * @see \humhub\modules\notification\targets\MailTarget
     */
    public function getMailSubject()
    {
        return 'New notification';
    }

    /**
     * Checks if the given $user is the originator of this notification.
     *
     * @param User $user
     * @return bool
     */
    public function isOriginator(User $user)
    {
        return $this->originator && $this->originator->id === $user->id;
    }

    /**
     * Checks if the originator blocked the given $user in order to avoid receive any notifications from the $user.
     *
     * @param User $user
     * @return bool
     * @since 1.10
     */
    public function isBlockedFromUser(User $user): bool
    {
        return $this->originator && $user->isBlockedForUser($this->originator);
    }

    /**
     * Checks if the source is blocked for the receiver $user.
     * For example, if the $user is not a member of a private Space
     *
     * @param User $user
     * @return bool
     * @since 1.11.2
     */
    public function isBlockedForUser(User $user): bool
    {
        if ($this->isSpaceContent()) {
            /* @var Space $space */
            $space = $this->source->content->container;
            return $space->visibility === Space::VISIBILITY_NONE
                && !$space->isMember($user);
        }

        return false;
    }

    /**
     * Check if the source is a Content from a Space
     *
     * @return bool
     * @since 1.11.2
     */
    private function isSpaceContent(): bool
    {
        return ($this->source instanceof ContentActiveRecord
                || $this->source instanceof ContentAddonActiveRecord)
            && $this->source->content->container instanceof Space;
    }

    /**
     * Creates the Notification instance of the current BaseNotification type for the
     * given $user.
     *
     * @param User $user
     * @return bool
     */
    public function saveRecord(User $user)
    {
        if (!$this->validate()) {
            return false;
        }

        $notification = new Notification([
            'user_id' => $user->id,
            'class' => static::class,
            'module' => $this->moduleId,
            'group_key' => $this->getGroupKey(),
        ]);

        if ($this->source) {
            $notification->setPolymorphicRelation($this->source);
            $notification->space_id = $this->getSpaceId();
        }

        if ($this->originator) {
            $notification->originator_user_id = $this->originator->id;
        }

        if ($this->payload) {
            $notification->payload = Json::encode($this->payload);
        }

        if (!$notification->save()) {
            Yii::error(
                'Could not save Notification Record for'
                . static::class . ' '
                . print_r($notification->getErrors(), true),
            );
            return false;
        }

        $this->record = $notification;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function about($source)
    {
        if (!$source) {
            return $this;
        }
        parent::about($source);
        $this->record->space_id = $this->getSpaceId();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function from($originator)
    {
        if (!$originator) {
            return $this;
        }
        $this->originator = $originator;
        $this->record->originator_user_id = $originator->id;

        return $this;
    }

    /**
     * Set additional data
     *
     * @param $payload
     * @return $this
     * @since 1.11
     */
    public function payload($payload)
    {
        if (!$payload) {
            return $this;
        }
        $this->payload = $payload;
        $this->record->payload = $payload;

        return $this;
    }

    /**
     * Deletes this notification
     */
    public function delete(User $user = null)
    {
        $condition = [];

        $condition['class'] = static::class;

        if ($user !== null) {
            $condition['user_id'] = $user->id;
        }

        if ($this->originator !== null) {
            $condition['originator_user_id'] = $this->originator->id;
        }

        if ($this->source !== null) {
            $condition['source_pk'] = $this->source->getPrimaryKey();
            $condition['source_class'] = PolymorphicRelation::getObjectModel($this->source);
        }

        Notification::deleteAll($condition);
    }

    /**
     * Marks notification as seen
     */
    public function markAsSeen()
    {
        if ($this->record->group_key != '') {
            // Ensure to update all grouped notifications
            Notification::updateAll([
                'seen' => 1,
            ], [
                'class' => $this->record->class,
                'user_id' => $this->record->user_id,
                'group_key' => $this->record->group_key,
            ]);
        } else {
            $this->record->seen = 1;
            $this->record->save();
        }

        // Automatically mark similar notifications (same source) as seen
        $similarNotifications = Notification::find()
            ->where(['source_class' => $this->record->source_class, 'source_pk' => $this->record->source_pk, 'user_id' => $this->record->user_id])
            ->andWhere(['!=', 'seen', '1']);
        foreach ($similarNotifications->all() as $notification) {
            /* @var $notification Notification */
            $notification->getBaseModel()->markAsSeen();
        }
    }

    /**
     * Returns a key for grouping notifications.
     * If null is returned (default) the notification grouping for this BaseNotification type disabled.
     *
     * The returned key could for example be a combination of classname related content id.
     *
     * @return string the group key
     */
    public function getGroupKey()
    {
        return null;
    }

    /**
     * Renders the Notificaiton for the given notification target.
     * Subclasses are able to use custom renderer for different targets by overwriting this function.
     *
     * @param BaseTarget $target
     * @return string render result
     */
    public function render(BaseTarget $target = null)
    {
        if (!$target) {
            $target = Yii::$app->notification->getTarget(WebTarget::class);
        }

        return $target->getRenderer()->render($this);
    }

    /**
     * Returns the combined display names of a grouped notification.
     *
     * Examples:
     *      User A and User B
     *      User A and 5 others
     *
     * @param bool $html if true the result will be encoded and may contain html
     * @return string the display names
     */
    public function getGroupUserDisplayNames($html = true)
    {
        if ($this->groupCount > 2) {
            list($user) = $this->getGroupLastUsers(1);
            $displayName = $html ? Html::tag('strong', Html::encode($user->displayName)) : $user->displayName;
            return Yii::t('NotificationModule.base', '{displayName} and {number} others', [
                'displayName' => $displayName,
                'number' => $this->groupCount - 1,
            ]);
        }

        $users = $this->getGroupLastUsers(2);
        $usersCount = count($users);

        if ($usersCount === 0) {
            return '[Deleted user]';
        }

        $displayName1 = $html ? Html::tag('strong', Html::encode($users[0]->displayName)) : $users[0]->displayName;
        if ($usersCount === 1) {
            return $displayName1;
        }

        $displayName2 = $html ? Html::tag('strong', Html::encode($users[1]->displayName)) : $users[1]->displayName;

        return Yii::t('NotificationModule.base', '{displayName} and {displayName2}', [
            'displayName' => $displayName1,
            'displayName2' => $displayName2,
        ]);
    }

    /**
     * Returns the last users of a grouped notification
     *
     * @param int $limit users to return
     * @return User[] the number of user
     */
    public function getGroupLastUsers($limit = 2)
    {
        $users = [];

        $query = Notification::find()
            ->where([
                'notification.user_id' => $this->record->user_id,
                'notification.class' => $this->record->class,
                'notification.group_key' => $this->record->group_key,
            ])
            ->joinWith(['originator', 'originator.profile'])
            ->orderBy(['notification.created_at' => SORT_DESC])
            ->groupBy(['notification.originator_user_id'])
            ->andWhere(['IS NOT', 'user.id', new Expression('NULL')])
            ->limit($limit);

        foreach ($query->all() as $notification) {
            $users[] = $notification->originator;
        }

        return $users;
    }

    /**
     * @inheritdoc
     */
    public function asArray(User $user)
    {
        $result = parent::asArray($user);
        $result['mailSubject'] = $this->getMailSubject();
        return $result;
    }

    /**
     * Should be overwritten by subclasses for a html representation of the notification.
     * @return string
     */
    public function html()
    {
        // Only for backward compatibility.
        return $this->getAsHtml();
    }

    /**
     * Use text() instead
     * @deprecated since version 1.2
     */
    public function getAsText()
    {
        return $this->text();
    }

    /**
     * Use html() instead
     * @deprecated since version 1.2
     */
    public function getAsHtml()
    {
        return null;
    }

    /**
     * This method is invoked right before a mail will be send for this notificatoin
     *
     * @param MessageInterface $message
     * @return bool when true the mail will be send
     * @see \humhub\modules\notification\targets\MailTarget
     */
    public function beforeMailSend(MessageInterface $message)
    {
        return true;
    }
}
