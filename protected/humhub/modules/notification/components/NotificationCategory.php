<?php

namespace humhub\modules\notification\components;

use humhub\modules\user\models\User;
use humhub\modules\notification\components\NotificationTarget;

/**
 * NotificationCategories are used to group different notifications in views and
 * configure the notifications in the notification settings.
 * 
 */
abstract class NotificationCategory extends \yii\base\Object
{

    /**
     * Category Id
     * @var type 
     */
    public $id;
    
    /**
     * Used to sort items in the frontend.
     * @var type 
     */
    public $sortOrder = PHP_INT_MAX;
    
    public function init()
    {
        parent::init();
        if(!$this->id) {
            throw new \yii\base\InvalidConfigException('NotificationCategories have to define an id property, which is not the case for "'.self::class.'"');
        }
    }

    /**
     * Returns a human readable title of this  category
     */
    public abstract function getTitle();

    /**
     * Returns a group description
     */
    public abstract function getDescription();

    /**
     * Returns the default enabled settings for the given $target.
     * In case the $target is unknown, subclasses can either return $target->defaultSetting
     * or another default value.
     * 
     * @param NotificationTarget $target
     * @return boolean
     */
    public function getDefaultSetting(NotificationTarget $target)
    {
        if ($target->id === \humhub\modules\notification\components\MailNotificationTarget::getId()) {
            return true;
        } else if ($target->id === \humhub\modules\notification\components\WebNotificationTarget::getId()) {
            return true;
        } else if ($target->id === \humhub\modules\notification\components\MobileNotificationTarget::getId()) {
            return false;
        }

        return $target->defaultSetting;
    }
    
    /**
     * Returns an array of target ids, which are not editable.
     * 
     * @param NotificationTarget $target
     */
    public function getFixedSettings()
    {
        return [];
    }
    
    /**
     * Checks if the given NotificationTarget is fixed for this category.
     * 
     * @param type $target
     * @return type
     */
    public function isFixedSettings(NotificationTarget $target)
    {
        return in_array($target->id, $this->getFixedSettings());
    }
    
    /**
     * Determines if this category is visible for the given $user.
     * This can be used if a category is only visible for users with certian permissions.
     * 
     * Note if no user is given this function should return true in most cases, otherwise this
     * category won't be visible in the global notification settings.
     * 
     * @param User $user
     * @return boolean
     */
    public function isVisible(User $user = null)
    {
        return true;
    }
}
