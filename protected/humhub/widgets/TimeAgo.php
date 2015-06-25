<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

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

    public function run()
    {
        if (is_numeric($this->timestamp)) {
            $this->timestamp = date('Y-m-d H:i:s', $this->timestamp);
        }

        // Convert timestamp to ISO 8601
        $this->timestamp = date("c", strtotime($this->timestamp));

        $this->getView()->registerJs('$(".time").timeago();', \yii\web\View::POS_END, 'timeago');
        return '<span class="time" title="' . $this->timestamp . '">' . $this->timestamp . '</span>';
    }

}
