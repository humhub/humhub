<?php


namespace humhub\components\bootstrap;

use humhub\modules\admin\models\forms\MailingSettingsForm;
use yii\base\BootstrapInterface;


class SettingsLoader implements BootstrapInterface
{

    public function bootstrap($app)
    {
        $this->setMailerConfig($app);
    }

    public function setMailerConfig($app): void
    {
        $transportType = $app->settings->get('mailer.transportType', MailingSettingsForm::TRANSPORT_PHP);

        if ($transportType === MailingSettingsForm::TRANSPORT_FILE) {
            $app->mailer->setTransport(['dsn' => 'native://default']);
            $app->mailer->useFileTransport = true;
        } elseif($transportType === MailingSettingsForm::TRANSPORT_CONFIG) {
            $app->set('mailer', false);
        } else {
            $transport = [];
            $app->mailer->useFileTransport = false;

            if ($transportType === MailingSettingsForm::TRANSPORT_SMTP) {
                if ($app->settings->get('mailer.hostname')) {
                    $transport['host'] = $app->settings->get('mailer.hostname');
                }
                if ($app->settings->get('mailer.port')) {
                    $transport['port'] = (int)$app->settings->get('mailer.port');
                } else {
                    $transport['port'] = 25;
                }
                if ($app->settings->get('mailer.username')) {
                    $transport['username'] = $app->settings->get('mailer.username');
                }
                if ($app->settings->get('mailer.password')) {
                    $transport['password'] = $app->settings->get('mailer.password');
                }
                $transport['scheme'] = (empty($app->settings->get('mailer.useSmtps'))) ? 'smtp' : 'smtps';

            } elseif ($transportType === MailingSettingsForm::TRANSPORT_PHP) {
                $transport['dsn'] = 'native://default';
            } elseif ($transportType === MailingSettingsForm::TRANSPORT_DSN) {
                $transport['dsn'] = $app->settings->get('mailer.dsn');
            }
            $app->mailer->setTransport($transport);
        }
    }
}
