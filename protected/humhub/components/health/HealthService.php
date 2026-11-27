<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

use humhub\components\fs\LocalMountConfig;
use humhub\components\health\checks\CoreMigrationsCheck;
use humhub\components\health\checks\CronCheck;
use humhub\components\health\checks\DatabaseCheck;
use humhub\components\health\checks\DocumentRootCheck;
use humhub\components\health\checks\FilesystemCheck;
use humhub\components\health\checks\InstallationCheck;
use humhub\components\health\checks\MaintenanceCheck;
use humhub\components\health\checks\ModuleMigrationsCheck;
use humhub\components\health\checks\QueueCheck;
use humhub\modules\user\components\MaintenanceModeGate;
use Yii;
use yii\base\Component;

/**
 * Runs the health checks and builds the {@see HealthReport}. Application component `health`.
 *
 * Works without a database and on an instance that is not installed. Configurable through the
 * environment, e.g. `HUMHUB_CONFIG__COMPONENTS__HEALTH__TOKEN`.
 *
 * @since 1.20
 */
class HealthService extends Component
{
    public const EVENT_COLLECT_CHECKS = 'collectChecks';
    public const MIN_TOKEN_LENGTH = 32;
    public const MODULE_CHECK_CACHE_TTL = 300;

    /**
     * @var string|null secret for detailed HTTP responses (`Authorization: Bearer`), at least 32 characters
     */
    public ?string $token = null;

    private bool $tokenWarningLogged = false;

    /**
     * @var string[] ids of checks not to run, e.g. `['queue', 'filesystem:modules']`
     */
    public array $disabledChecks = [];

    /**
     * @var int seconds after the last cron run that make a warning; 0 turns the cron check off
     */
    public int $cronWarningAge = 3600;

    /**
     * @var int seconds after the last cron run that make a failure; 0 = never a failure
     */
    public int $cronFailureAge = 86400;

    /**
     * @var int seconds a queued job may wait before the queue counts as not processed
     */
    public int $queueWaitThreshold = 1200;

    public function run(HealthLevel $level): HealthReport
    {
        $state = $this->resolveState();
        $results = [];
        $databaseOk = true;

        foreach ($this->getCoreChecks($state) as $check) {
            if (!$level->includes($check->level) || $this->isDisabled($check->id)) {
                continue;
            }
            if ($check instanceof InstallationCheck) {
                $check->state = $state;
            }
            $results[$check->id] = $this->execute($check, $check->id, $state, $databaseOk, $level, false);
            if ($check->id === 'database' && $results[$check->id]->status !== HealthStatus::Ok) {
                $databaseOk = false;
                // Switch at once, so the following checks report against the same state as the report
                if (HealthState::isInstalled($state)) {
                    $state = HealthState::DATABASE_UNAVAILABLE;
                }
            }
        }

        if ($level === HealthLevel::All && $databaseOk && HealthState::isInstalled($state)) {
            $results += $this->runModuleChecks($state);
        }

        return new HealthReport($level, $state, Yii::$app->version, time(), $results);
    }

    /**
     * Runs a single core check regardless of its level, e.g. for the dashboard warning. A slow check is not
     * turned into a warning here: the caller asks about the checked condition, not about the response time.
     *
     * @return HealthResult|null null if the check does not exist or is disabled
     */
    public function runCheck(string $id): ?HealthResult
    {
        if ($this->isDisabled($id)) {
            return null;
        }

        $state = $this->resolveState();
        foreach ($this->getCoreChecks($state) as $check) {
            if ($check->id === $id) {
                return $this->execute($check, $check->id, $state, true, HealthLevel::Ready, false, false);
            }
        }

        return null;
    }

    public function isTokenConfigured(): bool
    {
        if ($this->token === null || $this->token === '') {
            return false;
        }

        if (strlen($this->token) < self::MIN_TOKEN_LENGTH) {
            if (!$this->tokenWarningLogged) {
                $this->tokenWarningLogged = true;
                Yii::warning('Health check token ignored: it must have at least ' . self::MIN_TOKEN_LENGTH . ' characters.', 'health');
            }
            return false;
        }

        return true;
    }

