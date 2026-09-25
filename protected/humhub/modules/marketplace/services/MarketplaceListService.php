<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\services;

use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\models\Module;
use Yii;

/**
 * The module list of the marketplace: which modules it shows, filtered and in which order.
 *
 * The order is fixed: modules with an available update first, then the ones not installed yet,
 * then the installed ones — within each block the order humhub.com delivers.
 *
 * @since 1.20
 */
class MarketplaceListService
{
    public const STATUS_UPDATE = 'update';
    public const STATUS_NOT_INSTALLED = 'notInstalled';
    public const STATUS_INSTALLED = 'installed';

    public const STATUSES = [self::STATUS_UPDATE, self::STATUS_NOT_INSTALLED, self::STATUS_INSTALLED];

    public const TAGS = ['professional', 'official', 'community', 'partner', 'featured', 'purchased'];

    private const STATUS_ORDER = [
        self::STATUS_UPDATE => 0,
        self::STATUS_NOT_INSTALLED => 1,
        self::STATUS_INSTALLED => 2,
    ];

    /**
     * @var Module[]|null memoised result of {@see self::all()}, so a single request that
     *      calls `isAvailable()`, `find()` and `updateCount()` in turn builds the Module
     *      objects only once
     */
    private ?array $modules = null;

    public function __construct(private readonly OnlineModuleManager $onlineModuleManager)
    {
    }

    /**
     * Whether humhub.com delivered a catalogue at all — it never is empty when reachable.
     */
    public function isAvailable(): bool
    {
        return $this->onlineModuleManager->getModules() !== [];
    }

    /**
     * Every module the marketplace lists, keyed by id, in source order: all installed ones and
     * those not installed that have a compatible, non-legacy version.
     *
     * @return Module[]
     */
    public function all(): array
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $modules = [];
        foreach ($this->onlineModuleManager->getModules() as $id => $info) {
            $info['id'] ??= $id;
            $module = new Module($info);
            if ($module->isInstalled() || $module->isMarketplaced()) {
                $modules[$id] = $module;
            }
        }

        return $this->modules = $modules;
    }

    /**
     * @param array{q?: string, categoryId?: int|null, status?: string[], tag?: string[], useCase?: string[], id?: string} $params
     *        `categoryId` `0`/`null` = all, `-1` = without category; `status`, `tag` and
     *        `useCase` match any of their values; `id` selects that one module and ignores
     *        everything else
     * @return Module[] in list order
     */
    public function find(array $params = []): array
    {
        $modules = $this->all();

        $id = (string)($params['id'] ?? '');
        if ($id !== '') {
            return isset($modules[$id]) ? [$modules[$id]] : [];
        }

        // Keyword search is the platform's (name, description, module keywords), and the
        // ModuleManager::EVENT_AFTER_FILTER_MODULES it fires keeps other modules' say in it.
        $modules = Yii::$app->moduleManager->filterModules($modules, ['keyword' => (string)($params['q'] ?? '')]);

        $categoryId = isset($params['categoryId']) ? (int)$params['categoryId'] : 0;
        $statuses = $params['status'] ?? [];
        $tags = $params['tag'] ?? [];
        $useCases = $params['useCase'] ?? [];

        $matches = [];
        foreach (array_values($modules) as $index => $module) {
            $status = $this->status($module);
            if (!$this->inCategory($module, $categoryId)
                || ($statuses !== [] && !in_array($status, $statuses, true))
                || ($tags !== [] && !$this->hasAnyTag($module, $tags))
                || ($useCases !== [] && !$this->hasAnyUseCase($module, $useCases))) {
                continue;
            }
            $matches[] = [self::STATUS_ORDER[$status], $index, $module];
        }

        usort($matches, static fn(array $a, array $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        return array_column($matches, 2);
    }

    /**
     * Every use case present among the listed modules, with the number of listed modules
     * having it, sorted by name.
     *
     * @return array{id: string, name: string, count: int}[]
     */
    public function useCaseCounts(): array
    {
        $counts = [];
        foreach ($this->all() as $module) {
            foreach ($module->getUseCaseList() as $useCase) {
                $counts[$useCase] = ($counts[$useCase] ?? 0) + 1;
            }
        }

        $results = [];
        foreach ($counts as $id => $count) {
            $results[] = ['id' => $id, 'name' => self::humanizeUseCase($id), 'count' => $count];
        }

        usort($results, static fn(array $a, array $b) => $a['name'] <=> $b['name']);

        return $results;
    }

    /**
     * The number of listed modules with an available update, independent of any filter.
     */
    public function updateCount(): int
    {
        return count(array_filter($this->all(), static fn(Module $module) => $module->isUpdateAvailable()));
    }

    /**
     * @return string one of the `STATUS_*` constants
     */
    public function status(Module $module): string
    {
        if (!$module->isInstalled()) {
            return self::STATUS_NOT_INSTALLED;
        }

        return $module->isUpdateAvailable() ? self::STATUS_UPDATE : self::STATUS_INSTALLED;
    }

    private function inCategory(Module $module, int $categoryId): bool
    {
        if ($categoryId === 0) {
            return true;
        }

        $categories = is_array($module->categories) ? array_map('intval', $module->categories) : [];

        return $categoryId === -1 ? $categories === [] : in_array($categoryId, $categories, true);
    }

    private function hasAnyTag(Module $module, array $tags): bool
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

    private function hasAnyUseCase(Module $module, array $useCases): bool
    {
        return array_intersect($module->getUseCaseList(), $useCases) !== [];
    }

    /**
     * `intranet` stays as is, `higher-education` / `higher_education` become `Higher education`
     * — there is no translation source for humhub.com's use case ids.
     */
    private static function humanizeUseCase(string $id): string
    {
        return ucfirst(str_replace(['-', '_'], ' ', $id));
    }
}
