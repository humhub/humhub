<?php

namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;

/**
 *
 * @author buddha
 */
class MailNotificationTarget extends NotificationTarget
{

    /**
     * @inheritdoc
     */
    public $id = 'email';
    
    /**
     * Enable this target by default.
     * @var type 
     */
    public $defaultSetting = true;
    
    public $view = [
        'html' => '@humhub/modules/content/views/mails/Update',
        'text' => '@humhub/modules/content/views/mails/plaintext/Update'
    ];

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('NotificationModule.components_WebNotificationTarget', 'E-Mail');
    }

    /**
     * @inheritdoc
     */
    public function handle(BaseNotification $notification, User $user)
    {
        try {
            // TODO: find cleaner solution...
            Yii::$app->view->params['showUnsubscribe'] = true;

            $viewParams = [
                'notifications' => $notification->render($this),
                'notifications_plaintext' => $this->getText($notification)
            ];

            return Yii::$app->mailer->compose($this->view, $viewParams)
                            ->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')])
                            ->setTo($user->email)
                            ->setSubject($notification->getTitle())->send();
        } catch (\Exception $ex) {
            Yii::error('Could not send mail to: ' . $user->email . ' - Error:  ' . $ex->getMessage());
            if(YII_DEBUG) {
                throw $ex;
            }
        }
    }

    public function getText(BaseNotification $notification)
    {
        $textRenderer = $this->getRenderer();

        if (!method_exists($textRenderer, 'renderText')) {
            $textRenderer = Yii::createObject(MailTargetRenderer::class);
        }

        return $textRenderer->renderText($notification);
    }

}