    public function validateToken(string $token): bool
    {
        return $this->isTokenConfigured() && hash_equals($this->token, $token);
    }

    protected function resolveState(): string
    {
        return HealthState::resolve(
            fn() => Yii::$app->installationState,
            function (): bool {
                try {
                    return MaintenanceModeGate::isActive();
                } catch (\Throwable) {
                    return false;
                }
            },
        );
    }

    /**
     * The core checks in report order: live, ready, all.
     *
     * @return HealthCheck[]
     */
    protected function getCoreChecks(string $state): array
    {
        $checks = [
            new FilesystemCheck(['id' => 'filesystem:runtime', 'level' => HealthLevel::Live, 'paths' => ['@runtime']]),
        ];

        $checks[] = $this->guarded('filesystem:assets', HealthLevel::Live, function () {
            $assetsMount = Yii::$app->fs->getAssetsMountConfig();
            return $assetsMount instanceof LocalMountConfig
                ? new FilesystemCheck(['id' => 'filesystem:assets', 'level' => HealthLevel::Live, 'createdOnUse' => [$assetsMount->path]])
                : null;
        });

        $checks[] = $this->guarded('filesystem:logs', HealthLevel::Live, function () {
            $logs = Yii::getAlias('@logs');
            return !str_starts_with($logs . DIRECTORY_SEPARATOR, Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR)
                ? new FilesystemCheck(['id' => 'filesystem:logs', 'level' => HealthLevel::Live, 'createdOnUse' => [$logs]])
                : null;
        });

        $migrations = new PendingMigrations();
        $checks[] = new DatabaseCheck(['state' => $state]);
        $checks[] = new InstallationCheck(['state' => $state]);
        $checks[] = new CoreMigrationsCheck(['migrations' => $migrations]);
        $checks[] = new ModuleMigrationsCheck(['migrations' => $migrations]);

        $checks[] = $this->guarded('filesystem:uploads', HealthLevel::All, function () {
            $dataMount = Yii::$app->fs->getDataMountConfig();
            if (!$dataMount instanceof LocalMountConfig) {
                return null;
            }
            $uploads = Yii::getAlias($dataMount->path);
            return new FilesystemCheck(['id' => 'filesystem:uploads', 'paths' => [$uploads], 'createdOnUse' => [$uploads . '/file', $uploads . '/profile_image']]);
        });

        $checks[] = $this->guarded('filesystem:modules', HealthLevel::All, function () {
            $marketplace = Yii::$app->getModule('marketplace');
            return $marketplace !== null && !empty($marketplace->modulesPath)
                ? new FilesystemCheck(['id' => 'filesystem:modules', 'paths' => [$marketplace->modulesPath], 'failStatus' => HealthStatus::Warning])
                : null;
        });

        if ($this->cronWarningAge > 0) {
            $checks[] = new CronCheck(['warningAge' => $this->cronWarningAge, 'failureAge' => $this->cronFailureAge]);
        }
        $checks[] = new QueueCheck(['waitThreshold' => $this->queueWaitThreshold]);
        $checks[] = new DocumentRootCheck();
        $checks[] = new MaintenanceCheck();

        return array_values(array_filter($checks));
    }

    /**
     * Builds a check that depends on configuration. A configuration error becomes an `unknown` result of this
     * check instead of breaking the whole report.
     *
     * @param callable(): ?HealthCheck $factory null if the check does not apply
     */
    private function guarded(string $id, HealthLevel $level, callable $factory): ?HealthCheck
    {
        try {
            return $factory();
        } catch (\Throwable $e) {
            Yii::error($e, 'health');
            $message = 'Check could not be set up: ' . ($e->getMessage() !== '' ? $e->getMessage() : $e::class);

            return new class (['id' => $id, 'level' => $level, 'message' => $message]) extends HealthCheck {
                public string $message = '';

                public function run(): HealthResult
                {
                    return HealthResult::unknown($this->message);
                }
            };
        }
    }

