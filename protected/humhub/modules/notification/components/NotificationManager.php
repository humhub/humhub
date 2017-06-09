<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\Follow;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\notification\targets\BaseTarget;

/**
 * The NotificationManager component is responsible for sending BaseNotifications to Users over different
 * notification targets by using the send and sendBulk function.
 * 
 * A aotification target may be disabled for a specific user and will be skipped.
 * 
 * @author buddha
 */
class NotificationManager
{

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
    protected $_targets;

    /**
     * Cached array of NotificationCategories
     * @var type 
     */
    protected $_categories;

    /**
     * Sends the given $notification to all enabled targets of the given $users if possible
     * as bulk message.
     * 
     * @param \humhub\modules\notification\components\BaseNotification $notification
     * @param User[] $users
     */
    public function sendBulk(BaseNotification $notification, $users)
    {
        $recepients = $this->filterRecepients($notification, $users);

        foreach ($recepients as $recepient) {
            $notification->saveRecord($recepient);
            foreach ($this->getTargets() as $target) {
                $target->send($notification, $recepient);
            }
        }
    }

    /**
     * Filters out duplicates and the originator of the notification itself.
     * 
     * @param User[] $users
     * @return User[] array of unique user instances
     */
    protected function filterRecepients(BaseNotification $notification, $users)
    {
        $userIds = [];
        $filteredUsers = [];
        foreach ($users as $user) {
            if (!in_array($user->id, $userIds) && !$notification->isOriginator($user)) {
                $filteredUsers[] = $user;
                $userIds[] = $user->id;
            }
        }
        return $filteredUsers;
    }

    /**
     * Sends the given $notification to all enabled targets of a single user.
     * 
     * @param \humhub\modules\notification\components\BaseNotification $notification
     * @param User $user target user
     */
    public function send(BaseNotification $notification, User $user)
    {
        if ($notification->isOriginator($user)) {
            return;
        }

        $notification->saveRecord($user);
        foreach ($this->getTargets($user) as $target) {
            $target->send($notification, $user);
        }
    }

    /**
     * Returns all active targets for the given user.
     * 
     * @param type $user
     * @return type
     */
    public function getTargets(User $user = null)
    {
        if ($this->_targets) {
            return $this->_targets;
        }

        foreach ($this->targets as $target) {
            $instance = Yii::createObject($target);
            if ($instance->isActive($user)) {
                $this->_targets[] = $instance;
            }
        }

        return $this->_targets;
    }

    /**
     * Factory function for receiving a target instance for the given class.
     * 
     * @param type $class
     * @return type
     */
    public function getTarget($class)
    {
        foreach ($this->getTargets() as $target) {
            if ($target->className() == $class) {
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
     * @return type
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
     * @return User[]
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
     * @param boolean $public
     * @return User[]
     */
    public function getContainerFollowers(ContentContainerActiveRecord $container, $public = true)
    {
        $result = [];
        if ($container instanceof Space) {
            $isDefault = $this->isDefaultNotificationSpace($container);
            $members = Membership::getSpaceMembersQuery($container, true, true)->all();
            if ($public) {
                // Add explicit follower and non explicit follower if $isDefault
                $followers = $this->findFollowers($container, $isDefault)->all();
                $result = array_merge($members, $followers);
            } else if ($isDefault) {
                // Add all members without explicit following and no notification settings.
                $followers = Membership::getSpaceMembersQuery($container, true, false)
                                ->andWhere(['not exists', $this->findNotExistingSettingSubQuery()])->all();
                $result = array_merge($members, $followers);
            } else {
                $result = $members;
            }
        } else if ($container instanceof User) {
            // Note the notification follow logic for users is currently not implemented.
            // TODO: perhaps return only friends if public is false?
            $result = (!$public) ? [] : Follow::getFollowersQuery($container, true)->all();
            $result[] = $container;
        }
        return $result;
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
                'and', 'user.status=1', ['not exists', $this->findNotExistingSettingSubQuery()]
            ]);
        }

        return $query;
    }

