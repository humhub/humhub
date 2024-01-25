<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\Module;
use humhub\modules\marketplace\services\MarketplaceService;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\Button;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Modules displays the modules list
 *
 * @since 1.15
 * @author Luke
 */
class ModuleGroups extends Widget
{
    public array $groups = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initDefaultGroups();

        parent::init();

        ArrayHelper::multisort($this->groups, 'sortOrder');
    }

    private function initDefaultGroups()
    {
        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        $updateModules = $marketplaceModule->onlineModuleManager->getAvailableUpdateModules();
        $updateModulesCount = count($updateModules);
        (new MarketplaceService())->refreshPendingModuleUpdateCount($updateModulesCount);
        if ($updateModulesCount) {
            $updateAllButton = Button::primary(Yii::t('MarketplaceModule.base', 'Update all'))
                ->options([
                    'data-stop-title' => Icon::get('pause') . ' &nbsp; ' . Yii::t('MarketplaceModule.base', 'Stop updating'),
                    'data-stop-class' => 'btn btn-warning float-end',
                ])
                ->action('marketplace.updateAll')
                ->loader(false)
                ->cssClass('active float-end');

            $this->addGroup('availableUpdates', [
                'title' => Yii::t('MarketplaceModule.base', 'Available Updates'),
                'modules' => $updateModules,
                'count' => $updateModulesCount,
                'view' => 'module-update-card',
                'groupTemplate' => '<div class="container-module-updates">' . $updateAllButton . '{group}</div>',
                'moduleTemplate' => '<div class="card card-module col-lg-2 col-md-3 col-sm-4 col-6">{card}</div>',
                'sortOrder' => 10,
            ]);
        }

        $notInstalledModules = $marketplaceModule->onlineModuleManager->getNotInstalledModules();
        if ($notInstalledModulesCount = count($notInstalledModules)) {
            $this->addGroup('notInstalled', [
                'title' => Yii::t('MarketplaceModule.base', 'Uninstalled'),
                'modules' => Yii::$app->moduleManager->filterModules($notInstalledModules),
                'count' => $notInstalledModulesCount,
                'view' => 'module-uninstalled-card',
                'sortOrder' => 100,
            ]);
        }

        $installedModules = $marketplaceModule->onlineModuleManager->getInstalledModules();
        if ($installedModulesCount = count($installedModules)) {
            ArrayHelper::multisort($installedModules, 'isActivated', SORT_DESC);
            $this->addGroup('installed', [
                'title' => Yii::t('MarketplaceModule.base', 'Installed'),
                'modules' => Yii::$app->moduleManager->filterModules($installedModules),
                'count' => $installedModulesCount,
                'view' => 'module-installed-card',
                'noModulesMessage' => Yii::t('MarketplaceModule.base', 'No modules installed yet. Install some to enhance the functionality!'),
                'sortOrder' => 200,
            ]);
        }
    }

    public function addGroup(string $groupType, array $group)
    {
        $this->groups[$groupType] = $group;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $modules = '';

        $alwaysVisibleGroup = 'availableUpdates';
        $displaySingleGroup = false;
        $emptyGroupCount = 0;
        foreach ($this->groups as $groupType => $group) {
            if ($groupType !== $alwaysVisibleGroup && empty($group['modules'])) {
                $displaySingleGroup = true;
                $emptyGroupCount++;
            }
        }

        $singleGroupPrinted = false;
        foreach ($this->groups as $groupType => $group) {
            if ($singleGroupPrinted) {
                continue;
            }
            if (empty($group['count']) || ($emptyGroupCount === 1 && empty($group['modules']))) {
                continue;
            }
            if ($displaySingleGroup && $groupType !== $alwaysVisibleGroup) {
                $singleGroupPrinted = true;
                if (empty($group['modules'])) {
                    $group['title'] = false;
                }
            }
            $group['type'] = $groupType;
            $renderedGroup = $this->render('module-group', $group);

            if (isset($group['groupTemplate'])) {
                $renderedGroup = str_replace('{group}', $renderedGroup, $group['groupTemplate']);
            }

            $modules .= $renderedGroup;
        }

        return $modules;
    }

}
