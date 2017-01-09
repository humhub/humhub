<?php

namespace humhub\modules\notification\models\forms;

use Yii;

/**
 * Description of NotificationSettings
 *
 * @author buddha
 */
class NotificationSettings extends \yii\base\Model
{

    public $settings = [];
    public $user;
    protected $_targets;

    public function rules()
    {
        return [
            ['settings', 'safe']
        ];
    }

    public function targets()
    {
        if (!$this->_targets) {
            $this->_targets = Yii::$app->notification->getTargets($this->user);
        }

        return $this->_targets;
    }

    public function categories()
    {
        return Yii::$app->notification->getNotificationCategories($this->user);
    }

    public function getSettingFormname($category, $target)
    {
        return $this->formName() . "[settings][" . $target->getSettingKey($category) . "]";
    }

    public function save()
    {
        if (!$this->checkPermission()) {
            throw new \yii\web\HttpException(403);
        }

        if (!$this->validate()) {
            return false;
        }

        $module = Yii::$app->getModule('notification');
        $settingManager = ($this->user) ? $module->settings->user($this->user) : $module->settings;
        
        // Save all active settings
        foreach ($this->settings as $settingKey => $value) {
            $settingManager->set($settingKey, $value);
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
                    $settingManager->set($settingKey, false);
                }
            }
        }

        return true;
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

}
