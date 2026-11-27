<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\health;

use humhub\components\health\checks\DatabaseCheck;
use humhub\components\health\checks\FilesystemCheck;
use humhub\components\health\checks\InstallationCheck;
use humhub\components\health\HealthState;
use humhub\components\health\HealthStatus;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\Connection;
use yii\helpers\FileHelper;

/**
 * @since 1.20
 */
class BasicChecksTest extends HumHubDbTestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = Yii::getAlias('@runtime/health-test');
        FileHelper::createDirectory($this->dir);
    }

    protected function tearDown(): void
    {
        @chmod($this->dir . '/readonly', 0775);
        FileHelper::removeDirectory($this->dir);
        parent::tearDown();
    }

    public function testWritableDirectoryIsOk(): void
    {
        $check = new FilesystemCheck(['id' => 'filesystem:test', 'paths' => [$this->dir]]);

        $this->assertSame(HealthStatus::Ok, $check->run()->status);
    }

    public function testNotWritableDirectoryFailsWithItsPath(): void
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('root can write everywhere');
        }
        FileHelper::createDirectory($this->dir . '/readonly');
        chmod($this->dir . '/readonly', 0555);

        $result = (new FilesystemCheck(['id' => 'filesystem:test', 'paths' => [$this->dir . '/readonly']]))->run();

        $this->assertSame(HealthStatus::Failure, $result->status);
        $this->assertSame([$this->dir . '/readonly'], $result->data['notWritable']);
        $this->assertStringContainsString('readonly', $result->output);
    }

    public function testFailStatusIsConfigurable(): void
    {
        $check = new FilesystemCheck(['id' => 'filesystem:test', 'paths' => [$this->dir . '/missing'], 'failStatus' => HealthStatus::Warning]);

        $this->assertSame(HealthStatus::Warning, $check->run()->status);
    }

    public function testAMissingDirectoryCreatedOnUseIsOkWithAWritableParent(): void
    {
        $check = new FilesystemCheck(['id' => 'filesystem:test', 'createdOnUse' => [$this->dir . '/missing']]);

        $this->assertSame(HealthStatus::Ok, $check->run()->status);
    }

    public function testAMissingRequiredDirectoryFailsEvenWithAWritableParent(): void
    {
        $check = new FilesystemCheck([
            'id' => 'filesystem:test',
            'paths' => [$this->dir . '/missing'],
            'createdOnUse' => [$this->dir . '/missing/sub'],
        ]);
        $result = $check->run();

        $this->assertSame(HealthStatus::Failure, $result->status);
        $this->assertSame([$this->dir . '/missing', $this->dir . '/missing/sub'], $result->data['notWritable']);
    }

    public function testDatabaseOk(): void
    {
        $this->assertSame(HealthStatus::Ok, (new DatabaseCheck(['state' => HealthState::INSTALLED]))->run()->status);
    }

    public function testDatabaseNotReachableOnAnInstalledInstanceFails(): void
    {
        $result = (new DatabaseCheck(['state' => HealthState::INSTALLED, 'db' => $this->unreachableDb()]))->run();

        $this->assertSame(HealthStatus::Failure, $result->status);
    }

    public function testDatabaseNotSetUpBeforeInstallationIsOk(): void
    {
        $result = (new DatabaseCheck(['state' => HealthState::DATABASE_CONFIGURED, 'db' => $this->unreachableDb()]))->run();

        $this->assertSame(HealthStatus::Ok, $result->status);
        $this->assertNotNull($result->output);
    }

    public function testDatabaseUnavailableStateFailsWithoutConnecting(): void
    {
        $db = $this->createMock(Connection::class);
        $db->expects($this->never())->method('createCommand');

        $result = (new DatabaseCheck(['state' => HealthState::DATABASE_UNAVAILABLE, 'db' => $db]))->run();

        $this->assertSame(HealthStatus::Failure, $result->status);
    }

    public function testInstallationCheck(): void
    {
        $cases = [
            HealthState::INSTALLED => [HealthStatus::Ok, false],
            HealthState::MAINTENANCE => [HealthStatus::Ok, true],
            HealthState::NOT_INSTALLED => [HealthStatus::Ok, true],
            HealthState::DATABASE_CONFIGURED => [HealthStatus::Ok, true],
            HealthState::DATABASE_CREATED => [HealthStatus::Ok, true],
            HealthState::DATABASE_UNAVAILABLE => [HealthStatus::Unknown, false],
            HealthState::INVALID => [HealthStatus::Failure, false],
        ];

        foreach ($cases as $state => [$status, $notable]) {
            $result = (new InstallationCheck(['state' => $state]))->run();
            $this->assertSame($status, $result->status, $state);
            $this->assertSame($notable, $result->notable, $state);
        }
    }

    private function unreachableDb(): Connection
    {
        return new Connection([
            'dsn' => 'mysql:host=127.0.0.1;port=1;dbname=humhub_unreachable',
            'username' => 'humhub',
            'password' => 'humhub',
        ]);
    }
}
