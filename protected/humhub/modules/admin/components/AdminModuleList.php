<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\listing\ArrayListBuilder;
use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\Module;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\Module as MarketplaceModule;
use Throwable;
use Yii;

/**
 * The installed modules of the administration's module table ({@see ModuleTable},
 * `GET /api/v2/admin/module`), narrowed by a search (`q`: name, description or id), the `status`
 * (enabled or disabled) and `update` (`available`: a newer compatible version in the
 * marketplace), in the order of a column (`sort`: `name`, `version`, `update`, `status`, each
 * also descending with a leading `-`; without, the modules with an update first, then the
 * enabled and the disabled ones, each by name). The builder is an {@see ArrayListBuilder} of
 * {@see Module}s keyed by id.
 *
 * @since 1.20
 */
class AdminModuleList extends FilterableList
{
    public const STATUS_ENABLED = 'enabled';
    public const STATUS_DISABLED = 'disabled';

    public const UPDATE_AVAILABLE = 'available';

    private const SORTS = ['name', 'version', 'update', 'status'];

    private static ?array $updates = null;

    /**
     * The newer compatible versions in the marketplace by module id, from its cached module list;
     * none without the marketplace, the permission to manage modules, or a connection.
     *
     * @return array<string, string>
     */
    public static function availableUpdates(): array
    {
        if (self::$updates !== null) {
            return self::$updates;
        }

        self::$updates = [];
        if (!MarketplaceModule::isMarketplaceEnabled() || !Yii::$app->user->can(ManageModules::class)) {
            return self::$updates;
        }

        try {
            /** @var MarketplaceModule $marketplace */
            $marketplace = Yii::$app->getModule('marketplace');
            foreach ($marketplace->getOnlineModuleManager()->getModuleUpdates() as $id => $info) {
                self::$updates[$id] = (string)$info['latestCompatibleVersion'];
            }
        } catch (Throwable $e) {
            Yii::warning('Could not read the module updates: ' . $e->getMessage(), 'admin');
        }

        return self::$updates;
    }

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        return [
            new SearchFilter(
                'q',
                apply: static fn(ArrayListBuilder $list, string $keywords) => $list->filter(
                    static fn(Module $module) => stripos($module->getName(), $keywords) !== false
                        || stripos($module->getDescription(), $keywords) !== false
                        || stripos($module->id, $keywords) !== false,
                ),
                definition: [
                    'label' => Yii::t('AdminModule.modules', 'Search'),
                    'placeholder' => Yii::t('AdminModule.modules', 'Name or description'),
                    'sortOrder' => 100,
                ],
            ),
            new EnumFilter(
                'update',
                values: [self::UPDATE_AVAILABLE => Yii::t('AdminModule.modules', 'Update available')],
                apply: static fn(ArrayListBuilder $list) => $list->filter(
                    static fn(Module $module) => isset(self::availableUpdates()[$module->id]),
                ),
                definition: [
                    'label' => Yii::t('AdminModule.modules', 'Update'),
                    'sortOrder' => 200,
                ],
            ),
            new EnumFilter(
                'status',
                values: [
                    self::STATUS_ENABLED => Yii::t('AdminModule.modules', 'Enabled'),
                    self::STATUS_DISABLED => Yii::t('AdminModule.modules', 'Disabled'),
                ],
                apply: static fn(ArrayListBuilder $list, string $status) => $list->filter(
                    static fn(Module $module) => $module->getIsEnabled() === ($status === self::STATUS_ENABLED),
                ),
                definition: [
                    'label' => Yii::t('AdminModule.modules', 'Status'),
                    'sortOrder' => 300,
                ],
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function sorts(): array
    {
        // Accepted, not offered in a sort select: the table's column headers sort.
        $sorts = [];
        foreach (self::SORTS as $key) {
            $sorts[$key] = null;
            $sorts['-' . $key] = null;
        }

        return $sorts;
    }

    /**
     * @inheritdoc
     */
    protected function createBuilder(ListContext $context): ListBuilder
    {
        return new ArrayListBuilder(Yii::$app->moduleManager->getModules());
    }

    /**
     * @inheritdoc
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var ArrayListBuilder $builder */
        $sort = $this->value($values, self::SORT_PARAM);
        if ($sort !== null && $this->applySortCallback($builder, $sort, $context)) {
            return;
        }

        $updates = self::availableUpdates();
        $name = static fn(Module $a, Module $b) => strnatcasecmp($a->getName(), $b->getName());
        $key = ltrim((string)$sort, '-');
        $direction = str_starts_with((string)$sort, '-') ? -1 : 1;

        $compare = match ($key) {
            'name' => $name,
            'version' => static fn(Module $a, Module $b) => version_compare((string)$a->getVersion(), (string)$b->getVersion()),
            'update' => static fn(Module $a, Module $b) => isset($updates[$b->id]) <=> isset($updates[$a->id]),
            'status' => static fn(Module $a, Module $b) => $b->getIsEnabled() <=> $a->getIsEnabled(),
            default => null,
        };

        if ($compare === null) {
            // The modules with an update, then the enabled ones, then the disabled ones
            $builder->sort(static fn(Module $a, Module $b) => [!isset($updates[$a->id]), !$a->getIsEnabled()] <=> [!isset($updates[$b->id]), !$b->getIsEnabled()]
                ?: $name($a, $b));
            return;
        }

        $builder->sort(static fn(Module $a, Module $b) => ($compare($a, $b) * $direction) ?: $name($a, $b));
    }
}
