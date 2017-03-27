<?php

namespace humhub\modules\notification\models\forms;

use Yii;
use humhub\modules\notification\targets\BaseTarget;

/**
 * Description of NotificationSettings
 *
 * @author buddha
 */
class NotificationSettings extends \yii\base\Model
{

    /**
     * Will hold the selected notification settings. Note this will only be filled with selected settings
     * and not with deselected notification settings.
     * 
     * @var array
     */
    public $settings = [];

    /**
     * @var string[] Holds the selected spaces for receiving content created notifications.
     */
    public $spaceGuids = [];

    /**
     * @var \humhub\modules\user\models\User instance for which the settings should by appleid, if null global settings are used.
     */
    public $user;

    /**
     * @var boolean manage if the user/users should receive desktop notifications. 
     */
    public $desktopNotifications;

    /**
     * @var BaseTarget[] 
     */
    protected $_targets;

    /**
     * @inerhitdoc
     */
    public function init()
    {
        if ($this->user) {
            // Note the user object has to be provided in the model constructor.
            $spaces = Yii::$app->notification->getSpaces($this->user);
            $this->spaceGuids = array_map(function ($space) {
                return $space->guid;
            }, $spaces);
        } else {
            $this->spaceGuids = Yii::$app->getModule('notification')->settings->getSerialized('sendNotificationSpaces');
        }

        $this->desktopNotifications = Yii::$app->notification->getDesktopNoficationSettings($this->user);

        $module = Yii::$app->getModule('notification');
        return ($this->user) ? $module->settings->user($this->user) : $module->settings;
    }

    /**
     * @inerhitdoc
     */
    public function rules()
    {
        return [
            ['desktopNotifications', 'integer'],
            [['settings', 'spaceGuids'], 'safe']
        ];
    }

    /**
     * @inerhitdoc
     */
    public function attributeLabels()
    {
        if ($this->user) {
            $desktopNotificationLabel = Yii::t('NotificationModule.models_forms_NotificationSettings', 'Receive desktop notifications when you are online.');
        } else {
            $desktopNotificationLabel = Yii::t('NotificationModule.models_forms_NotificationSettings', 'Allow desktop notifications by default.');
        }
        return [
            'spaceGuids' => Yii::t('NotificationModule.models_forms_NotificationSettings', 'Receive \'New Content\' Notifications for the following spaces'),
            'desktopNotifications' => $desktopNotificationLabel
        ];
    }

    /**
     * Checks if this form has already been saved before.
     * @return boolean
     */
    public function isUserSettingLoaded()
    {
        if ($this->user) {
            return $this->getSettings()->get('notification.like_email') !== null;
        }
        return false;
    }

    /**
     * @return BaseTarget[] the notification targets enabled for this user (or global)
     */
    public function targets()
    {
        if (!$this->_targets) {
            $this->_targets = Yii::$app->notification->getTargets($this->user);
        }

        return $this->_targets;
    }

    /**
     * @return NotificationCategory[] NotificationCategories enabled for this user (or global)
     */
    public function categories()
    {
        return Yii::$app->notification->getNotificationCategories($this->user);
    }

    /**
     * Returns the field name for the given category/target combination.
     * 
     * @param type $category 
     * @param type $target
     * @return type
     */
    public function getSettingFormname($category, $target)
    {
        return $this->formName() . "[settings][" . $target->getSettingKey($category) . "]";
    }

    /**
     * Saves the settings for the given user (or global if no user is given).
     * 
     * @return boolean if the save process was successful else false
     * @throws \yii\web\HttpException
     */
    public function save()
    {
        if (!$this->checkPermission()) {
            throw new \yii\web\HttpException(403);
        }

        if (!$this->validate()) {
            return false;
        }

        $this->saveSpaceSettings();
        Yii::$app->notification->setDesktopNoficationSettings($this->desktopNotifications, $this->user);
        Yii::$app->notification->setSpaces($this->spaceGuids, $this->user);

        $settings = $this->getSettings();

        // Save all active settings
        foreach ($this->settings as $settingKey => $value) {
            $settings->set($settingKey, $value);
        }

        // Save all inactive settings
        foreach ($this->targets() as $target) {
            if (!$target->isEditable($this->user)) {
                continue;
            }

            foreach ($this->categories() as $category) {
                if ($category->isFixedSetting($target)) {
                    continue;
                }

                $settingKey = $target->getSettingKey($category);
                if (!array_key_exists($settingKey, $this->settings)) {
                    $settings->set($settingKey, false);
                }
            }
        }

        return true;
    }

    /**
     * Saves the Notificaton Space settings for the given user.
     * This is skipped if no user is selected (global settings).
     * 
     * If the user is already a member of this space this function activates the sending of notifications for
     * his membership.
     * 
     * If the user is already following the space this function activates the sendinf of notification for his follow record.
     * 
     * Otherwise a new follow record is created.
     * 
     * @return type
     */
    private function saveSpaceSettings()
    {
        // There is no global space setting right now.
        if (!$this->user) {
            return;
        }

        Yii::$app->notification->setSpaces($this->spaceGuids, $this->user);
    }

    public function getSettings()
    {
        $module = Yii::$app->getModule('notification');
        return ($this->user) ? $module->settings->user($this->user) : $module->settings;
    }

    public function checkPermission()
    {
        if (Yii::$app->user->can(new \humhub\modules\admin\permissions\ManageSettings())) {
            return true;
        } else if (!$this->user) {
            return false; // Only ManageSettings user can set global notification settings
        } else {
            return Yii::$app->user->id == $this->user->id;
        }
    }

    public function resetUserSettings()
    {
        if (!$this->user) {
            return false;
        }

        $settings = $this->getSettings();
        foreach ($this->targets() as $target) {
            foreach ($this->categories() as $category) {
                $settings->delete($target->getSettingKey($category));
            }
        }
        Yii::$app->notification->setSpaces([], $this->user);
        return true;
    }

}
