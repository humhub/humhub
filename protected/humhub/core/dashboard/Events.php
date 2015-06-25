<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
namespace humhub\core\dashboard;

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
     * On build of the TopMenu, check if module is enabled
     * When enabled add a menu item
     *
     * @param type $event
     */
    public static function onTopMenuInit($event)
    {

        // Is Module enabled on this workspace?
        $event->sender->addItem(array(
            'label' => Yii::t('DashboardModule.base', 'Dashboard'),
            'id' => 'dashboard',
            'icon' => '<i class="fa fa-tachometer"></i>',
            'url' => Url::toRoute('/dashboard/dashboard'),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'dashboard'),
        ));
    }

}
