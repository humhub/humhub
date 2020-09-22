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
        $elapsed = time() - $this->timestamp;

        if (Yii::$app->params['formatter']['timeAgoBefore'] !== false && $elapsed >= Yii::$app->params['formatter']['timeAgoBefore']) {
            return $this->renderDateTime($elapsed);
        }

        return $this->renderTimeAgo();
    }

    /**
     * Render TimeAgo Javascript
     * 
     * @return string timeago span
     */
    public function renderTimeAgo()
    {
        // Use static timeago
        if (Yii::$app->params['formatter']['timeAgoStatic']) {
            return '<span class="time"><span title="' . $this->getFullDateTime() . '">' . Yii::$app->formatter->asRelativeTime($this->timestamp) . '</span></span>';
        }

        // Convert timestamp to ISO 8601
        $this->timestamp = date("c", $this->timestamp);

        $this->getView()->registerJs('$(".time").timeago();', \yii\web\View::POS_END, 'timeago');
        return '<span class="time" title="' . $this->timestamp . '">' . $this->getFullDateTime() . '</span>';
    }

    /**
     * Show full date
     * 
     * @param int $elasped time in seconds
     * @return string output of full date and time
     */
    public function renderDateTime($elapsed)
    {
        // Show time when within specified range
        if (Yii::$app->params['formatter']['timeAgoHideTimeAfter'] === false || $elapsed <= Yii::$app->params['formatter']['timeAgoHideTimeAfter']) {
            $date = $this->getFullDateTime();
        } else {
            $date = Yii::$app->formatter->asDate($this->timestamp, 'medium');
        }

        return '<span class="time"><span title="' . $this->getFullDateTime() . '">' . $date . '</span></span>';
    }

    /**
     * Returns full date as text
     * 
     * @return string
     */
    protected function getFullDateTime()
    {
        if (isset(Yii::$app->params['formatter']['timeAgoFullDateCallBack']) && is_callable(Yii::$app->params['formatter']['timeAgoFullDateCallBack'])) {
            return call_user_func(Yii::$app->params['formatter']['timeAgoFullDateCallBack'], $this->timestamp);
        }

        return Yii::$app->formatter->asDate($this->timestamp, 'medium') . ' - ' . Yii::$app->formatter->asTime($this->timestamp, 'short');
    }

}
