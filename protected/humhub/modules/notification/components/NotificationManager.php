<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\components\Event;
use humhub\components\Module;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * The NotificationManager component is responsible for sending BaseNotifications to Users over different
 * notification targets by using the send and sendBulk function.
 *
 * A notification target may be disabled for a specific user and will be skipped.
 *
 * @author buddha
 */
class NotificationManager
{
    /**
     * Sends the notifications categories in the results
     */
    public const EVENT_SEARCH_MODULE_NOTIFICATIONS = 'searchModuleNotifications';

    /**
     * User setting name to know if the user has modified default notification settings
     */
    public const IS_TOUCHED_SETTINGS = 'is_touched_settings';

    /**
     *
     * @var array Target configuration.
     */
    public $targets = [];

    /**
     *
     * @var BaseNotification[] Cached array of BaseNotification instances.
     */
    protected $_notifications;

    /**
     * @var BaseTarget[] Cached target instances.
     */
    protected $_targets = null;

    /**
     * Cached array of NotificationCategories
     * @var NotificationCategory[]
     */
    protected $_categories;

    /**
     * Sends the given $notification to all enabled targets of the given $users if possible
     * as bulk message.
     *
     * @param BaseNotification $notification
     * @param ActiveQueryUser $userQuery
     * @throws InvalidConfigException
     */
    public function sendBulk(BaseNotification $notification, $userQuery)
    {
        if (!$notification->isValid()) {
            return;
        }

        $processed = [];
        /** @var User $user */
        foreach ($userQuery->each() as $user) {
            if (in_array($user->id, $processed)) {
                continue;
            }

            if ($notification->suppressSendToOriginator && $notification->isOriginator($user)) {
                continue;
            }

            if ($notification->isBlockedFromUser($user)) {
                continue;
            }

            if ($notification->isBlockedForUser($user)) {
                continue;
            }

            if ($user->status != User::STATUS_ENABLED) {
                continue;
            }

            if ($notification->saveRecord($user)) {
                foreach ($this->getTargets($user) as $target) {
                    $target->send($notification, $user);
                }
            } else {
                Yii::debug('Could not store notification ' . get_class($notification) . ' for user ' . $user->id);
            }

            $processed[] = $user->id;
        }
    }

    /**
     * Sends the given $notification to all enabled targets of a single user.
     *
     * @param BaseNotification $notification
     * @param User $user target user
     * @throws InvalidConfigException
     */
    public function send(BaseNotification $notification, User $user)
    {
        $this->sendBulk($notification, User::find()->where(['user.id' => $user->id]));
    }

    /**
     * Returns all active targets for the given user.
     * If no user is given, all configured targets will be returned.
     *
     * @param User $user |null the user
     * @return BaseTarget[] the target
     * @throws InvalidConfigException
     */
    public function getTargets(User $user = null)
    {
        // Initialize targets
        if ($this->_targets === null) {
            $this->_targets = [];
            foreach ($this->targets as $targetClass => $targetConfig) {
                $targetConfig = is_array($targetConfig) ? $targetConfig : [];
                if (!isset($targetConfig['class'])) { // Allow class overwrites
                    $targetConfig['class'] = $targetClass;
                }
                $this->_targets[] = Yii::createObject($targetConfig);
            }
        }

        $userTargets = [];
        foreach ($this->_targets as $target) {
            if ($target->isActive($user)) {
                $userTargets[] = $target;
            }
        }

        return $userTargets;
    }

    /**
     * Factory function for receiving a target instance for the given class.
     *
     * @param string $class
     * @return BaseTarget
     * @throws InvalidConfigException
     */
    public function getTarget($class)
    {
        foreach ($this->getTargets() as $target) {
            if (get_class($target) == $class) {
                return $target;
            }
        }
    }

    /**
     * Checks if the given user is following notifications for the given space.
     * This is the case for members and followers with the sent_notifications settings.
     *
     * @param User $user
     * @param Space $space
     * @return bool
     */
    public function isFollowingSpace(User $user, Space $space)
    {
        $membership = $space->getMembership($user);
        if ($membership) {
            return $membership->send_notifications;
        }

        return $space->isFollowedByUser($user, true);
    }

