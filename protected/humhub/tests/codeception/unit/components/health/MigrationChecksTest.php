<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\health;

use humhub\components\health\checks\CoreMigrationsCheck;
use humhub\components\health\checks\ModuleMigrationsCheck;
use humhub\components\health\HealthStatus;
use humhub\components\health\PendingMigrations;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\FileHelper;

/**
 * @since 1.20
 */
class MigrationChecksTest extends HumHubDbTestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = Yii::getAlias('@runtime/health-migrations-test');
        foreach (['base', 'user', 'custom_pages'] as $source) {
            FileHelper::createDirectory($this->dir . '/' . $source);
        }
        touch($this->dir . '/base/m260101_000000_applied.php');
        touch($this->dir . '/base/m260102_000000_pending.php');
        touch($this->dir . '/base/README.md');
        touch($this->dir . '/user/m260103_000000_user_pending.php');
        touch($this->dir . '/custom_pages/m261022_110000_fix_space_index_url.php');
    }

    protected function tearDown(): void
    {
        FileHelper::removeDirectory($this->dir);
        parent::tearDown();
    }

    private function migrations(array $applied = ['m260101_000000_applied']): PendingMigrations
    {
        return new PendingMigrations(
            corePaths: ['base' => $this->dir . '/base', 'user' => $this->dir . '/user'],
            modulePaths: ['custom_pages' => $this->dir . '/custom_pages'],
            applied: $applied,
        );
    }

    public function testPendingAreGroupedBySource(): void
    {
        $migrations = $this->migrations();

        $this->assertSame([
            'base' => ['m260102_000000_pending'],
            'user' => ['m260103_000000_user_pending'],
        ], $migrations->getCorePending());
        $this->assertSame(['custom_pages' => ['m261022_110000_fix_space_index_url']], $migrations->getModulePending());
    }

    public function testCoreCheckFailsOnPendingMigrations(): void
    {
        $result = (new CoreMigrationsCheck(['migrations' => $this->migrations()]))->run();

        $this->assertSame(HealthStatus::Failure, $result->status);
        $this->assertSame('2 pending core migration(s)', $result->output);
        $this->assertSame(['base', 'user'], array_keys($result->data['pending']));
    }

    public function testModuleCheckWarnsOnPendingMigrations(): void
    {
        $result = (new ModuleMigrationsCheck(['migrations' => $this->migrations()]))->run();

        $this->assertSame(HealthStatus::Warning, $result->status);
        $this->assertSame('custom_pages: 1 pending migration(s)', $result->output);
        $this->assertSame(['custom_pages' => ['m261022_110000_fix_space_index_url']], $result->data['pending']);
    }

    public function testNothingPendingIsOk(): void
    {
        $all = [
            'm260101_000000_applied', 'm260102_000000_pending', 'm260103_000000_user_pending',
            'm261022_110000_fix_space_index_url',
        ];

        $this->assertSame(HealthStatus::Ok, (new CoreMigrationsCheck(['migrations' => $this->migrations($all)]))->run()->status);
        $this->assertSame(HealthStatus::Ok, (new ModuleMigrationsCheck(['migrations' => $this->migrations($all)]))->run()->status);
    }

    /**
     * Against the real test database and the real migration directories: the test DB is fully migrated.
     */
    public function testTheTestInstallationHasNoPendingCoreMigrations(): void
    {
        $this->assertSame([], (new PendingMigrations())->getCorePending());
    }
}
