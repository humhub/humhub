<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace;

use humhub\components\Module as CoreModule;
use humhub\modules\admin\events\ModulesEvent;
use humhub\modules\marketplace\models\Module as ModelModule;
use humhub\modules\marketplace\widgets\ModuleFilters;
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
        $categoryId = Yii::$app->request->get('categoryId');

        if (empty($categoryId)) {
            return true;
        }

        return is_array($module->categories) && in_array($categoryId, $module->categories);
    }

    /**
     * @param ModelModule $module
     * @return bool
     */
    private static function isFilteredModuleByTags(ModelModule $module): bool
    {
        $tags = Yii::$app->request->get('tags', ModuleFilters::getDefaultValue('tags'));

        if (empty($tags)) {
            return true;
        }

        $tags = explode(',', $tags);

        $searchInstalled = in_array('installed', $tags);
        $searchNotInstalled = in_array('uninstalled', $tags);
        if ($searchInstalled && $searchNotInstalled && count($tags) === 2) {
            // No need to filter when only 2 tags "Installed" and "Not Installed" are selected
            return true;
        }
        if ($searchInstalled && !$searchNotInstalled && !$module->isInstalled()) {
            // Exclude all NOT Installed modules when requested only Installed modules
            return false;
        }
        if (!$searchInstalled && $searchNotInstalled && $module->isInstalled()) {
            // Exclude all Installed modules when requested only NOT Installed modules
            return false;
        }
        if (($searchInstalled || $searchNotInstalled) && count($tags) === 1) {
            // No need to next filter when only 1 tag "Installed" or "Not Installed" is selected
            return true;
        }

        foreach ($tags as $tag) {
            switch ($tag) {
                case 'professional':
                    if ($module->isProOnly()) {
                        return true;
                    }
                    break;
                case 'featured':
                    if ($module->featured) {
                        return true;
                    }
                    break;
                case 'official':
                    if (!$module->isThirdParty) {
                        return true;
                    }
                    break;
                case 'partner':
                    if ($module->isPartner) {
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

    public static function onAccountTopMenuInit($event)
    {
        if (!Module::isEnabled()) {
            return;
        }

        /* @var AccountTopMenu $menu */
        $menu = $event->sender;

        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $updatesCount = count($marketplaceModule->onlineModuleManager->getModuleUpdates());
        $updatesCountInfo = $updatesCount > 0 ? ' ' . Label::defaultType($updatesCount) : '';

        $menu->addEntry(new MenuLink([
            'label' => Yii::t('MarketplaceModule.base', 'Marketplace') . $updatesCountInfo,
            'icon' => 'cubes',
            'url' => Url::toRoute('/marketplace/browse'),
            'sortOrder' => 450,
        ]));
    }
}
