<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\models\forms;

use humhub\components\Module;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;
use yii\web\HttpException;

/**
 * Description of NotificationSettings
 *
 * @author buddha
 */
class NotificationSettings extends Model
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
     * @var User instance for which the settings should by appleid, if null global settings are used.
     */
    public $user;

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

        $module = Yii::$app->getModule('notification');

        return ($this->user) ? $module->settings->user($this->user) : $module->settings;
    }

    /**
     * @inerhitdoc
     */
    public function rules()
    {
        return [
            [['settings', 'spaceGuids'], 'safe'],
        ];
    }

    /**
     * @inerhitdoc
     */
    public function attributeLabels()
    {
        return [
            'spaceGuids' => Yii::t('NotificationModule.base', 'Receive \'New Content\' Notifications for the following spaces'),
        ];
    }

    /**
     * Checks if this form has already been saved before.
     * @throws \Throwable
     */
    public function isTouchedSettings(): bool
    {
        if ($this->user) {
            return NotificationManager::isTouchedSettings($this->user);
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
     * @param NotificationCategory $category
     * @param BaseTarget $target
     * @return string
     */
    public function getSettingFormname($category, $target)
    {
        return $this->formName() . "[settings][" . $target->getSettingKey($category) . "]";
    }

    /**
     * Saves the settings for the given user (or global if no user is given).
     *
     * @return bool if the save process was successful else false
     * @throws HttpException
     */
    public function save()
    {
        if (!$this->checkPermission()) {
            throw new HttpException(403);
        }

        if (!$this->validate()) {
            return false;
        }

        $this->saveSpaceSettings();
        Yii::$app->notification->setSpaces($this->spaceGuids, $this->user);

        $settings = $this->getSettings();

        if ($this->user) {
            $settings->set(NotificationManager::IS_TOUCHED_SETTINGS, true);
        }

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
        /** @var Module $module */
        $module = Yii::$app->getModule('notification');

        return ($this->user) ? $module->settings->user($this->user) : $module->settings;
    }

    public function checkPermission()
    {
        if (Yii::$app->user->can(new ManageSettings())) {
            return true;
        } elseif (!$this->user) {
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
        $settings?->delete(NotificationManager::IS_TOUCHED_SETTINGS);
        foreach ($this->targets() as $target) {
            foreach ($this->categories() as $category) {
                $settings->delete($target->getSettingKey($category));
            }
        }
        Yii::$app->notification->setSpaces([], $this->user);

        return true;
    }

    /**
     * @return bool
     */
    public function canResetAllUsers()
    {
        return !isset($this->user) && Yii::$app->user->can(ManageUsers::class);
    }

    /**
     * Resets all settings stored for all current user
     * @throws \Throwable
     */
    public function resetAllUserSettings()
    {
        $notificationSettings = [NotificationManager::IS_TOUCHED_SETTINGS];
        foreach ($this->targets() as $target) {
            foreach ($this->categories() as $category) {
                $notificationSettings[] = $target->getSettingKey($category);
            }
        }

        ContentContainerSetting::deleteAll(['AND',
            ['module_id' => 'notification'],
            ['IN', 'name', $notificationSettings],
        ]);

        Yii::$app->notification->resetSpaces();

        /** @var Module $module */
        $module = Yii::$app->getModule('notification');
        $settingsManager = $module->settings->user();
        $settingsManager?->reload();
    }
}