    /**
     * Returns all notification followers for the given $content instance.
     * This function includes ContentContainer followers only if the content visibility is set to public,
     * else only space members with send_notifications settings are returned.
     *
     * @param Content $content
     * @return ActiveQueryUser
     * @throws Exception
     */
    public function getFollowers(Content $content)
    {
        return $this->getContainerFollowers($content->getContainer(), $content->isPublic());
    }

    /**
     * Returns all notification followers for the given $container. If $public is set to false
     * only members with send_notifications settings are returned.
     *
     * @param ContentContainerActiveRecord $container
     * @param bool $public
     * @return ActiveQueryUser
     */
    public function getContainerFollowers(ContentContainerActiveRecord $container, $public = true)
    {

        $query = null;

        if ($container instanceof Space) {
            $isDefault = $this->isDefaultNotificationSpace($container);

            $query = $container->getMemberListService()->getNotificationQuery();

            if ($public) {
                // Add explicit follower and non explicit follower if $isDefault
                $query->union($this->findFollowers($container, $isDefault));
            } elseif ($isDefault) {
                // Add all members without explicit following and no notification settings.
                $query->union($container->getMemberListService()->getNotificationQuery(false)
                    ->andWhere(['not exists', $this->findNotExistingSettingSubQuery()]));
            }
        } elseif ($container instanceof User) {
            // Note the notification follow logic for users is currently not implemented.
            // TODO: perhaps return only friends if public is false?

            $query = User::find()->where(['id' => $container->id]);
            if ($public) {
                $query->union(Follow::getFollowersQuery($container, true));
            }
        }
        return $query;
    }

    private function isDefaultNotificationSpace($container)
    {
        $defaultSpaces = Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces');
        return (empty($defaultSpaces)) ? false : in_array($container->guid, $defaultSpaces);
    }

    private function findFollowers($container, $isDefault = false)
    {
        // Find all followers with send_notifications = 1
        $query = Follow::getFollowersQuery($container, true);

        if ($isDefault) {
            // Add all user with no notification setting
            $query->orWhere([
                'and', 'user.status=1', ['not exists', $this->findNotExistingSettingSubQuery()],
            ]);
        }

        return $query;
    }

    private function findNotExistingSettingSubQuery()
    {
        return ContentContainerSetting::find()
            ->where('contentcontainer_setting.contentcontainer_id=user.contentcontainer_id')
            ->andWhere(['contentcontainer_setting.module_id' => 'notification'])
            ->andWhere(['contentcontainer_setting.name' => self::IS_TOUCHED_SETTINGS]);
    }

