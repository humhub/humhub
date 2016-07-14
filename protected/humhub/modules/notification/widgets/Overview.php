<?php

namespace humhub\modules\notification\widgets;

use Yii;

/**
 * NotificationListWidget shows an stream of notifications for an user at the top menu.
 *
 * @author andystrobel
 * @package humhub.modules_core.notification
 * @since 0.5
 */
class Overview extends \yii\base\Widget
{

    /**
     * Runs the notification widget
     */
    public function run()
    {
        if (Yii::$app->user->isGuest)
            return;

        return $this->render('overview', array(
                    'update' => \humhub\modules\notification\controllers\ListController::getUpdates()
        ));
    }

}

?>