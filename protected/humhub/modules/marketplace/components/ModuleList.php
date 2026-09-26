<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\components;

use humhub\components\api\ApiRules;
use humhub\components\listing\ArrayListBuilder;
use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\modules\marketplace\models\Module;
use humhub\modules\marketplace\Module as MarketplaceModule;
use humhub\modules\marketplace\services\MarketplaceListService;
use Yii;

/**
 * The module list of the marketplace: which of the catalogue's modules
 * ({@see MarketplaceListService::all()}) it shows, filtered and in which order.
 * `GET /api/v2/marketplace/module` is built with it, and the marketplace page's filter
 * definitions are its {@see self::definitions()} (which never contact humhub.com).
 *
 * Parameters: `q` (the platform's module keyword search — name, description, keywords —, see
 * {@see \humhub\components\ModuleManager::filterModules()}, whose
 * `EVENT_AFTER_FILTER_MODULES` fires for every list), `status` (any of
 * {@see MarketplaceListService::STATUSES}), `tag` (any of {@see self::TAGS}), `useCase` (any
 * lowercase id — humhub.com is free to add use cases without a core release), `categoryId`
 * (`-1` = without category, `0` = all) and `id` (that one module, every other filter ignored —
 * what a link to one module carries). `status`, `tag` and `useCase` take several values,
 * repeated or comma-separated, and match any of them.
 *
 * The order is fixed: modules with an available update first, then the ones not installed
 * yet, then the installed ones — within each block the order humhub.com delivers.
 *
 * Modules add filters on {@see self::EVENT_INIT} and restrict the list on
 * {@see self::EVENT_BUILD}, the builder being an {@see ArrayListBuilder} of {@see Module}s
 * keyed by id (see {@see FilterableList}).
 *
 * @since 1.20
 */
class ModuleList extends FilterableList
{
    public const TAGS = ['professional', 'official', 'community', 'partner', 'featured', 'purchased'];

    private const STATUS_ORDER = [
        MarketplaceListService::STATUS_UPDATE => 0,
        MarketplaceListService::STATUS_NOT_INSTALLED => 1,
        MarketplaceListService::STATUS_INSTALLED => 2,
    ];

    /**
     * @param MarketplaceListService|null $service the catalogue; `null` = the one of the
     *        marketplace module's online module manager, created when the list is first built
     */
    public function __construct(private ?MarketplaceListService $service = null, array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        return [
            // Applied in finalize(): the keyword search runs for every list.
            new SearchFilter('q', definition: [
                'label' => Yii::t('MarketplaceModule.base', 'Search'),
                'placeholder' => Yii::t('MarketplaceModule.base', 'Search Modules...'),
                'sortOrder' => 100,
            ]),
            new EnumFilter(
                'status',
                values: [
                    MarketplaceListService::STATUS_INSTALLED => Yii::t('MarketplaceModule.base', 'Installed'),
                    MarketplaceListService::STATUS_NOT_INSTALLED => Yii::t('MarketplaceModule.base', 'Not Installed'),
                    MarketplaceListService::STATUS_UPDATE => Yii::t('MarketplaceModule.base', 'Update Available'),
                ],
                apply: fn(ArrayListBuilder $list, array $statuses) => $list->filter(
                    fn(Module $module) => in_array($this->getService()->status($module), $statuses, true),
                ),
                multiple: true,
                definition: ['label' => Yii::t('MarketplaceModule.base', 'Status'), 'sortOrder' => 200],
            ),
            new EnumFilter(
                'tag',
                values: [
                    'professional' => Yii::t('MarketplaceModule.base', 'Professional Edition'),
                    'official' => Yii::t('MarketplaceModule.base', 'Official'),
                    'community' => Yii::t('MarketplaceModule.base', 'Community'),
                    'partner' => Yii::t('MarketplaceModule.base', 'Partner'),
                    'featured' => Yii::t('MarketplaceModule.base', 'Featured'),
                    'purchased' => Yii::t('MarketplaceModule.base', 'Purchased'),
                ],
                apply: static fn(ArrayListBuilder $list, array $tags) => $list->filter(
                    static fn(Module $module) => self::hasAnyTag($module, $tags),
                ),
                multiple: true,
                definition: ['label' => Yii::t('MarketplaceModule.base', 'Type'), 'sortOrder' => 300],
            ),
            new EnumFilter(
                'useCase',
                apply: static fn(ArrayListBuilder $list, array $useCases) => $list->filter(
                    static fn(Module $module) => array_intersect($module->getUseCaseList(), $useCases) !== [],
                ),
                multiple: true,
                pattern: '/^[a-z0-9_-]+$/',
                definition: [
                    'label' => Yii::t('MarketplaceModule.base', 'Use Case'),
                    'optionsUrl' => ApiRules::url('marketplace/use-case'),
                    'sortOrder' => 400,
                ],
            ),
            new EnumFilter(
                'categoryId',
                apply: static fn(ArrayListBuilder $list, string $categoryId) => $list->filter(
                    static fn(Module $module) => self::inCategory($module, (int)$categoryId),
                ),
                pattern: '/^-?[0-9]+$/',
                definition: [
                    'label' => Yii::t('MarketplaceModule.base', 'Category'),
                    'optionsUrl' => ApiRules::url('marketplace/category'),
                    'sortOrder' => 500,
                ],
            ),
            // Applied in finalize(): it overrides every other filter.
            new SearchFilter('id', definition: ['hidden' => true, 'sortOrder' => 10000]),
        ];
    }

