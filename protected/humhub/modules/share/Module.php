<?php

namespace humhub\modules\share;

use Yii;
use humhub\models\Setting;
use humhub\modules\share\widgets\ShareWidget;

class Module extends \humhub\components\Module
{

    public static function onSidebarInit($event)
    {
        if (Setting::Get('enable', 'share') == 1 && Yii::$app->user->getIdentity()->getSetting("hideSharePanel", "share") != 1) {
            $event->sender->addWidget(ShareWidget::className(), array(), array('sortOrder' => 150));
        }
    }
}

?>
