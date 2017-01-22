<?php

namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\Follow;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;

/**
 * The NotificationManager component is responsible for sending BaseNotifications to Users over different
 * NotificationTargets by using the send and sendBulk function.
 * 
 * A NotificationTarget may be disabled for a specific user and will be skipped.
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
     * @var NotificationTarget[] Cached target instances.
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
        foreach ($this->getTargets() as $target) {
            $target->sendBulk($notification, $recepients);
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
        if ($container instanceof Space) {
            $members = Membership::getSpaceMembersQuery($container, true, true)->all();
            $followers = (!$public) ? [] : Follow::getFollowersQuery($container, true)->all();
            return array_merge($members, $followers);
        } else if ($container instanceof User) {
            // Note the notification follow logic for users is currently not implemented.
            // TODO: perhaps return only friends if public is false?
            return (!$public) ? [] : Follow::getFollowersQuery($container, true)->all();
        }
    }

    /**
     * Returns all spaces this user is following (including member spaces) with sent_notification setting.
     * 
     * @param User $user
     * @return type
     */
    public function getSpaces(User $user)
    {
        $memberSpaces = Membership::getUserSpaceQuery($user, true, true)->all();
        $followSpaces = Follow::getFollowedSpacesQuery($user, true)->all();

        return array_merge($memberSpaces, $followSpaces);
    }

    /**
     * Returns all spaces this user is following (including member spaces) without sent_notification setting.
     * 
     * @param User $user
     * @return type
     */
    public function getNonNotificationSpaces(User $user)
    {
        $memberSpaces = Membership::getUserSpaceQuery($user, true, false)->all();
        $followSpaces = Follow::getFollowedSpacesQuery($user, false)->all();

        return array_merge($memberSpaces, $followSpaces);
    }

    /**
     * Sets the notification space settings for this user (or global if no user is given).
     * 
     * @param User $user
     * @param string[] $spaces array of space guids
     */
    public function setSpaces(User $user = null, $spaceGuids)
    {
        if (!$user) {
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

    public function setSpaceSetting(User $user = null, Space $space, $follow = true)
    {
        $membership = $space->getMembership($user->id);
        if ($membership) {
            $membership->send_notifications = $follow;
            $membership->save();
            return;
        }

        $followed = $space->getFollowedRecord($user);
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
