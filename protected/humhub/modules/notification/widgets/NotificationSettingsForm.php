<?php
namespace humhub\modules\notification\widgets;

use Yii;

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
     * @var type 
     */
    public $showSpaces = true;
    
    private $defaultSpaces = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        if($this->model->user) {
            $this->defaultSpaces = Yii::$app->notification->getNonNotificationSpaces($this->model->user);
        }
        
        return $this->render('notificationSettingsForm', [
            'form' => $this->form,
            'model' => $this->model,
            'showSpaces' => $this->showSpaces,
            'defaultSpaces' => $this->defaultSpaces
        ]);
    }
}
