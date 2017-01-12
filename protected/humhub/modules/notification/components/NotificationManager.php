<?php
namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\Follow;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * Description of NotificationManager
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
     * Returns all active targets for the given user.
     * 
     * @param type $user
     * @return type
     */
    public function getTargets(User $user = null)
    {
        if($this->_targets) {
            return $this->_targets;
        }
        
        foreach($this->targets as $target) {
            $instance = Yii::createObject($target);
            if($instance->isActive($user)) {
                $this->_targets[] = $instance;
            }
        }
        
        return $this->_targets;
    }
    
    public function getTarget($class)
    {
        foreach($this->getTargets() as $target) {
            if($target->className() == $class) {
                return $target;
            }
        }
    }
    
    public function getContainerFollowers(ContentContainerActiveRecord $space)
    {
        $members = Membership::getSpaceMembersQuery($space, true, true)->all();
        $followers = Follow::getFollowersQuery($space, true)->all();
        return array_merge($members, $followers);
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
        if(!$user) {
            return Yii::$app->getModule('notification')->settings->setSerialized('sendNotificationSpaces', $spaceGuids);  
        }
        
        $spaces = Space::findAll(['guid' => $spaceGuids]);
        
        // Save actual selection.
        foreach($spaces as $space) {
            $membership = $space->getMembership($user->id);
            if($membership) {
                $membership->send_notifications = 1;
                $membership->save();
                continue;
            }
            
            $followed = $space->getFollowedRecord($user);
            if($followed) {
                $followed->send_notifications = 1;
                $followed->save();
                continue;
            }
            
            $space->follow($user, true);
        }
        
        $spaceIds = array_map(function($space) { return $space->id; }, $spaces);
        
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
        if($this->_categories) {
            return $this->_categories;
        }
        
        $result = [];
        
        foreach($this->getNotifications() as $notification) {
            $category = $notification->getCategory();
            if($category && !array_key_exists($category->id, $result) && $category->isVisible($user)) {
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
        foreach($notificationClasses as $notificationClass) {
            $result[] = Yii::createObject($notificationClass);
        }
        return $result;
    }

}
