<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use Yii;
use yii\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\user\models\User;

/**
 *
 * @author buddha
 */
class MailTarget extends BaseTarget
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
        return Yii::t('NotificationModule.targets', 'E-Mail');
    }

    /**
     * @inheritdoc
     */
    public function handle(BaseNotification $notification, User $recipient)
    {
        Yii::$app->i18n->setUserLocale($recipient);

        Yii::$app->view->params['showUnsubscribe'] = true;
        Yii::$app->view->params['unsubscribeUrl'] = \yii\helpers\Url::to(['/notification/user'], true);

        // Note: the renderer is configured in common.php by default its an instance of MailTarget
        $renderer = $this->getRenderer();

        $viewParams = \yii\helpers\ArrayHelper::merge([
                    'headline' => '',
                    'notification' => $notification,
                    'space' => $notification->getSpace(),
                    'content' => $renderer->render($notification),
                    'content_plaintext' => $renderer->renderText($notification)
                        ], $notification->getViewParams());


        $from = $notification->originator ? Html::encode($notification->originator->displayName) . ' (' . Html::encode(Yii::$app->name) . ')' : Yii::$app->settings->get('mailer.systemEmailName');

        Yii::$app->mailer->compose($this->view, $viewParams)
                ->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => $from])
                ->setTo($recipient->email)
                ->setSubject($notification->getMailSubject())->send();

        Yii::$app->i18n->autosetLocale();
    }

    /**
     * @inheritdoc
     */
    public function isActive(User $user = null)
    {
        // Do not send mail notifications for example content during installlation.
        return Yii::$app->params['installed'];
    }

}