    private function findNotExistingSettingSubQuery()
    {
        return ContentContainerSetting::find()
                        ->where('contentcontainer_setting.contentcontainer_id=user.contentcontainer_id')
                        ->andWhere(['contentcontainer_setting.module_id' => 'notification'])
                        ->andWhere(['contentcontainer_setting.name' => 'notification.like_email']);
    }

    /**
     * Returns all spaces this user is following (including member spaces) with sent_notification setting.
     * 
     * @param User $user
     * @return Space[]
     */
    public function getSpaces(User $user)
    {
        $isLoaded = (Yii::$app->getModule('notification')->settings->user($user)->get('notification.like_email') !== null);
        if ($isLoaded) {
            $memberSpaces = Membership::getUserSpaceQuery($user, true, true)->all();
            $followSpaces = Follow::getFollowedSpacesQuery($user, true)->all();

            return array_merge($memberSpaces, $followSpaces);
        } else {
            return Space::findAll(['guid' => Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces')]);
        }
    }

    /**
     * Returns all spaces this user is not following.
     * 
     * @param User $user
     * @return type
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

        $spaceIds = array_map(function($space) {
            return $space->id;
        }, $spaces);

        // Update non selected membership spaces
        \humhub\modules\space\models\Membership::updateAll(['send_notifications' => 0], [
            'and',
            ['user_id' => $user->id],
            ['not in', 'space_id', $spaceIds]
        ]);

        // Update non selected following spaces
        \humhub\modules\user\models\Follow::updateAll(['send_notifications' => 0], [
            'and',
            ['user_id' => $user->id],
            ['object_model' => Space::class],
            ['not in', 'object_id', $spaceIds]
        ]);
    }

    /**
     * Defines the enable_html5_desktop_notifications setting for the given user or global if no user is given.
     * 
     * @param type $value
     * @param User $user
     */
    public function setDesktopNoficationSettings($value = 0, User $user = null)
    {
        $module = Yii::$app->getModule('notification');
        $settingManager = ($user) ? $module->settings->user($user) : $module->settings;
        $settingManager->set('enable_html5_desktop_notifications', $value);
    }

    /**
     * Determines the enable_html5_desktop_notifications setting either for the given user or global if no user is given.
     * By default the setting is enabled.
     * @param User $user
     * @return type
     */
    public function getDesktopNoficationSettings(User $user = null)
    {
        if ($user) {
            return Yii::$app->getModule('notification')->settings->user($user)->getInherit('enable_html5_desktop_notifications', 1);
        } else {
            return Yii::$app->getModule('notification')->settings->get('enable_html5_desktop_notifications', 1);
        }
    }

    /**
     * Sets the send_notifications settings for the given space and user.
     * 
     * @param User $user user instance for which this settings will aplly
     * @param Space $space which notifications will be followed / unfollowed
     * @param type $follow the setting value (true by default)
     * @return type
     */
    public function setSpaceSetting(User $user = null, Space $space, $follow = true)
    {
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
     * @return type
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

        usort($this->_categories, function($a, $b) {
            return $a->sortOrder - $b->sortOrder;
        });

        return $this->_categories;
    }

    /**
     * Searches for all Notifications exported by modules.
     * @return type
     */
    protected function searchModuleNotifications()
    {
        $result = [];
        foreach (Yii::$app->moduleManager->getModules(['includeCoreModules' => true]) as $module) {
            if ($module instanceof \humhub\components\Module && $module->hasNotifications()) {
                $result = array_merge($result, $this->createNotifications($module->getNotifications()));
            }
        }
        return $result;
    }

    protected function createNotifications($notificationClasses)
    {
        $result = [];
        foreach ($notificationClasses as $notificationClass) {
            $result[] = Yii::createObject($notificationClass);
        }
        return $result;
    }

}
