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
use Yii;

/**
 * The installed modules of the administration's module table ({@see ModuleTable},
 * `GET /api/v2/admin/module`), narrowed by a search (`q`: name, description or id) and the
 * `status` (enabled or disabled), in the order of a column (`sort`: `name`, `version`, `status`,
 * each also descending with a leading `-`; without, the enabled and then the disabled ones,
 * each by name). The builder is an {@see ArrayListBuilder} of {@see Module}s keyed by id. It
 * asks the marketplace nothing, so the page stays fast.
 *
 * @since 1.20
 */
class AdminModuleList extends FilterableList
{
    public const STATUS_ENABLED = 'enabled';
    public const STATUS_DISABLED = 'disabled';

    private const SORTS = ['name', 'version', 'status'];

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

        $name = static fn(Module $a, Module $b) => strnatcasecmp($a->getName(), $b->getName());
        $key = ltrim((string)$sort, '-');
        $direction = str_starts_with((string)$sort, '-') ? -1 : 1;

        $compare = match ($key) {
            'name' => $name,
            'version' => static fn(Module $a, Module $b) => version_compare((string)$a->getVersion(), (string)$b->getVersion()),
            'status' => static fn(Module $a, Module $b) => $b->getIsEnabled() <=> $a->getIsEnabled(),
            default => null,
        };

        if ($compare === null) {
            // The enabled modules, then the disabled ones
            $builder->sort(static fn(Module $a, Module $b) => ($b->getIsEnabled() <=> $a->getIsEnabled()) ?: $name($a, $b));
            return;
        }

        $builder->sort(static fn(Module $a, Module $b) => ($compare($a, $b) * $direction) ?: $name($a, $b));
    }
}
