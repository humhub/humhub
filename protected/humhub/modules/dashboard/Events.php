<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dashboard;

use humhub\modules\dashboard\widgets\ShareWidget;
use humhub\modules\ui\widgets\Icon;
use humhub\modules\ui\menu\events\MenuEvent;
use humhub\modules\ui\menu\MenuEntry;
use humhub\widgets\TopMenu;
use Yii;
use yii\helpers\Url;

/**
 * Description of Events
 *
 * @author luke
 */
class Events
{

    /**
     * TopMenu init event callback
     *
     * @see TopMenu
     * @param MenuEvent $event
     */
    public static function onTopMenuInit($event)
    {
        $topMenu = $event->sender;

        $entry = new MenuEntry();

        $entry->id = 'dashboard';
        $entry->label = Yii::t('DashboardModule.base', 'Dashboard');
        $entry->url = Url::to(['/dashboard/dashboard']);
        $entry->icon = new Icon(['name' => 'tachometer']);
        $entry->sortOrder = 100;
        $entry->isActive = function () {
            return (Yii::$app->controller->module && Yii::$app->controller->module->id === 'dashboard');
        };

        $topMenu->addEntry($entry);
    }

}
