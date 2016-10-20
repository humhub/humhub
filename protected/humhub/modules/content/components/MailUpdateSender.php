<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use yii\helpers\Console;
use humhub\modules\user\models\User;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\activity\components\BaseActivity;

/**
 * Sends a mail update to a user
 *
 * @since 1.1
 * @author luke
 */
class MailUpdateSender extends \yii\base\Component
{

    const INTERVAL_DAILY = 1;
    const INTERVAL_HOURY = 2;

    /**
     * @var User
     */
    public $user;

    /**
     * @var int interval (e.g. daily or hourly)
     */
    public $interval;

    /**
     * Processes update e-mails for all users
     */
    public static function processCron($controller)
    {
        // Detect the mailing interval we're in
        if (Yii::$app->controller->action->id == 'hourly') {
            $interval = self::INTERVAL_HOURY;
        } elseif (Yii::$app->controller->action->id == 'daily') {
            $interval = self::INTERVAL_DAILY;
        } else {
            throw new \yii\console\Exception('Invalid mail update interval!');
        }

        // Get users
        $users = User::find()->distinct()->joinWith(['httpSessions', 'profile'])->where(['user.status' => User::STATUS_ENABLED]);
        $totalUsers = $users->count();
        $processed = 0;

        Console::startProgress($processed, $totalUsers, 'Sending update e-mails to users... ', false);


        $mailsSent = 0;
        foreach ($users->each() as $user) {
            $mailSender = new self;
            $mailSender->user = $user;
            $mailSender->interval = $interval;
            if ($mailSender->send()) {
                $mailsSent++;
            }

            Console::updateProgress( ++$processed, $totalUsers);
        }

        Console::endProgress(true);
        $controller->stdout('done - ' . $mailsSent . ' email(s) sent.' . PHP_EOL, Console::FG_GREEN);

        // Switch back to system language
        self::switchLanguage();
    }

    /**
     * Sends mail update to user
     *
     * @return type
     */
    public function send()
    {
        if (!$this->isMailingEnabled()) {
            return false;
        }

        Yii::$app->user->switchIdentity($this->user);
        self::switchLanguage($this->user);

        $notifications = $this->renderNotifications();
        $activities = $this->renderActivities();

        // Check there is content to send
        if ($activities['html'] !== '' || $notifications['html'] !== '') {

            try {
                // TODO: find cleaner solution...
                Yii::$app->view->params['showUnsubscribe'] = true;
                
                $mail = Yii::$app->mailer->compose([
                    'html' => '@humhub/modules/content/views/mails/Update',
                    'text' => '@humhub/modules/content/views/mails/plaintext/Update'
                        ], [
                    'activities' => $activities['html'],
                    'activities_plaintext' => $activities['text'],
                    'notifications' => $notifications['html'],
                    'notifications_plaintext' => $notifications['text'],
                ]);

                $mail->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')]);
                $mail->setTo($this->user->email);
                $mail->setSubject($this->getSubject());
                if ($mail->send()) {
                    return true;
                }
            } catch (\Exception $ex) {
                Yii::error('Could not send mail to: ' . $this->user->email . ' - Error:  ' . $ex->getMessage());
            }
        }

        return false;
    }

    /**
     * Renders notifications mail output
     *
     * @return array
     */
    protected function renderNotifications()
    {
        $notifications = Yii::$app->getModule('notification')->getMailNotifications($this->user, $this->interval);

        $result['html'] = '';
        $result['text'] = '';

        foreach ($notifications as $notification) {
            $result['html'] .= $notification->render(BaseNotification::OUTPUT_MAIL);
            $result['text'] .= $notification->render(BaseNotification::OUTPUT_MAIL_PLAINTEXT);
        }

        return $result;
    }

    /**
     * Renders activity mail output
     *
     * @return array
     */
    protected function renderActivities()
    {
        $activities = Yii::$app->getModule('activity')->getMailActivities($this->user, $this->interval);

        $result['html'] = '';
        $result['text'] = '';

        foreach ($activities as $activity) {
            $result['html'] .= $activity->render(BaseActivity::OUTPUT_MAIL);
            $result['text'] .= $activity->render(BaseActivity::OUTPUT_MAIL_PLAINTEXT);
        }

        return $result;
    }

    /**
     * Switch to current language
     *
     * @param User $user optional user
     */
    protected static function switchLanguage($user = null)
    {
        if ($user !== null && $user->language != "") {
            Yii::$app->language = $user->language;
        } elseif (Yii::$app->settings->get('defaultLanguage') != '') {
            Yii::$app->language = Yii::$app->settings->get('defaultLanguage');
        } else {
            Yii::$app->language = 'en';
        }
    }

    /**
     * Returns mail subject
     */
    protected function getSubject()
    {
        $module = Yii::$app->getModule('content');

        if ($this->interval == self::INTERVAL_HOURY) {
            if ($module->emailSubjectHourlyUpdate !== null) {
                return $module->emailSubjectHourlyUpdate;
            }
            return Yii::t('base', "Latest news");
        } else {
            if ($module->emailSubjectDailyUpdate !== null) {
                return $module->emailSubjectDailyUpdate;
            }
            return Yii::t('base', "Your daily summary");
        }
    }

    /**
     * Checks if user should receive e-mails
     */
    protected function isMailingEnabled()
    {
        if ($this->user->email === "") {
            return false;
        }
        return true;
    }

}
