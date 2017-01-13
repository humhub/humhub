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
    const INTERVAL_HOURY = 1;
    const INTERVAL_DAILY = 2;

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
     * @var string the mail summary layout file for html mails
     */
    public $mailSummaryLayout = '@activity/views/mails/mailSummary';

    /**
     * @var string the mail summary layout file for plaintext mails
     */
    public $mailSummaryLayoutPlaintext = '@activity/views/mails/plaintext/mailSummary';

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

        $mailRenderer = new MailRenderer();
        foreach ($this->getActivities() as $activity) {
            $outputHtml .= $mailRenderer->render($activity);
            $outputPlaintext .= $mailRenderer->renderText($activity);
        }

        if (empty($outputHtml)) {
            return false;
        }

        try {
            $mail = Yii::$app->mailer->compose(['html' => $this->layout, 'text' => $this->layoutPlaintext], [
                'activities' => $outputHtml,
                'activitiesPlaintext' => $outputPlaintext,
            ]);

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

#        $lastMailDate = $this->user->last_activity_email;
        $lastMailDate = "";
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
        foreach ($stream->activeQuery->all() as $content) {
            try {
                $activity = $content->getPolymorphicRelation();
                if ($activity instanceof \humhub\modules\activity\models\Activity) {
                    /**
                     * @var $activity \humhub\modules\activity\models\Activity
                     */
                    $activities[] = $activity->getActivityBaseClass();
                }
            } catch (Exception $ex) {
                Yii::error($ex->getMessage());
                return [];
            }
        }

        return $activities;
    }

}
