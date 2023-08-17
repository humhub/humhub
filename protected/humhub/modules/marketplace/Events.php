<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace;

use humhub\modules\admin\events\ModulesEvent;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\models\Module as ModelModule;
use humhub\modules\marketplace\services\MarketplaceService;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\widgets\AccountTopMenu;
use humhub\widgets\Label;
use Yii;
use yii\base\BaseObject;
use yii\base\Event;
use yii\helpers\Url;

class Events extends BaseObject
{

    /**
     * On console application initialization
     *
     * @param Event $event
     */
    public static function onConsoleApplicationInit($event)
    {
        if (!Module::isEnabled()) {
            return;
        }

        $application = $event->sender;
        $application->controllerMap['module'] = commands\MarketplaceController::class;
    }

    public static function onHourlyCron()
    {
        Yii::$app->queue->push(new jobs\PeActiveCheckJob());
        Yii::$app->queue->push(new jobs\ModuleCleanupsJob());
        Yii::$app->queue->push(new jobs\RefreshPendingModuleUpdateCountJob());
    }

    public static function onMarketplaceAfterFilterModules(ModulesEvent $event)
    {
        if (!Module::isEnabled()) {
            return;
        }

        if (!is_array($event->modules)) {
            return;
        }

        foreach ($event->modules as $m => $module) {
            /* @var ModelModule $module */
            if (!$module->getFilterService()->isFiltered()) {
                unset($event->modules[$m]);
            }
        }
    }

    public static function onAccountTopMenuInit($event)
    {
        if (!Module::isEnabled() ||
            !Yii::$app->user->isAdmin() ||
            !Yii::$app->user->can(ManageModules::class)) {
            return;
        }

        /* @var AccountTopMenu $menu */
        $menu = $event->sender;

        $updatesCount = (new MarketplaceService())->getPendingModuleUpdateCount();
        $updatesCountInfo = $updatesCount > 0 ? ' ' . Label::defaultType($updatesCount) : '';

        $menu->addEntry(new MenuLink([
            'label' => Yii::t('MarketplaceModule.base', 'Marketplace') . $updatesCountInfo,
            'icon' => 'cubes',
            'url' => Url::toRoute('/marketplace/browse'),
            'sortOrder' => 450,
        ]));
    }
}
