<?php
namespace humhub\modules\notification\components;

use Yii;

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
    public function getTargets($user = null)
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
