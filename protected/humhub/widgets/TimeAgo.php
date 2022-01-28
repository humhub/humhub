<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * Shows a given date & time as automatically updating fuzzy timestamps (e.g. "4 minutes ago" or "about 1 day ago").
 *
 * @author luke
 */
class TimeAgo extends Widget
{

    /**
     * @var string Database (Y-m-d H:i:s) or Unix timestamp
     */
    public $timestamp;

    /**
     * @var int|bool defines if the timeAgo calculation shold only be used within a certain time interval in seconds.
     * (default Yii::$app->params['formatter']['timeAgoBefore'])
     * @since 1.7
     */
    public $timeAgoBefore;

    /**
     * @var int|bool defines if the time information should only be added within a certain time interval in seconds this
     * is only used if the timeAgo calculation is not active. (default Yii::$app->params['formatter']['timeAgoHideTimeAfter'])
     * @since 1.7
     */
    public $hideTimeAfter;

    /**
     * @var bool defines if a static render method should be used (default Yii::$app->params['formatter']['timeAgoStatic'])
     * @since 1.7
     */
    public $staticTimeAgo;

    /**
     * @var string Additional prefixed information (e.g. "Created on") for title tooltip overlay
     * @since 1.9
     */
    public $titlePrefixInfo;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->hideTimeAfter === null) {
            $this->hideTimeAfter = Yii::$app->params['formatter']['timeAgoHideTimeAfter'];
        }

        if ($this->timeAgoBefore === null) {
            $this->timeAgoBefore = Yii::$app->params['formatter']['timeAgoBefore'];
        }

        if ($this->staticTimeAgo === null) {
            $this->staticTimeAgo = Yii::$app->params['formatter']['timeAgoStatic'];
        }

        // Make sure we get an timestamp in server tz
        if (is_numeric($this->timestamp)) {
            $this->timestamp = date('Y-m-d H:i:s', $this->timestamp);
        }
        $this->timestamp = strtotime($this->timestamp);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->isRenderStatic()) {
            return $this->renderStatic();
        }

        $elapsed = time() - $this->timestamp;
        if ($this->isTimeAgoElapsed($elapsed)) {
            return $this->renderDateTime($elapsed);
        }

        return $this->renderTimeAgo();
    }

    /**
     * @return bool
     */
    private function isRenderStatic()
    {
        return $this->staticTimeAgo;
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    private function renderStatic()
    {
        $isoDate = date("c", $this->timestamp);
        return '<time class="tt time timeago" datetime="' . $isoDate . '" title="' . $this->titlePrefixInfo . $this->getFullDateTime() . '">' . Yii::$app->formatter->asRelativeTime($this->timestamp) . '</time>';
    }

    /**
     * @param $elapsed
     * @return bool
     */
    private function isTimeAgoElapsed($elapsed)
    {
        return $this->timeAgoBefore !== false && $elapsed >= $this->timeAgoBefore;
    }

    /**
     * Show full date
     *
     * @param $elapsed
     * @return string output of full date and time
     * @throws InvalidConfigException
     */
    public function renderDateTime($elapsed)
    {
        // Show time when within specified range
        if ($this->isHideTimeAfter($elapsed)) {
            $date = Yii::$app->formatter->asDate($this->timestamp, 'medium');
        } else {
            $date = $this->getFullDateTime();
        }

        $isoDate = date("c", $this->timestamp);
        return '<time class="tt time timeago" datetime="' . $isoDate . '" title="' . $this->titlePrefixInfo . $this->getFullDateTime() . '">' . $date . '</time>';
    }

    /**
     * @param $elapsed
     * @return bool
     */
    private function isHideTimeAfter($elapsed)
    {
        return $this->hideTimeAfter !== false && $elapsed >= $this->hideTimeAfter;
    }


    /**
     * Render TimeAgo Javascript
     *
     * @return string timeago span
     * @throws InvalidConfigException
     */
    public function renderTimeAgo()
    {
        // Convert timestamp to ISO 8601
        $date = date("c", $this->timestamp);
        return '<time class="tt time timeago" data-ui-addition="timeago" datetime="' . $date . '" title="' . $this->titlePrefixInfo . $this->getFullDateTime() . '">' . $this->getFullDateTime() . '</time>';
    }


    /**
     * Returns full date as text
     *
     * @return string
     * @throws InvalidConfigException
     */
    protected function getFullDateTime()
    {
        if (isset(Yii::$app->params['formatter']['timeAgoFullDateCallBack']) && is_callable(Yii::$app->params['formatter']['timeAgoFullDateCallBack'])) {
            return call_user_func(Yii::$app->params['formatter']['timeAgoFullDateCallBack'], $this->timestamp);
        }

        return Yii::$app->formatter->asDate($this->timestamp, 'medium') . ' - ' . Yii::$app->formatter->asTime($this->timestamp, 'short');
    }

}
