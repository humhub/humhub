<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;

/**
 * TrackingWidget adds statistic tracking code to all layouts
 *
 * @since 1.1
 * @author Luke
 */
class TrackingWidget extends \humhub\components\Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        return Yii::$app->settings->get('trackingHtmlCode');
    }

}
