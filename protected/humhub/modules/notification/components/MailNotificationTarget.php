<?php

namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;
use yii\helpers\Html;

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

    /**
     * @var array Notification mail layout. 
     */
    public $view = [
        'html' => '@notification/views/mails/wrapper',
        'text' => '@notification/views/mails/plaintext/wrapper'
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
    public function handle(BaseNotification $notification, User $recipient)
    {
        Yii::$app->i18n->setUserLocale($recipient);

        Yii::$app->view->params['showUnsubscribe'] = true;
        Yii::$app->view->params['unsubscribeUrl'] = \yii\helpers\Url::to(['/notification/user'], true);

        // Note: the renderer is configured in common.php by default its an instance of MailNotificationTarget
        $renderer = $this->getRenderer();

        $viewParams = \yii\helpers\ArrayHelper::merge([
                    'headline' => $notification->getHeadline($recipient),
                    'notification' => $notification,
                    'space' => $notification->getSpace(),
                    'content' => $renderer->render($notification),
                    'content_plaintext' => $renderer->renderText($notification)
                        ], $notification->getViewParams());


        $from = $notification->originator ? Html::encode($notification->originator->displayName) . ' (' . Html::encode(Yii::$app->name) . ')' : Yii::$app->settings->get('mailer.systemEmailName');

        Yii::$app->mailer->compose($this->view, $viewParams)
                ->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => $from])
                ->setTo($recipient->email)
                ->setSubject($notification->getTitle($recipient))->send();

        Yii::$app->i18n->autosetLocale();
    }
    
    /**
     * @inheritdoc
     */
    public function isActive(User $user = null)
    {
        return Yii::$app->params['installed'];
    }
}
