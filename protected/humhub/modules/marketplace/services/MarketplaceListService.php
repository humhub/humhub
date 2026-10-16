<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\services;

use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\models\Module;

/**
 * The catalogue of the marketplace: every module it lists, their status, use cases and
 * available updates. The list the page shows — filtered and ordered — is
 * {@see \humhub\modules\marketplace\components\ModuleList}.
 *
 * @since 1.20
 */
class MarketplaceListService
{
    public const STATUS_UPDATE = 'update';
    public const STATUS_NOT_INSTALLED = 'notInstalled';
    public const STATUS_INSTALLED = 'installed';

    public const STATUSES = [self::STATUS_UPDATE, self::STATUS_NOT_INSTALLED, self::STATUS_INSTALLED];

    /**
     * @var Module[]|null memoised result of {@see self::all()}, so a single request that
     *      calls `isAvailable()`, `all()` and `updateCount()` in turn builds the Module
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

    /**
     * `intranet` stays as is, `higher-education` / `higher_education` become `Higher education`
     * — there is no translation source for humhub.com's use case ids.
     */
    private static function humanizeUseCase(string $id): string
    {
        return ucfirst(str_replace(['-', '_'], ' ', $id));
    }
}
