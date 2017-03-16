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
    
    /**
     * Spaces which should be added by default to the space chooser result as suggestion
     * @var type 
     */
    private $defaultSpaces = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->defaultSpaces = Yii::$app->notification->getNonNotificationSpaces($this->model->user);

        
        return $this->render('notificationSettingsForm', [
            'form' => $this->form,
            'model' => $this->model,
            'showSpaces' => $this->showSpaces,
            'defaultSpaces' => $this->defaultSpaces
        ]);
    }
}
