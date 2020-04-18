<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\jobs;

use humhub\modules\activity\Module;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use Yii;
use humhub\modules\queue\ActiveJob;
use humhub\modules\activity\components\MailSummaryProcessor;
use humhub\modules\activity\components\MailSummary;
use yii\queue\RetryableJobInterface;

/**
 * SendMailSummary
 *
 * @since 1.2
 * @author Luke
 */
class SendMailSummary extends ActiveJob implements ExclusiveJobInterface, RetryableJobInterface
{

    /**
     * @var int the interval
     */
    public $interval;


    /**
     * @var int maximum 1 hour
     */
    private $maxExecutionTime = 60 * 60 * 1;

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

        if ($this->interval === MailSummary::INTERVAL_DAILY || $this->interval === MailSummary::INTERVAL_HOURLY || $this->interval === MailSummary::INTERVAL_WEEKLY) {
            MailSummaryProcessor::process($this->interval);
        } else {
            Yii::error('Invalid summary interval given' . $this->interval, 'activity.job');
            return;
        }
    }

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return $this->maxExecutionTime;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error)
    {
        return false;
    }

}
