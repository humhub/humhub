<?php

namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;
use humhub\components\rendering\Renderer;

/**
 * A NotificationTarget is used to handle new Basenotifications. A NotificationTarget
 * may send nofication information to external services in specific formats or use
 * specific protocols.
 * 
 * @author buddha
 */
abstract class NotificationTarget extends \yii\base\Object
{

    /**
     * Unique target id has to be defined by subclasses.
     * @var string 
     */
    public $id;

    /**
     * Holds the title of this insance.
     * @var type 
     */
    public $title;

    /**
     * Default Renderer for this NotificationTarget
     * @var type 
     */
    public $renderer;

    /**
     * Defines the acknowledge flag in the notification record.
     * If not set, the notification target does not support the acknowledgement of a notification,
     * or provides an custom implemention.
     * 
     * @var string
     * @see NotificationTarget::acknowledge() 
     */
    public $acknowledgeFlag;

    /**
     * Will be used as default enable setting, if there is no user specific setting and no
     * global setting and also no default setting for this target for a given NotificationCategory.
     * @var boolean 
     */
    public $defaultSetting = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->title = $this->getTitle();
    }

    /**
     * @return string Human readable title for views.
     */
    public abstract function getTitle();

    /**
     * @return \humhub\components\rendering\Renderer default renderer for this target.
     */
    public function getRenderer()
    {
        return \yii\di\Instance::ensure($this->renderer, Renderer::class);
    }

    /**
     * Used to handle a BaseNotification for a given $user.
     * 
     * The NotificationTarget can handle the notification for example by pushing a Job to
     * a Queue or directly handling the notification.
     * 
     * @param BaseNotification $notification
     */
    public abstract function handle(BaseNotification $notification, User $user);

    /**
     * Used to acknowledge the seding/processing of the given $notification.
     * 
     * @param BaseNotification $notification notification to be acknowledged
     * @param boolean $state true if successful otherwise false
     */
    public function acknowledge(BaseNotification $notification, $state = true)
    {
        if ($this->acknowledgeFlag && $notification->record->hasAttribute($this->acknowledgeFlag)) {
            $notification->record->setAttribute($this->acknowledgeFlag, $state);
            $notification->record->save();
        }
    }

    /**
     * @return boolean Check if the given $notification has already been processed.
     */
    public function isAcknowledged(BaseNotification $notification)
    {
        if ($this->acknowledgeFlag && $notification->record->hasAttribute($this->acknowledgeFlag)) {
            return $notification->record->getAttribute($this->acknowledgeFlag);
        }
        return false;
    }

    /**
     * Static access to the target id.
     * 
     * @return string
     */
    public static function getId()
    {
        $instance = new static();
        return $instance->id;
    }

    /**
     * Used to process a $notification for the given $user.
     * 
     * By default the $noification will be marked as acknowledged before processing.
     * The processing is triggerd by calling $this->handle. 
     * If the processing fails the acknowledged mark will be set to false.
     * 
     * @param BaseNotification $notification
     */
    public function send(BaseNotification $notification, User $user)
    {
        // Do not send if this target is not enabled or this notification is already acknowledged.
        if(!$this->isEnabled($notification, $user) || $this->isAcknowledged($notification)) {
            return;
        }

        try {
            $this->acknowledge($notification, true);

            if ($this->isEnabled($notification, $user)) {
                $this->handle($notification, $user);
            } else {
                $this->acknowledge($notification, false);
            }
        } catch (\Exception $e) {
            Yii::error($e);
            $this->acknowledge($notification, false);
            if(!YII_ENV_PROD) {
                throw $e;
            }
        }
    }

    /**
     * Used for handling the given $notification for multiple $users.
     * 
     * @param BaseNotification $notification
     * @param type $users
     */
    public function sendBulk(BaseNotification $notification, $users)
    {
        if ($users instanceof \yii\db\ActiveQuery) {
            $users = $users->all();
        }

        foreach ($users as $user) {
            $this->send($notification, $user);
        }
    }

    /**
     * Returns the setting key for this target of the given $category.
     * @param type $category
     * @return type
     */
    public function getSettingKey($category)
    {
        return $category->id . '_' . $this->id;
    }

    /**
     * Some NotificationTargets may need to be activated first or require a certain permission in order to be used.
     * 
     * This function checks if this target is active for the given user.
     * If no user is given this function will determine if the target is globaly active or deactivated.
     * 
     * If a subclass does not overwrite this function it will be activated for all users by default.
     * 
     * @param User $user
     */
    public function isActive(User $user = null)
    {
        return true;
    }

    /**
     * Checks if the given $notification is enabled for this target.
     * If the $notification is not part of a NotificationCategory the $defaultSetting
     * of this NotificationTarget is returned.
     * 
     * If this NotificationTarget is not active for the given $user, this function will return false. 
     * 
     * @param BaseNotification $notification
     * @param User $user
     * @see NotificationTarget::isActive()
     * @see NotificationTarget::isCategoryEnabled()
     * @return boolean
     */
    public function isEnabled(BaseNotification $notification, User $user = null)
    {
        if (!$this->isActive($user)) {
            return false;
        }

        $category = $notification->getCategory();
        return ($category) ? $this->isCategoryEnabled($category, $user) : $this->defaultSetting;
    }
    
    /**
     * Checks if the settings for this target are editable.
     * @return boolean
     */
    public function isEditable(user $user = null)
    {
        return true;
    }

    /**
     * Returns the enabled setting of this target for the given $category.
     * 
     * @param NotificationCategory $category
     * @param User $user
     * @return boolean
     */
    public function isCategoryEnabled(NotificationCategory $category, User $user = null)
    {        
        if(!$category->isVisible($user)) {
            return false;
        }
        
        if($category->isFixedSetting($this)) {
            return $category->getDefaultSetting($this);
        }

        $settingKey = $this->getSettingKey($category);
        
        if ($user) {
            $enabled = Yii::$app->getModule('notification')->settings->user($user)->getInherit($settingKey, $category->getDefaultSetting($this));
        } else {
            $enabled = Yii::$app->getModule('notification')->settings->get($settingKey, $category->getDefaultSetting($this));
        }

        return ($enabled === null) ? $this->defaultSetting : boolval($enabled);
    }

}
