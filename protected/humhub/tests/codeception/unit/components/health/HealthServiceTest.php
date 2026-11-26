<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\health;

use Closure;
use humhub\components\fs\LocalMountConfig;
use humhub\components\health\CollectChecksEvent;
use humhub\components\health\HealthCheck;
use humhub\components\health\HealthLevel;
use humhub\components\health\HealthResult;
use humhub\components\health\HealthService;
use humhub\components\health\HealthState;
use humhub\components\health\HealthStatus;
use RuntimeException;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

/**
 * @since 1.20
 */
class HealthServiceTest extends HumHubDbTestCase
{
    protected function tearDown(): void
    {
        Event::off(HealthService::class, HealthService::EVENT_COLLECT_CHECKS);
        Yii::$app->cache->flush();
        parent::tearDown();
    }

    private function check(string $id, HealthLevel $level, HealthResult|Closure $result, array $config = []): HealthCheck
    {
        return new class (['id' => $id, 'level' => $level, 'result' => $result] + $config) extends HealthCheck {
            public HealthResult|Closure|null $result = null;
            public int $calls = 0;

            public function run(): HealthResult
            {
                $this->calls++;
                return $this->result instanceof Closure ? ($this->result)() : $this->result;
            }
        };
    }

    private function service(array $checks, string $state = HealthState::INSTALLED, array $config = []): HealthService
    {
        return new class ($checks, $state, $config) extends HealthService {
            public function __construct(private array $stubChecks, private string $stubState, array $config)
            {
                parent::__construct($config);
            }

            protected function getCoreChecks(string $state): array
            {
                return $this->stubChecks;
            }

            protected function resolveState(): string
            {
                return $this->stubState;
            }
        };
    }

    public function testLevelsSelectChecks(): void
    {
        $service = $this->service([
            $this->check('l', HealthLevel::Live, HealthResult::ok()),
            $this->check('r', HealthLevel::Ready, HealthResult::ok()),
            $this->check('a', HealthLevel::All, HealthResult::ok()),
        ]);

        $this->assertSame(['l'], array_keys($service->run(HealthLevel::Live)->results));
        $this->assertSame(['l', 'r'], array_keys($service->run(HealthLevel::Ready)->results));
        $this->assertSame(['l', 'r', 'a'], array_keys($service->run(HealthLevel::All)->results));
    }

    public function testAnExceptionBecomesUnknown(): void
    {
        $report = $this->service([
            $this->check('boom', HealthLevel::Live, fn() => throw new RuntimeException('kaputt')),
        ])->run(HealthLevel::Live);

        $this->assertSame(HealthStatus::Unknown, $report->results['boom']->status);
        $this->assertSame('kaputt', $report->results['boom']->output);
    }

    public function testReservedKeysInDataBecomeUnknown(): void
    {
        $report = $this->service([
            $this->check('bad', HealthLevel::Live, fn() => HealthResult::ok(data: ['status' => 'x'])),
        ])->run(HealthLevel::Live);

        $this->assertSame(HealthStatus::Unknown, $report->results['bad']->status);
    }

    public function testAFailedDatabaseSkipsDependentChecksAndMarksTheStateUnavailable(): void
    {
        $dependent = $this->check('migrations:core', HealthLevel::Ready, HealthResult::ok(), ['requiresInstallation' => true]);
        $report = $this->service([
            $this->check('database', HealthLevel::Ready, HealthResult::failure('down')),
            $dependent,
        ])->run(HealthLevel::Ready);

        $this->assertSame(0, $dependent->calls);
        $this->assertSame(HealthStatus::Unknown, $report->results['migrations:core']->status);
        $this->assertSame('skipped: database unavailable', $report->results['migrations:core']->output);
        $this->assertSame(HealthState::UNAVAILABLE, $report->state);
        $this->assertSame(HealthStatus::Failure, $report->getStatus());
    }

    public function testChecksNeedingAnInstallationAreOkBeforeIt(): void
    {
        $dependent = $this->check('cron', HealthLevel::All, HealthResult::failure('x'), ['requiresInstallation' => true]);
        $report = $this->service([$dependent], HealthState::DATABASE_CREATED)->run(HealthLevel::All);

        $this->assertSame(0, $dependent->calls);
        $this->assertSame(HealthStatus::Ok, $report->results['cron']->status);
        $this->assertSame('not installed', $report->results['cron']->output);
    }

    public function testDisabledChecksAreLeftOut(): void
    {
        $report = $this->service(
            [$this->check('queue', HealthLevel::All, HealthResult::warning('w'))],
            config: ['disabledChecks' => ['queue']],
        )->run(HealthLevel::All);

        $this->assertSame([], $report->results);
    }

    public function testASlowCheckIsAWarning(): void
    {
        $report = $this->service([
            $this->check('slow', HealthLevel::Live, function () {
                usleep(5000);
                return HealthResult::ok();
            }, ['timeout' => 0]),
        ])->run(HealthLevel::Live);

        $this->assertSame(HealthStatus::Warning, $report->results['slow']->status);
        $this->assertStringStartsWith('slow (', $report->results['slow']->output);
        $this->assertGreaterThanOrEqual(5, $report->results['slow']->durationMs);
    }

