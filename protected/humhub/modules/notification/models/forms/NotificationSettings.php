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
    
    protected $_targets;

    public function rules()
    {
        return [
            ['settings', 'safe']
        ];
    }

    public function targets($user = null)
    {
        if(!$this->_targets) {
            $this->_targets = Yii::$app->notification->getTargets($user);
        }
        
        return $this->_targets;
    }

    public function categories($user = null)
    {
        return Yii::$app->notification->getNotificationCategories($user);
    }
    
    public function getSettingFormname($category, $target)
    {
        return $this->formName()."[settings][".$target->getSettingKey($category)."]";
    }

    public function save($user = null)
    {
        $module = Yii::$app->getModule('notification');
        $settingManager = ($user) ? $module->settings->user($user) : $module->settings;
        foreach ($this->settings as $settingKey => $value) {
            $settingManager->set($settingKey, $value);
        }
    }

}