    /**
     * @inheritdoc
     * @return ArrayListBuilder of {@see Module}s keyed by id, in list order
     */
    public function build(array $params, ListContext $context): ArrayListBuilder
    {
        /** @var ArrayListBuilder $builder */
        $builder = parent::build($params, $context);

        return $builder;
    }

    public function getService(): MarketplaceListService
    {
        if ($this->service === null) {
            /** @var MarketplaceModule $module */
            $module = Yii::$app->getModule('marketplace');
            $this->service = new MarketplaceListService($module->getOnlineModuleManager());
        }

        return $this->service;
    }

    /**
     * @inheritdoc
     */
    protected function createBuilder(ListContext $context): ListBuilder
    {
        return new ArrayListBuilder($this->getService()->all());
    }

    /**
     * `id` selects that one module and ignores everything else; otherwise the keyword search,
     * then the order by status.
     *
     * @inheritdoc
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var ArrayListBuilder $builder */
        $id = $this->value($values, 'id');
        if ($id !== null) {
            $modules = $this->getService()->all();
            $builder->setItems(isset($modules[$id]) ? [$id => $modules[$id]] : []);

            return;
        }

        // Keyword search is the platform's (name, description, module keywords), and the
        // ModuleManager::EVENT_AFTER_FILTER_MODULES it fires keeps other modules' say in it.
        $builder->setItems(Yii::$app->moduleManager->filterModules($builder->items(), ['keyword' => (string)$this->value($values, 'q')]));

        $service = $this->getService();
        $builder->sort(static fn(Module $a, Module $b) => self::STATUS_ORDER[$service->status($a)] <=> self::STATUS_ORDER[$service->status($b)]);
    }

    /**
     * `0` = all, `-1` = without category.
     */
    private static function inCategory(Module $module, int $categoryId): bool
    {
        if ($categoryId === 0) {
            return true;
        }

        $categories = is_array($module->categories) ? array_map('intval', $module->categories) : [];

        return $categoryId === -1 ? $categories === [] : in_array($categoryId, $categories, true);
    }

    private static function hasAnyTag(Module $module, array $tags): bool
    {
        foreach ($tags as $tag) {
            $matches = match ($tag) {
                'professional' => $module->isProFeature(),
                'featured' => (bool)$module->featured,
                'official' => !$module->isThirdParty,
                'community' => (bool)$module->isCommunity,
                'partner' => (bool)$module->isPartner,
                'purchased' => (bool)$module->purchased,
                default => false,
            };
            if ($matches) {
                return true;
            }
        }

        return false;
    }
}
