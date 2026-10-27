<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\listing\FilterableList;
use humhub\components\Module;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\marketplace\Module as MarketplaceModule;
use Yii;

/**
 * The administration's module table (Administration › Modules): the columns, row actions and the
 * list ({@see AdminModuleList}) of `admin\widgets\ModuleTable` and `GET /api/v2/admin/module`.
 * A row's id is the module's id. Created through the DI container, so a module may replace it.
 *
 * @since 1.20
 */
class ModuleTable extends DataTable
{
    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new AdminModuleList();
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('AdminModule.modules', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('version', ColumnDefinition::TYPE_TEXT, Yii::t('AdminModule.modules', 'Version'), priority: 2, sort: 'version', sortOrder: 200, narrow: true),
            new ColumnDefinition('status', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.modules', 'Status'), priority: 1, sort: 'status', filter: 'status', sortOrder: 400, narrow: true),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        $canManage = static fn() => Yii::$app->user->can(ManageModules::class);
        $moduleUrl = static fn(string $route) => static fn(Module $module) => [$route, 'moduleId' => $module->id];

        return [
            new RowAction(
                'configure',
                Yii::t('AdminModule.base', 'Configure'),
                RowAction::KIND_LINK,
                static fn(Module $module) => $module->getConfigUrl(),
                icon: 'settings',
                visible: static fn(Module $module) => $module->getIsEnabled() && $module->getConfigUrl() !== ''
                    && Yii::$app->user->can(ManageSettings::class),
                sortOrder: 100,
            ),
            new RowAction(
                'default',
                Yii::t('AdminModule.base', 'Set as default'),
                RowAction::KIND_MODAL,
                $moduleUrl('/admin/module/set-as-default'),
                icon: 'star',
                visible: static fn(Module $module) => $module->getIsEnabled() && $module instanceof ContentContainerModule && $canManage(),
                sortOrder: 200,
            ),
            new RowAction(
                'enable',
                Yii::t('AdminModule.base', 'Enable'),
                RowAction::KIND_REQUEST,
                static fn(Module $module) => ['/admin/api/module/enable', 'id' => $module->id],
                icon: 'player-play',
                visible: static fn(Module $module) => !$module->getIsEnabled() && $canManage(),
                sortOrder: 400,
            ),
            new RowAction(
                'disable',
                Yii::t('AdminModule.base', 'Disable'),
                RowAction::KIND_POST,
                $moduleUrl('/admin/module/disable'),
                icon: 'ban',
                confirm: Yii::t('AdminModule.modules', 'Disabling the module will permanently delete all module-related content.'),
                visible: static fn(Module $module) => $module->getIsEnabled() && $canManage(),
                sortOrder: 400,
            ),
            new RowAction(
                'marketplace',
                Yii::t('AdminModule.base', 'Show in Marketplace'),
                RowAction::KIND_LINK,
                static fn(Module $module) => ['/marketplace/browse', 'id' => $module->id],
                icon: 'info-circle',
                visible: static fn(Module $module) => self::isMarketplaceModule($module),
                sortOrder: 500,
            ),
            new RowAction(
                'uninstall',
                Yii::t('AdminModule.base', 'Uninstall'),
                RowAction::KIND_POST,
                $moduleUrl('/admin/module/remove'),
                icon: 'trash',
                confirm: Yii::t('AdminModule.modules', 'Uninstalling the module will permanently delete all module-related content.'),
                destructive: true,
                visible: static fn(Module $module) => $canManage() && $module->getModuleService()->canRemove(),
                sortOrder: 100,
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Module $model */
        return [
            'name' => [
                'text' => $model->getName(),
                'subtitle' => $model->getDescription() ?: null,
                'image' => $model->getImage(),
                'url' => $model->getIsEnabled() && $model->getConfigUrl() !== '' && Yii::$app->user->can(ManageSettings::class)
                    ? $model->getConfigUrl()
                    : null,
            ],
            'version' => ['text' => (string)$model->getVersion()],
            'status' => [$model->getIsEnabled()
                ? ['text' => Yii::t('AdminModule.modules', 'Enabled'), 'value' => AdminModuleList::STATUS_ENABLED, 'tone' => 'success']
                : ['text' => Yii::t('AdminModule.modules', 'Disabled'), 'value' => AdminModuleList::STATUS_DISABLED]],
        ];
    }

    /**
     * Whether the viewer may see the table: those who may manage modules or settings, as the web
     * module administration.
     */
    public function canAccess(): bool
    {
        return Yii::$app->user->can([ManageModules::class, ManageSettings::class]);
    }

    private static function isMarketplaceModule(Module $module): bool
    {
        /** @var MarketplaceModule|null $marketplace */
        $marketplace = Yii::$app->getModule('marketplace');

        return MarketplaceModule::isMarketplaceEnabled()
            && $marketplace !== null
            && dirname($module->basePath) === Yii::getAlias($marketplace->modulesPath);
    }
}
