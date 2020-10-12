<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;

/**
 * Shows a given date & time as automatically updating fuzzy timestamps (e.g. "4 minutes ago" or "about 1 day ago").
 * 
 * @author luke
 */
class TimeAgo extends \yii\base\Widget
{

    /**
     * @var string Database (Y-m-d H:i:s) or Unix timestamp
     */
    public $timestamp;

    /**
     * @var int|bool defines if the timeAgo calculation shold only be used within a certain time interval in seconds.
     * (default Yii::$app->params['formatter']['timeAgoBefore'])
     */
    public $timeAgoBefore;

    /**
     * @var int|bool defines if the time information should only be added within a certain time interval in seconds this
     * is only used if the timeAgo calculation is not active. (default Yii::$app->params['formatter']['timeAgoHideTimeAfter'])
     */
    public $hideTimeAfter;

    /**
     * @var bool defines if a static render method should be used (default Yii::$app->params['formatter']['timeAgoStatic'])
     */
    public $staticTimeAgo;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

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
        if($this->isRenderStatic()) {
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
        $timeAgoStatic = $this->staticTimeAgo !== null ?  $this->staticTimeAgo : Yii::$app->params['formatter']['timeAgoStatic'];
        return $timeAgoStatic;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function renderStatic()
    {
        return '<span class="time"><span title="tt ' . $this->getFullDateTime() . '">' . Yii::$app->formatter->asRelativeTime($this->timestamp) . '</span></span>';
    }

    /**
     * @param $elapsed
     * @return bool
     */
    private function isTimeAgoElapsed($elapsed)
    {
        $timeAgoBefore = $this->timeAgoBefore !== null ? $this->timeAgoBefore : Yii::$app->params['formatter']['timeAgoBefore'];
        return $timeAgoBefore !== false && $elapsed >= $timeAgoBefore;
    }

    /**
     * @param $elapsed
     * @return bool
     */
    private function isHideTimeAfter($elapsed)
    {
        $timeAgoHideTimeAfter = $this->hideTimeAfter !== null ? $this->hideTimeAfter : Yii::$app->params['formatter']['timeAgoHideTimeAfter'];
        return $timeAgoHideTimeAfter === false || $elapsed >= $timeAgoHideTimeAfter;
    }

    /**
     * Show full date
     *
     * @param int $elasped time in seconds
     * @return string output of full date and time
     * @throws \yii\base\InvalidConfigException
     */
    public function renderDateTime($elapsed)
    {
        // Show time when within specified range
        if (!$this->isHideTimeAfter($elapsed)) {
            $date = $this->getFullDateTime();
        } else {
            $date = Yii::$app->formatter->asDate($this->timestamp, 'medium');
        }

        return '<span class="tt time"><span title="' . $this->getFullDateTime() . '">' . $date . '</span></span>';
    }


    /**
     * Render TimeAgo Javascript
     *
     * @return string timeago span
     * @throws \yii\base\InvalidConfigException
     */
    public function renderTimeAgo()
    {
       // $this->getView()->registerJs('$(".time").timeago();', \yii\web\View::POS_END, 'timeago');

       // return '<span class="tt timeago time" data-ui-addition="timeago" title="' . date("c", $this->timestamp) . '">' . $this->getFullDateTime() . '</span>';


        // Convert timestamp to ISO 8601
        $date =  date("c", $this->timestamp);
        return '<time class="tt time timeago" data-ui-addition="timeago" datetime="'.$date.'" title="' .$this->getFullDateTime() . '">' . $this->getFullDateTime() . '</time>';
    }


    /**
     * Returns full date as text
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFullDateTime()
    {
        if (isset(Yii::$app->params['formatter']['timeAgoFullDateCallBack']) && is_callable(Yii::$app->params['formatter']['timeAgoFullDateCallBack'])) {
            return call_user_func(Yii::$app->params['formatter']['timeAgoFullDateCallBack'], $this->timestamp);
        }

        return Yii::$app->formatter->asDate($this->timestamp, 'medium') . ' - ' . Yii::$app->formatter->asTime($this->timestamp, 'short');
    }

}
