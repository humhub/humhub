<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\health;

use Codeception\Test\Unit;
use humhub\components\health\HealthLevel;
use humhub\components\health\HealthResult;
use humhub\components\health\HealthStatus;
use InvalidArgumentException;

/**
 * @since 1.20
 */
class HealthValueTypesTest extends Unit
{
    public function testWorstFollowsTheSeverityOrder(): void
    {
        $this->assertSame(HealthStatus::Ok, HealthStatus::worst());
        $this->assertSame(HealthStatus::Warning, HealthStatus::worst(HealthStatus::Ok, HealthStatus::Warning));
        $this->assertSame(HealthStatus::Unknown, HealthStatus::worst(HealthStatus::Warning, HealthStatus::Unknown));
        $this->assertSame(HealthStatus::Failure, HealthStatus::worst(HealthStatus::Unknown, HealthStatus::Failure, HealthStatus::Ok));
    }

    public function testExitCodesFollowNagios(): void
    {
        $this->assertSame(0, HealthStatus::Ok->exitCode());
        $this->assertSame(1, HealthStatus::Warning->exitCode());
        $this->assertSame(2, HealthStatus::Failure->exitCode());
        $this->assertSame(3, HealthStatus::Unknown->exitCode());
    }

    public function testLevelsAreCumulative(): void
    {
        $this->assertTrue(HealthLevel::Live->includes(HealthLevel::Live));
        $this->assertFalse(HealthLevel::Live->includes(HealthLevel::Ready));
        $this->assertTrue(HealthLevel::Ready->includes(HealthLevel::Live));
        $this->assertFalse(HealthLevel::Ready->includes(HealthLevel::All));
        $this->assertTrue(HealthLevel::All->includes(HealthLevel::Live));
        $this->assertTrue(HealthLevel::All->includes(HealthLevel::Ready));
    }

    public function testANonOkResultNeedsAnOutput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HealthResult(HealthStatus::Warning);
    }

    public function testReservedKeysAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HealthResult::ok(data: ['status' => 'mine']);
    }

    public function testToArrayPutsReservedKeysFirstAndDataInline(): void
    {
        $result = HealthResult::warning('1 pending', ['pending' => ['x' => ['m1']]])
            ->withDuration(5)
            ->withCachedAt('2026-11-25T10:00:00Z');

        $this->assertSame([
            'status' => 'warning',
            'output' => '1 pending',
            'durationMs' => 5,
            'cachedAt' => '2026-11-25T10:00:00Z',
            'pending' => ['x' => ['m1']],
        ], $result->toArray());
    }

    public function testAnOkResultWithoutOutputIsJustItsStatus(): void
    {
        $this->assertSame(['status' => 'ok'], HealthResult::ok()->toArray());
    }

    public function testWithStatusKeepsOutputUnlessReplaced(): void
    {
        $failure = HealthResult::failure('down', ['a' => 1]);

        $capped = $failure->withStatus(HealthStatus::Warning);
        $this->assertSame(HealthStatus::Warning, $capped->status);
        $this->assertSame('down', $capped->output);
        $this->assertSame(['a' => 1], $capped->data);

        $this->assertSame('slow', HealthResult::ok()->withStatus(HealthStatus::Warning, 'slow')->output);
    }
}
