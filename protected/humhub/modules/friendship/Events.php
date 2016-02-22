<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship;

use Yii;
use yii\helpers\Url;

/**
 * Events provides callbacks for all defined module events.
 * 
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * Add friends navigation entry to account menu
     * 
     * @param \yii\base\Event $event
     */
    public static function onAccountMenuInit($event)
    {
        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            $event->sender->addItem(array(
                'label' => Yii::t('FriendshipModule.base', 'Friends'),
                'url' => Url::to(['/friendship/manage']),
                'icon' => '<i class="fa fa-group"></i>',
                'group' => 'account',
                'sortOrder' => 130,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'friendship'),
            ));
        }
    }

}
