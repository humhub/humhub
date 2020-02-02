<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\jobs;

use humhub\modules\activity\Module;
use Yii;
use humhub\modules\queue\ActiveJob;
use humhub\modules\activity\components\MailSummaryProcessor;
use humhub\modules\activity\components\MailSummary;

/**
 * SendMailSummary
 *
 * @since 1.2
 * @author Luke
 */
class SendMailSummary extends ActiveJob
{

    /**
     * @var int the interval
     */
    public $interval;

    /**
     * @inheritdoc
     */
    public function run()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('activity');
        if (!$module->enableMailSummaries) {
            return;
        }

        if ($this->interval === MailSummary::INTERVAL_DAILY || $this->interval === MailSummary::INTERVAL_HOURLY || $this->interval === MailSummary::INTERVAL_WEEKLY) {
            MailSummaryProcessor::process($this->interval);
        } else {
            Yii::error('Invalid summary interval given' . $this->interval, 'activity.job');
            return;
        }
    }

}
