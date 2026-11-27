<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\health;

use Codeception\Test\Unit;
use humhub\components\health\HealthState;
use humhub\components\InstallationState;
use yii\base\InvalidConfigException;

/**
 * @since 1.20
 */
class HealthStateTest extends Unit
{
    private function installationState(int $state, bool $unreachable = false): InstallationState
    {
        $mock = $this->createMock(InstallationState::class);
        $mock->method('hasState')->willReturnCallback(fn(int $s) => $state >= $s);
        $mock->method('isDatabaseUnreachable')->willReturn($unreachable);

        return $mock;
    }

    private function resolve(InstallationState $state, bool $maintenance = false): string
    {
        return HealthState::resolve(fn() => $state, fn() => $maintenance);
    }

    public function testLifecycleStates(): void
    {
        $this->assertSame(HealthState::NOT_INSTALLED, $this->resolve($this->installationState(InstallationState::STATE_NOT_INSTALLED)));
        $this->assertSame(HealthState::DATABASE_CONFIGURED, $this->resolve($this->installationState(InstallationState::STATE_DATABASE_CONFIGURED)));
        $this->assertSame(HealthState::DATABASE_CREATED, $this->resolve($this->installationState(InstallationState::STATE_DATABASE_CREATED)));
        $this->assertSame(HealthState::INSTALLED, $this->resolve($this->installationState(InstallationState::STATE_INSTALLED)));
    }

    public function testMaintenanceOnlyAppliesToAnInstalledInstance(): void
    {
        $this->assertSame(HealthState::MAINTENANCE, $this->resolve($this->installationState(InstallationState::STATE_INSTALLED), true));
        $this->assertSame(HealthState::DATABASE_CREATED, $this->resolve($this->installationState(InstallationState::STATE_DATABASE_CREATED), true));
    }

    public function testUnreachableDatabaseWins(): void
    {
        $this->assertSame(HealthState::DATABASE_UNAVAILABLE, $this->resolve($this->installationState(InstallationState::STATE_DATABASE_CONFIGURED, true), true));
    }

    public function testInvalidState(): void
    {
        $this->assertSame(
            HealthState::INVALID,
            HealthState::resolve(fn() => throw new InvalidConfigException('Invalid installation state: 7'), fn() => false),
        );
    }

    public function testIsInstalled(): void
    {
        $this->assertTrue(HealthState::isInstalled(HealthState::INSTALLED));
        $this->assertTrue(HealthState::isInstalled(HealthState::MAINTENANCE));
        $this->assertFalse(HealthState::isInstalled(HealthState::DATABASE_CREATED));
        $this->assertFalse(HealthState::isInstalled(HealthState::DATABASE_UNAVAILABLE));
    }
}
