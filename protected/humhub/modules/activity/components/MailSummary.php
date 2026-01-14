<?php

namespace humhub\modules\activity\components;

use humhub\modules\activity\models\Activity;
use humhub\modules\activity\Module;
use humhub\modules\activity\services\RenderService;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\base\Component;
use yii\db\Expression;
use yii\helpers\Url;

class MailSummary extends Component
{
    /**
     * Intervals
     */
    public const INTERVAL_NONE = 0;
    public const INTERVAL_HOURLY = 1;
    public const INTERVAL_DAILY = 2;
    public const INTERVAL_WEEKLY = 3;
    public const INTERVAL_MONTHLY = 4;

    /**
     * @var User the user
     */
    public $user;

    /**
     * @var int the interval of this summary
     */
    public $interval;

    /**
     * @var int the maximum number of activities in the e-mail summary
     */
    public $maxActivityCount = 50;

    /**
     * @var string the mail summary layout file for html mails
     */
    public $layout = '@activity/views/mails/mailSummary';

    /**
     * @var string the mail summary layout file for plaintext mails
     */
    public $layoutPlaintext = '@activity/views/mails/plaintext/mailSummary';

    public ?string $lastSummaryDate = null;

    /**
     * Sends the summary mail to the user
     */
    public function send()
    {
        if ($this->user === null || empty($this->user->email)) {
            return false;
        }

        Yii::$app->i18n->setUserLocale($this->user);

        $outputHtml = '';
        $outputPlaintext = '';

        foreach ($this->getActivities() as $record) {
            $renderService = new RenderService($record);

            $outputHtml .= $renderService->getMail();
            $outputPlaintext .= $renderService->getPlaintext();
        }

        if (empty($outputHtml)) {
            return false;
        }

        try {
            Yii::$app->view->params['showUnsubscribe'] = true;
            Yii::$app->view->params['unsubscribeUrl'] = Url::to(['/activity/user'], true);
            $mail = Yii::$app->mailer->compose([
                'html' => $this->layout,
                'text' => $this->layoutPlaintext,
            ], [
                'activities' => $outputHtml,
                'activitiesPlaintext' => $outputPlaintext,
            ]);

            $mail->setTo($this->user->email);
            $mail->setSubject($this->getSubject());
            if ($mail->send()) {
                $this->setLastSummaryDate();
                Yii::$app->i18n->autosetLocale();
                return true;
            }
        } catch (Throwable $ex) {
            Yii::error('Could not send mail to: ' . $this->user->email . ' - Error:  ' . $ex->getMessage());
        } finally {
            Yii::$app->i18n->autosetLocale();
        }

        return false;
    }

    private function getSubject()
    {
        return match ($this->interval) {
            self::INTERVAL_DAILY => Yii::t('ActivityModule.base', 'Your daily summary'),
            self::INTERVAL_WEEKLY => Yii::t('ActivityModule.base', 'Your weekly summary'),
            self::INTERVAL_MONTHLY => Yii::t('ActivityModule.base', 'Your monthly summary'),
            default => Yii::t('ActivityModule.base', 'Latest news'),
        };
    }

    /**
     * @return Activity[]
     */
    public function getActivities()
    {
        $query = Activity::find();
        $query->defaultScopes($this->user);
        $query->andWhere(['>', 'activity.created_at', $this->getLastSummaryDate()]);
        $query->mailLimitContentContainer($this->user);
        $query->mailLimitTypes($this->user);

        return $query->all();
    }

    private function setLastSummaryDate(): void
    {
        static::getModule()->settings->user($this->user)->set('mailSummaryLast', time());
    }

    /**
     * @return string|Expression of the last summary mail
     */
    private function getLastSummaryDate()
    {
        if ($this->lastSummaryDate !== null) {
            return $this->lastSummaryDate;
        }

        $lastSent = (int)static::getModule()->settings->user($this->user)->get('mailSummaryLast');
        if (empty($lastSent)) {
            $hours = match ($this->interval) {
                static::INTERVAL_DAILY => 24,
                static::INTERVAL_WEEKLY => 7 * 24,
                static::INTERVAL_MONTHLY => 30 * 24,
                static::INTERVAL_HOURLY => 1,
                default => 1,
            };

            $lastSent = new Expression('NOW() - INTERVAL ' . $hours . ' HOUR');
        } else {
            $lastSent = date('Y-m-d G:i:s', $lastSent);
        }

        return $lastSent;
    }

    private static function getModule(): Module
    {
        return Yii::$app->getModule('activity');
    }
}