    /**
     * Returns all spaces this user is following (including member spaces) with sent_notification setting.
     *
     * @param User $user
     * @return Space[]
     */
    public function getSpaces(User $user)
    {
        $memberSpaces = Membership::getUserSpaceQuery($user, true, true)->all();
        $followSpaces = Follow::getFollowedSpacesQuery($user, true)->all();

        $result = array_merge($memberSpaces, $followSpaces);

        if (!static::isTouchedSettings($user)) {
            $result = array_merge($result, Space::find()
                ->where(['guid' => Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces')])
                ->visible($user)
                ->filterBlockedSpaces($user)
                ->all());
        }

        return $result;
    }

    /**
     * @throws \Throwable
     */
    public static function isTouchedSettings(User $user): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('notification');
        return (bool)$module->settings->user($user)?->get(self::IS_TOUCHED_SETTINGS);
    }

    /**
     * Returns all spaces this user is not following.
     *
     * @param User $user
     * @return Space[]
     */
    public function getNonNotificationSpaces(User $user = null, $limit = 25)
    {
        if ($user) {
            $memberSpaces = Membership::getUserSpaceQuery($user, true, false)->limit($limit)->all();
            $limit -= count($memberSpaces);
            $followSpaces = Follow::getFollowedSpacesQuery($user, false)->limit($limit)->all();

            return array_merge($memberSpaces, $followSpaces);
        } else {
            $defaultSpaces = Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces');
            return (empty($defaultSpaces)) ? Space::find()->limit($limit)->all() : Space::find()->where(['not in', 'guid', $defaultSpaces])->limit($limit)->all();
        }
    }

    /**
     * Sets the notification space settings for this user (or global if no user is given).
     *
     * Those are the spaces for which the user want to receive ContentCreated Notifications.
     *
     * @param string[] $spaceGuids array of space guids
     * @param User $user
     */
    public function setSpaces($spaceGuids, User $user = null)
    {
        if (!$user) { // Note: global notification space settings are currently not active!
            return Yii::$app->getModule('notification')->settings->setSerialized('sendNotificationSpaces', $spaceGuids);
        }

        $spaces = Space::findAll(['guid' => $spaceGuids]);

        // Save actual selection.
        foreach ($spaces as $space) {
            $this->setSpaceSetting($user, $space);
        }

        $spaceIds = array_map(function ($space) {
            return $space->id;
        }, $spaces);

        // Update non selected membership spaces
        Membership::updateAll(['send_notifications' => 0], [
            'and',
            ['user_id' => $user->id],
            ['not in', 'space_id', $spaceIds],
        ]);

        // Update non selected following spaces
        Follow::updateAll(['send_notifications' => 0], [
            'and',
            ['user_id' => $user->id],
            ['object_model' => Space::class],
            ['not in', 'object_id', $spaceIds],
        ]);
    }

    /**
     * Reset the notification space settings for all users
     */
    public function resetSpaces()
    {
        // Reset notifications for all selected membership spaces
        Membership::updateAll(['send_notifications' => 0]);

        // Delete all selected following spaces
        Follow::updateAll(['send_notifications' => 0], ['object_model' => Space::class]);
    }

    /**
     * Sets the send_notifications settings for the given space and user.
     *
     * @param User $user user instance for which this settings will aplly
     * @param Space $space which notifications will be followed / unfollowed
     * @param bool $follow the setting value (true by default)
     */
    public function setSpaceSetting(User $user = null, Space $space, $follow = true)
    {
        /* @var $membership Membership */
        $membership = $space->getMembership($user->id);
        if ($membership) {
            $membership->send_notifications = $follow;
            $membership->save();
            return;
        }

        $followed = $space->getFollowRecord($user);
        if ($followed) {
            $followed->send_notifications = $follow;
            $followed->save();
            return;
        }

        $space->follow($user, $follow);
    }

    /**
     * Returns all available Notifications
     *
     * @return BaseNotification[]
     * @throws Exception
     */
    public function getNotifications()
    {
        if (!$this->_notifications) {
            $this->_notifications = $this->searchModuleNotifications();
        }
        return $this->_notifications;
    }

    /**
     * Returns all available NotificationCategories as array with category id as
     * key and a category instance as value.
     */
    public function getNotificationCategories($user = null)
    {
        if ($this->_categories) {
            return $this->_categories;
        }

        $result = [];

        foreach ($this->getNotifications() as $notification) {
            $category = $notification->getCategory();
            if ($category && !array_key_exists($category->id, $result) && $category->isVisible($user)) {
                $result[$category->id] = $category;
            }
        }

        $this->_categories = array_values($result);

        usort($this->_categories, function ($a, $b) {
            return $a->sortOrder - $b->sortOrder;
        });

        return $this->_categories;
    }

    /**
     * Searches for all Notifications exported by modules.
     * @return array
     * @throws Exception
     */
    protected function searchModuleNotifications()
    {
        $result = [];
        foreach (Yii::$app->moduleManager->getEnabledModules(['includeCoreModules' => true]) as $module) {
            if ($module instanceof Module && $module->hasNotifications()) {
                $result = array_merge($result, $this->createNotifications($module->getNotifications()));
            }
        }

        $evt = new Event(['result' => $result]);
        Event::trigger($this, static::EVENT_SEARCH_MODULE_NOTIFICATIONS, $evt);

        return $evt->result;
    }

    protected function createNotifications($notificationClasses)
    {
        $result = [];
        foreach ($notificationClasses as $notificationClass) {
            $result[] = Yii::createObject($notificationClass);
        }
        return $result;
    }

    /**
     * Check if notifications are sent from the given Space to the given or current user
     *
     * @param Space $space
     * @param User|null $user
     * @return bool
     * @since 1.15.6
     */
    public function hasSpace(Space $space, User $user = null): bool
    {
        if ($user === null) {
            if (Yii::$app->user->isGuest) {
                return false;
            }
            $user = Yii::$app->user->getIdentity();
        }

        foreach (self::getSpaces($user) as $notificationSpace) {
            if ($space->is($notificationSpace)) {
                return true;
            }
        }

        return false;
    }
}