    /**
     * @return array<string, HealthResult>
     */
    private function runModuleChecks(string $state): array
    {
        $event = new CollectChecksEvent();
        try {
            $this->trigger(self::EVENT_COLLECT_CHECKS, $event);
        } catch (\Throwable $e) {
            Yii::error($e, 'health');
        }

        $results = [];
        foreach ($event->getChecks() as [$moduleId, $check]) {
            $id = 'module:' . $moduleId . ':' . $check->id;
            if ($this->isDisabled($id)) {
                continue;
            }

            if (isset($results[$id])) {
                Yii::warning("Health check '$id' is registered more than once.", 'health');
                $results[$id] = HealthResult::warning('Check id registered more than once');
                continue;
            }

            if (!preg_match('/^[a-z0-9][a-z0-9._-]*$/i', $check->id)) {
                $results[$id] = HealthResult::warning('Invalid check id');
                continue;
            }

            if (!Yii::$app->hasModule($moduleId)) {
                $results[$id] = HealthResult::warning(sprintf('Module "%s" is not enabled', $moduleId));
                continue;
            }

            $result = $this->execute($check, $id, $state, true, HealthLevel::All, true);
            if ($result->status->severity() > HealthStatus::Warning->severity()) {
                $result = $result->withStatus(HealthStatus::Warning);
            }
            $results[$id] = $result;
        }

        return $results;
    }

    private function execute(
        HealthCheck $check,
        string $reportId,
        string $state,
        bool $databaseOk,
        HealthLevel $level,
        bool $isModuleCheck,
        bool $judgeDuration = true,
    ): HealthResult {
        if ($check->requiresInstallation || $isModuleCheck) {
            if ($state === HealthState::INVALID) {
                return HealthResult::unknown('skipped: invalid installation state');
            }
            if (!$databaseOk || $state === HealthState::DATABASE_UNAVAILABLE) {
                return HealthResult::unknown('skipped: database unavailable');
            }
            if (!HealthState::isInstalled($state)) {
                return HealthResult::ok('not installed');
            }
        }

        $ttl = $level === HealthLevel::All
            ? ($check->cacheTtl ?? ($isModuleCheck ? self::MODULE_CHECK_CACHE_TTL : 0))
            : 0;
        $cacheKey = 'health:' . $reportId . ':' . Yii::$app->version;

        if ($ttl > 0) {
            try {
                $cached = Yii::$app->cache->get($cacheKey);
                if ($cached instanceof HealthResult) {
                    return $cached;
                }
            } catch (\Throwable) {
                // no cache, run the check
            }
        }

        $start = hrtime(true);
        try {
            $result = $check->run();
        } catch (\Throwable $e) {
            Yii::error($e, 'health');
            $result = HealthResult::unknown($e->getMessage() !== '' ? $e->getMessage() : $e::class);
        }
        $ms = intdiv(hrtime(true) - $start, 1_000_000);
        $result = $result->withDuration($ms);

        if ($judgeDuration && $result->status === HealthStatus::Ok && $ms > $check->timeout * 1000) {
            $result = $result->withStatus(HealthStatus::Warning, sprintf('slow (%d ms > %d ms)', $ms, $check->timeout * 1000));
        }

        if ($ttl > 0) {
            try {
                Yii::$app->cache->set($cacheKey, $result->withCachedAt(gmdate('Y-m-d\TH:i:s\Z')), $ttl);
            } catch (\Throwable) {
                // caching is an optimisation only
            }
        }

        return $result;
    }

    /**
     * Whether a check id is listed in [[disabledChecks]].
     */
    public function isDisabled(string $id): bool
    {
        return in_array($id, $this->disabledChecks, true);
    }
}
