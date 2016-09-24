<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\widgets;

use Yii;

/**
 * NotificationListWidget shows an stream of notifications for an user at the top menu.
 *
 * @author andystrobel
 * @since 0.5
 */
class Overview extends \yii\base\Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->user->isGuest)
            return;

        return $this->render('overview', array(
                    'update' => \humhub\modules\notification\controllers\ListController::getUpdates(),
                    'updateInterval' => Yii::$app->getModule('notification')->pollClientUpdateInterval
        ));
    }

}

?>