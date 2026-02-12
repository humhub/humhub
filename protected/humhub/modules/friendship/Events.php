<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship;

use humhub\helpers\ControllerHelper;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\widgets\AccountMenu;
use Yii;
use yii\base\BaseObject;
use yii\base\Event;

/**
 * Events provides callbacks for all defined module events.
 *
 * @author luke
 */
class Events extends BaseObject
{
    /**
     * Add friends navigation entry to account menu
     *
     * @param Event $event
     */
    public static function onAccountMenuInit($event)
    {
        if (Yii::$app->getModule('friendship')->isFriendshipEnabled()) {
            /* @var AccountMenu $menu */
            $menu = $event->sender;

            $menu->addEntry(new MenuLink([
                'label' => Yii::t('FriendshipModule.base', 'Friends'),
                'url' => ['/friendship/manage'],
                'icon' => 'group',
                'sortOrder' => 130,
                'isActive' => ControllerHelper::isActivePath('friendship'),
            ]));
        }
    }

}
