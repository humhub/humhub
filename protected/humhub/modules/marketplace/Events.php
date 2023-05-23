<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace;

use humhub\components\Module as CoreModule;
use humhub\components\OnlineModule;
use humhub\modules\admin\events\ModulesEvent;
use humhub\modules\admin\widgets\ModuleControls;
use humhub\modules\marketplace\models\Module as ModelModule;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\widgets\AccountTopMenu;
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
        if (!self::getEnabledMarketplaceModule()) {
            return;
        }

        $application = $event->sender;
        $application->controllerMap['module'] = commands\MarketplaceController::class;
    }

    public static function onHourlyCron($event)
    {
        Yii::$app->queue->push(new jobs\PeActiveCheckJob());
        Yii::$app->queue->push(new jobs\ModuleCleanupsJob());
    }

    private static function getEnabledMarketplaceModule(): ?Module
    {
        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        return $marketplaceModule->enabled ? $marketplaceModule : null;
    }

    public static function onAdminModuleManagerAfterFilterModules(ModulesEvent $event)
    {
        if (!self::getEnabledMarketplaceModule()) {
            return;
        }

        if (!is_array($event->modules)) {
            return;
        }

        foreach ($event->modules as $m => $module) {
            if (!self::isFilteredModule($module)) {
                unset($event->modules[$m]);
            }
        }
    }

    /**
     * @param CoreModule|ModelModule $module
     * @return bool
     */
    private static function isFilteredModule($module): bool
    {
        return self::isFilteredModuleByCategory($module) &&
            self::isFilteredModuleByTags($module);
    }

    /**
     * @param CoreModule|ModelModule $module
     * @return bool
     */
    private static function isFilteredModuleByCategory($module): bool
    {
        $categoryId = Yii::$app->request->get('categoryId', null);

        if (empty($categoryId)) {
            return true;
        }

        $moduleCategories = (new OnlineModule(['module' => $module]))->categories;

        return empty($moduleCategories) ? false : in_array($categoryId, $moduleCategories);
    }

    /**
     * @param CoreModule|ModelModule $module
     * @return bool
     */
    private static function isFilteredModuleByTags($module): bool
    {
        $tags = Yii::$app->request->get('tags', null);

        if (empty($tags)) {
            return true;
        }

        $tags = explode(',', $tags);

        $onlineModule = new OnlineModule(['module' => $module]);

        foreach ($tags as $tag) {
            switch ($tag) {
                case 'professional':
                    if ($onlineModule->isProOnly) {
                        return true;
                    }
                    break;
                case 'featured':
                    if ($onlineModule->isFeatured) {
                        return true;
                    }
                    break;
                case 'official':
                    if (!$onlineModule->isThirdParty) {
                        return true;
                    }
                    break;
                case 'partner':
                    if ($onlineModule->isPartner) {
                        return true;
                    }
                    break;
                case 'new':
                    // TODO: Filter by new status
                    break;
            }
        }

        return false;
    }

    public static function onAdminModuleControlsInit($event)
    {
        if (!self::getEnabledMarketplaceModule()) {
            return;
        }

        /* @var ModuleControls $moduleControls */
        $moduleControls = $event->sender;

        $module = $moduleControls->module;

        if (!($module instanceof ModelModule)) {
            return;
        }

        /** @var \humhub\modules\marketplace\models\Module $module */

        if ($module->isNonFree) {
            $moduleControls->addEntry(new MenuLink([
                'id' => 'marketplace-licence-key',
                'label' => Yii::t('MarketplaceModule.base', 'Add Licence Key'),
                'url' => ['/marketplace/purchase'],
                'htmlOptions' => ['data-target' => '#globalModal'],
                'icon' => 'key',
                'sortOrder' => 1000,
            ]));
        }

        if ($module->isThirdParty) {
            $moduleControls->addEntry(new MenuLink([
                'id' => 'marketplace-third-party',
                'label' => Yii::t('MarketplaceModule.base', 'Third-party')
                    . ($module->isCommunity ? ' - ' . Yii::t('MarketplaceModule.base', 'Community') : ''),
                'url' => ['/marketplace/browse/thirdparty-disclaimer'],
                'htmlOptions' => ['data-target' => '#globalModal'],
                'icon' => 'info-circle',
                'sortOrder' => 1100,
            ]));
        }
    }

    public static function onAccountTopMenuInit($event)
    {
        if (!self::getEnabledMarketplaceModule()) {
            return;
        }

        /* @var AccountTopMenu $menu */
        $menu = $event->sender;

        $menu->addEntry(new MenuLink([
            'label' => Yii::t('MarketplaceModule.base', 'Marketplace'),
            'icon' => 'download',
            'url' => Url::toRoute('/marketplace/browse'),
            'sortOrder' => 450,
        ]));
    }
}
