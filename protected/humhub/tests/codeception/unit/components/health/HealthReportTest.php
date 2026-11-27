<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\health;

use Codeception\Test\Unit;
use humhub\components\health\HealthLevel;
use humhub\components\health\HealthReport;
use humhub\components\health\HealthResult;
use humhub\components\health\HealthStatus;

/**
 * @since 1.20
 */
class HealthReportTest extends Unit
{
    private const TIME = 1795603260; // 2026-11-25T10:41:00Z

    private function report(array $results, string $state = 'installed', HealthLevel $level = HealthLevel::All): HealthReport
    {
        return new HealthReport($level, $state, '1.20.0', self::TIME, $results);
    }

    public function testStatusIsTheWorstCheck(): void
    {
        $report = $this->report([
            'a' => HealthResult::ok(),
            'b' => HealthResult::warning('w'),
            'c' => HealthResult::unknown('u'),
        ]);

        $this->assertSame(HealthStatus::Unknown, $report->getStatus());
        $this->assertSame(HealthStatus::Ok, $this->report([])->getStatus());
    }

    public function testEmptyChecksEncodeAsObject(): void
    {
        $this->assertStringContainsString('"checks":{}', json_encode($this->report([])->toArray()));
    }

    public function testNotesListProblemsAndNotableOkResults(): void
    {
        $report = $this->report([
            'installation' => HealthResult::ok('Maintenance mode enabled', notable: true),
            'cron' => HealthResult::ok('last run 2026-11-25T10:41:00Z'),
            'migrations:module' => HealthResult::warning('custom_pages: 1 pending migration'),
        ]);

        $this->assertSame(['Maintenance mode enabled', 'custom_pages: 1 pending migration'], $report->getNotes());
    }

    public function testDetailedArray(): void
    {
        $report = $this->report([
            'database' => HealthResult::ok()->withDuration(2),
            'queue' => HealthResult::ok('3 waiting', ['waiting' => 3]),
        ]);

        $this->assertSame([
            'format' => 1,
            'status' => 'ok',
            'state' => 'installed',
            'level' => 'all',
            'version' => '1.20.0',
            'time' => '2026-11-25T10:41:00Z',
            'notes' => [],
            'checks' => [
                'database' => ['status' => 'ok', 'durationMs' => 2],
                'queue' => ['status' => 'ok', 'output' => '3 waiting', 'waiting' => 3],
            ],
        ], $report->toArray());
    }

    public function testMinimalArrayHidesDetails(): void
    {
        $report = $this->report(['database' => HealthResult::failure('down')], 'database-unavailable', HealthLevel::Ready);

        $this->assertSame(['format' => 1, 'status' => 'failure', 'state' => 'database-unavailable'], $report->toArray(false));
    }

    public function testText(): void
    {
        $report = $this->report([
            'database' => HealthResult::ok(),
            'migrations:module' => HealthResult::warning('custom_pages: 1 pending migration'),
        ]);

        $this->assertSame(
            "WARNING  installed  HumHub 1.20.0  (all)\n"
            . "  ok       database\n"
            . "  warning  migrations:module  custom_pages: 1 pending migration\n",
            $report->toText(),
        );
    }

    public function testExitCodes(): void
    {
        $cases = [
            [HealthResult::ok(), 0, 0],
            [HealthResult::warning('w'), 1, 0],
            [HealthResult::failure('f'), 2, 1],
            [HealthResult::unknown('u'), 3, 1],
        ];

        foreach ($cases as [$result, $default, $failureOnly]) {
            $report = $this->report(['x' => $result]);
            $this->assertSame($default, $report->getExitCode(), $result->status->value);
            $this->assertSame($failureOnly, $report->getExitCode(true), $result->status->value);
        }
    }

    public function testHttpStatusCodes(): void
    {
        $this->assertSame(200, $this->report(['x' => HealthResult::ok()])->getHttpStatusCode());
        $this->assertSame(200, $this->report(['x' => HealthResult::warning('w')])->getHttpStatusCode());
        $this->assertSame(503, $this->report(['x' => HealthResult::failure('f')])->getHttpStatusCode());
        $this->assertSame(503, $this->report(['x' => HealthResult::unknown('u')])->getHttpStatusCode());
    }

    public function testTheSameNoteIsListedOnce(): void
    {
        $report = $this->report([
            'installation' => HealthResult::ok('Maintenance mode enabled', notable: true),
            'maintenance' => HealthResult::warning('Maintenance mode enabled'),
        ]);

        $this->assertSame(['Maintenance mode enabled'], $report->getNotes());
    }

    public function testInvalidUtf8IsReplacedInTheOutput(): void
    {
        $report = $this->report(['database' => HealthResult::failure("Verbindung fehlgeschlagen: \xE4", ['detail' => "\xFF"])]);

        $json = json_encode($report->toArray(), JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('Verbindung fehlgeschlagen', $json);
    }
}
