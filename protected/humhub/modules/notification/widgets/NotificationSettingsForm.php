<?php
namespace humhub\modules\notification\widgets;

/**
 * Description of NotificationSettingForm
 *
 * @author buddha
 */
class NotificationSettingsForm extends \yii\base\Widget
{
    /**
     * @var \yii\widgets\ActiveForm 
     */
    public $form;
    
    /**
     * @var \humhub\modules\notification\models\forms\NotificationSettings 
     */
    public $model;
    
    /**
     * Used for user notification settings. If null use global settings.
     * @var type 
     */
    public $user;
    
    /**
     * @var type 
     */
    public $showSpaces = true;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('notificationSettingsForm', [
            'form' => $this->form,
            'model' => $this->model,
            'user' => $this->user,
            'showSpaces' => $this->showSpaces
        ]);
    }
}
