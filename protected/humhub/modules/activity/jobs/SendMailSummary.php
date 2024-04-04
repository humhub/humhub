<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\jobs;

use humhub\modules\activity\components\MailSummary;
use humhub\modules\activity\components\MailSummaryProcessor;
use humhub\modules\activity\Module;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\LongRunningActiveJob;
use Yii;

/**
 * SendMailSummary
 *
 * @since 1.2
 * @author Luke
 */
class SendMailSummary extends LongRunningActiveJob implements ExclusiveJobInterface
{
    /**
     * @var int the interval
     */
    public $interval;

    /**
     * @inhertidoc
     */
    public function getExclusiveJobId()
    {
        return get_class($this) . $this->interval;
    }

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

        if (in_array($this->interval, [
            MailSummary::INTERVAL_DAILY,
            MailSummary::INTERVAL_HOURLY,
            MailSummary::INTERVAL_WEEKLY,
            MailSummary::INTERVAL_MONTHLY,
        ], true)) {
            MailSummaryProcessor::process($this->interval);
        } else {
            Yii::error('Invalid summary interval given' . $this->interval, 'activity.job');
            return;
        }
    }
}
