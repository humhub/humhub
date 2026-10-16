<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\components\InstallationState;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * Sends notifications by e-mail: one mail per {@see DeliveryBatch}, rendered by
 * `@notification/views/mails/notification` and its plaintext twin.
 *
 * The activity summary mail ({@see \humhub\modules\activity\components\MailSummary}) only
 * mentions the number of unread notifications.
 *
 * @since 1.2, rewritten in 1.20
 */
final class MailTarget extends BaseTarget
{
    public const ID = 'email';

    /**
     * @inheritdoc
     */
    public string $id = self::ID;

    /**
     * @inheritdoc
     *
     * The first mail waits a minute: right before sending, notifications the user has seen in the
     * meantime are dropped, so a user on the site usually gets no mail for what they already saw.
     */
    public array $delays = [60, 300, 900, 1800];

    /**
     * @inheritdoc
     */
    public int $highPriorityDelay = 60;

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return Yii::t('NotificationModule.targets', 'E-Mail');
    }

    /**
     * Composes the mail in the recipient's language; a recipient without an e-mail address gets nothing.
     *
     * @inheritdoc
     */
    public function deliver(DeliveryBatch $batch): void
    {
        $user = $batch->recipient;
        if (empty($user->email)) {
            return;
        }

        Yii::$app->i18n->setUserLocale($user);
        try {
            Yii::$app->view->params['showUnsubscribe'] = true;
            Yii::$app->view->params['unsubscribeUrl'] = Url::to(['/notification/user'], true);

            $mail = Yii::$app->mailer->compose(
                [
                    'html' => '@notification/views/mails/notification',
                    'text' => '@notification/views/mails/plaintext/notification',
                ],
                ['batch' => $batch],
            )
                ->setTo($user->email)
                ->setSubject($batch->getSubject());

            if ($replyTo = Yii::$app->settings->get('mailerSystemEmailReplyTo')) {
                $mail->setReplyTo($replyTo);
            }

            if (!$mail->send()) {
                Yii::warning('Notification mail to ' . $user->email . ' (' . $batch->getSubject() . ') was not sent', 'notification');
            }
        } finally {
            unset(Yii::$app->view->params['showUnsubscribe'], Yii::$app->view->params['unsubscribeUrl']);
            Yii::$app->i18n->autosetLocale();
        }
    }

    /**
     * No mails before the installation is finished, e.g. for the example content.
     *
     * @inheritdoc
     */
    public function isActive(?User $user = null): bool
    {
        return parent::isActive($user) && Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED);
    }
}
