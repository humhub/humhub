<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\widgets;

use humhub\modules\notification\models\Notification;
use Yii;

/**
 * UpdateNotificationCount widget is an LayoutAddon widget for updating the notification count
 * and is only used if pjax is active.
 *
 * @author buddha
 * @since 1.2
 */
class UpdateNotificationCount extends \yii\base\Widget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $this->view->registerJs('$(document).one("humhub:ready", function() {
            if(humhub && humhub.modules.notification && humhub.modules.notification.menu) {
                humhub.modules.notification.menu.updateCount(' . Notification::findUnseen()->count() . ');
            }
        });');
    }
}
