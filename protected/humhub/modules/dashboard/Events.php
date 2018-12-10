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
use yii\base\Event;
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
     * @param Event $event
     */
    public static function onTopMenuInit($event)
    {
        /** @var TopMenu $topMenu */
        $topMenu = $event->sender;

        $entry = new MenuEntry();

        $entry->setId('dashboard');
        $entry->setLabel(Yii::t('DashboardModule.base', 'Dashboard'));
        $entry->setUrl(['/dashboard/dashboard']);
        $entry->setIcon(new Icon(['name' => 'tachometer']));
        $entry->setSortOrder(100);
        $entry->setIsActive((Yii::$app->controller->module && Yii::$app->controller->module->id === 'dashboard'));

        $topMenu->addEntry($entry);
    }

}
