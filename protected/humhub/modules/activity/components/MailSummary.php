<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\components;

use Yii;
use yii\base\Exception;
use yii\base\Component;
use humhub\modules\dashboard\components\actions\DashboardStream;

/**
 * MailSummary is send to the user with a list of new activities
 *
 * @since 1.2
 * @author Luke
 */
class MailSummary extends Component
{

    /**
     * Intervals
     */
    const INTERVAL_NONE = 0;
    const INTERVAL_DAILY = 1;
    const INTERVAL_HOURY = 2;

    /**
     * @var \humhub\modules\user\models\User the user
     */
    public $user = null;

    /**
     * @var int the interval of this summary
     */
    public $interval;

    /**
     * @var int the maximum number of activities in the e-mail summary
     */
    public $maxActivityCount = 50;

    /**
     * Sends the summary mail to the user
     */
    public function send()
    {
        if ($this->user === null || empty($this->user->email)) {
            return false;
        }

        $outputHtml = '';
        $outputPlaintext = '';

        foreach ($this->getActivities() as $activity) {
            $outputHtml .= $activity->render(BaseActivity::OUTPUT_MAIL);
            $outputPlaintext .= $activity->render(BaseActivity::OUTPUT_MAIL_PLAINTEXT);
        }

        try {
            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/content/views/mails/Update',
                'text' => '@humhub/modules/content/views/mails/plaintext/Update'
                    ], [
                'activities' => $outputHtml,
                'activities_plaintext' => $outputPlaintext,
            ]);

            $mail->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')]);
            $mail->setTo($this->user->email);
            $mail->setSubject($this->getSubject());
            if ($mail->send()) {
                //TODO: Store date of last activity e-mail send
                return true;
            }
        } catch (Exception $ex) {
            Yii::error('Could not send mail to: ' . $this->user->email . ' - Error:  ' . $ex->getMessage());
        }
    }

    /**
     * Returns the subject of the MailSummary
     * 
     * @return string the subject of mail summary
     */
    protected function getSubject()
    {
        if ($this->interval === self::INTERVAL_DAILY) {
            return Yii::t('ActivityModule.base', "Your daily summary");
        } elseif ($this->interval === self::INTERVAL_HOURY) {
            return Yii::t('ActivityModule.base', "Latest news");
        }

        return "";
    }

    /**
     * Returns the list of activities for the e-mail summary
     * 
     * @return \humhub\modules\activity\models\Activity[] the activities
     */
    protected function getActivities()
    {
        $lastMailDate = $this->user->last_activity_email;
        if ($lastMailDate == "" || $lastMailDate == "0000-00-00 00:00:00") {
            $lastMailDate = new \yii\db\Expression('NOW() - INTERVAL 24 HOUR');
        }

        $stream = new DashboardStream('stream', Yii::$app->controller);
        $stream->limit = $this->maxActivityCount;
        $stream->mode = DashboardStream::MODE_ACTIVITY;
        $stream->user = $this->user;
        $stream->init();
        $stream->activeQuery->andWhere(['>', 'content.created_at', $lastMailDate]);

        $activities = [];
        foreach ($stream->activeQuery->all() as $wallEntry) {
            try {
                $activity = $wallEntry->content->getPolymorphicRelation();
                $activities[] = $activity->getActivityBaseClass();
            } catch (Exception $ex) {
                Yii::error($ex->getMessage());
                return [];
            }
        }
    }

}
