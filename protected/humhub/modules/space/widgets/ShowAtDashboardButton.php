<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use Yii;
use \yii\base\Widget;

/**
 * Header Controls Button to include space contents in dashboard
 *
 * @author luke
 */
class ShowAtDashboardButton extends Widget
{

    public $space;

    public function run()
    {
        if (Yii::$app->user->isGuest || !$this->space->isMember()) {
            return;
        }

        $membership = $this->space->getMembership();

        if ($membership === null) {
            return;
        }

        $showAtDashboard = ($membership->show_at_dashboard);

        return $this->render('showAtDashboardButton', array(
                    'space' => $this->space,
                    'showAtDashboard' => $showAtDashboard
        ));
    }

}