    public function testModuleChecksAreCappedNamespacedAndCached(): void
    {
        $failing = $this->check('bind', HealthLevel::Live, HealthResult::failure('LDAP down'));
        Event::on(HealthService::class, HealthService::EVENT_COLLECT_CHECKS, function (CollectChecksEvent $event) use ($failing) {
            $event->addCheck('user', $failing);
        });
        $service = $this->service([]);

        $this->assertSame([], $service->run(HealthLevel::Ready)->results, 'module checks only belong to all');

        $first = $service->run(HealthLevel::All)->results['module:user:bind'];
        $this->assertSame(HealthStatus::Warning, $first->status);
        $this->assertSame('LDAP down', $first->output);

        $second = $service->run(HealthLevel::All)->results['module:user:bind'];
        $this->assertSame(1, $failing->calls);
        $this->assertNotNull($second->cachedAt);
    }

    public function testModuleChecksOfDisabledModulesAndDuplicatesAreReported(): void
    {
        Event::on(HealthService::class, HealthService::EVENT_COLLECT_CHECKS, function (CollectChecksEvent $event) {
            $event->addCheck('doesnotexist', $this->check('x', HealthLevel::All, HealthResult::ok()));
            $event->addCheck('user', $this->check('dup', HealthLevel::All, HealthResult::ok()));
            $event->addCheck('user', $this->check('dup', HealthLevel::All, HealthResult::ok()));
        });

        $results = $this->service([])->run(HealthLevel::All)->results;

        $this->assertSame(HealthStatus::Warning, $results['module:doesnotexist:x']->status);
        $this->assertSame(HealthStatus::Warning, $results['module:user:dup']->status);
    }

    public function testModuleChecksDoNotRunBeforeTheInstallation(): void
    {
        $check = $this->check('x', HealthLevel::All, HealthResult::ok());
        Event::on(HealthService::class, HealthService::EVENT_COLLECT_CHECKS, fn(CollectChecksEvent $e) => $e->addCheck('user', $check));

        $this->service([], HealthState::DATABASE_CREATED)->run(HealthLevel::All);

        $this->assertSame(0, $check->calls);
    }

    public function testModuleCheckCacheIsSeparatedPerModule(): void
    {
        Event::on(HealthService::class, HealthService::EVENT_COLLECT_CHECKS, function (CollectChecksEvent $event) {
            $event->addCheck('user', $this->check('same', HealthLevel::All, HealthResult::warning('from user')));
            $event->addCheck('space', $this->check('same', HealthLevel::All, HealthResult::warning('from space')));
        });
        $service = $this->service([]);

        for ($i = 0; $i < 2; $i++) {
            $results = $service->run(HealthLevel::All)->results;
            $this->assertSame('from user', $results['module:user:same']->output);
            $this->assertSame('from space', $results['module:space:same']->output);
        }
    }

    public function testModuleChecksDoNotRunWhenTheDatabaseCheckFails(): void
    {
        $check = $this->check('x', HealthLevel::All, HealthResult::ok());
        Event::on(HealthService::class, HealthService::EVENT_COLLECT_CHECKS, fn(CollectChecksEvent $e) => $e->addCheck('user', $check));

        $report = $this->service([
            $this->check('database', HealthLevel::Ready, HealthResult::failure('down')),
        ])->run(HealthLevel::All);

        $this->assertSame(0, $check->calls);
        $this->assertArrayNotHasKey('module:user:x', $report->results);
    }

    public function testAThrowingModuleCheckIsAWarning(): void
    {
        Event::on(HealthService::class, HealthService::EVENT_COLLECT_CHECKS, function (CollectChecksEvent $event) {
            $event->addCheck('user', $this->check('boom', HealthLevel::All, fn() => throw new RuntimeException('kaputt')));
        });

        $result = $this->service([])->run(HealthLevel::All)->results['module:user:boom'];

        $this->assertSame(HealthStatus::Warning, $result->status);
        $this->assertSame('kaputt', $result->output);
    }

    public function testToken(): void
    {
        $token = str_repeat('a', 32);

        $this->assertFalse((new HealthService())->isTokenConfigured());
        $this->assertFalse((new HealthService(['token' => 'short']))->isTokenConfigured());
        $this->assertFalse((new HealthService(['token' => 'short']))->validateToken('short'));
        $this->assertTrue((new HealthService(['token' => $token]))->validateToken($token));
        $this->assertFalse((new HealthService(['token' => $token]))->validateToken($token . 'x'));
    }

    public function testRealCoreChecksOnTheTestInstallation(): void
    {
        $report = Yii::$app->health->run(HealthLevel::Ready);

        $this->assertSame(HealthState::INSTALLED, $report->state);
        $this->assertSame(
            ['filesystem:runtime', 'filesystem:assets', 'database', 'installation', 'migrations:core', 'migrations:module'],
            array_keys($report->results),
        );
        $this->assertSame(HealthStatus::Ok, $report->results['database']->status);
        $this->assertSame(HealthStatus::Ok, $report->results['migrations:core']->status);
    }

    public function testAMissingAssetsDirectoryIsOkWhenItCanBeCreated(): void
    {
        $fs = Yii::$app->fs;
        $original = $fs->getAssetsMountConfig();
        $fs->mounts['assets'] = new LocalMountConfig();
        $fs->mounts['assets']->path = Yii::getAlias('@runtime') . '/health-missing-assets-' . uniqid();

        try {
            $this->assertSame(HealthStatus::Ok, Yii::$app->health->runCheck('filesystem:assets')->status);
        } finally {
            $fs->mounts['assets'] = $original;
        }
    }

    public function testRunCheck(): void
    {
        Yii::$app->settings->set('cronLastRun', time());

        $this->assertSame(HealthStatus::Ok, Yii::$app->health->runCheck('cron')->status);
        $this->assertNull(Yii::$app->health->runCheck('nope'));
    }
}
