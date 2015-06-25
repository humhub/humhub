<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * Description of TimeAgo
 *
 * @author luke
 */
class TimeAgo extends \yii\base\Widget
{

    public $timestamp;

    public function run()
    {
        return "TA" . $this->timestamp;
    }

}
