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

    /**
     * Will hold the selected notification settings. Note this will only be filled with selected settings
     * and not with deselected notification settings.
     * 
     * @var []
     */
    public $settings = [];
    
    /**
     * @var string[] Holds the selected spaces for receiving content created notifications.
     */
    public $spaces = [];
    
    /**
     * The user
     * @var type 
     */
    public $user;
    protected $_targets;
    
    public function init()
    {
        // Note the user object has to be provided in the model constructor.
        $this->spaces = Yii::$app->notification->getSpaces($this->user);
    }

    public function rules()
    {
        return [
            [['settings', 'spaces'], 'safe']
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'spaces' => Yii::t('NotificationModule.models_forms_NotificationSettings', 'Receive Notifications for the following spaces:')
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
        
        //$this->saveSpaceSettings();
        Yii::$app->notification->setSpaces($this->spaces, $this->user);

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

}
