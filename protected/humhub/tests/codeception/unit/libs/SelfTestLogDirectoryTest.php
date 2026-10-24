<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\libs;

use humhub\libs\SelfTest;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Controller;

/**
 * @since 1.20
 */
class SelfTestLogDirectoryTest extends HumHubDbTestCase
{
    private string|false $originalLogsAlias;
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        // The self test tells the setup controller apart from any other, so it expects one to be running
        Yii::$app->controller = new Controller('information', Yii::$app);

        $this->originalLogsAlias = Yii::getAlias('@logs', false);
        $this->path = sys_get_temp_dir() . '/humhub-logs-' . uniqid();
        mkdir($this->path, 0777, true);
    }

    protected function tearDown(): void
    {
        Yii::setAlias('@logs', $this->originalLogsAlias ?: null);
        chmod($this->path, 0777);
        rmdir($this->path);

        parent::tearDown();
    }

    public function testALogDirectoryInsideTheRuntimeDirectoryIsCoveredByTheRuntimeCheck()
    {
        Yii::setAlias('@logs', '@runtime/logs');

        $this->assertNull($this->logDirectoryCheck());
    }

    public function testARelocatedLogDirectoryIsCheckedOnItsOwn()
    {
        Yii::setAlias('@logs', $this->path);

        $this->assertSame('OK', $this->logDirectoryCheck()['state']);

        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('root can write anywhere');
        }

        chmod($this->path, 0555);

        $this->assertSame('ERROR', $this->logDirectoryCheck()['state']);
    }

    public function testAMissingRelocatedLogDirectoryNeedsAWritableParent()
    {
        Yii::setAlias('@logs', $this->path . '/logs');

        $this->assertSame('OK', $this->logDirectoryCheck()['state']);
    }

    private function logDirectoryCheck(): ?array
    {
        $title = Yii::t('AdminModule.information', 'Permissions') . ' - ' . Yii::t('AdminModule.information', 'Logs');

        foreach (SelfTest::getResults() as $check) {
            if ($check['title'] === $title) {
                return $check;
            }
        }

        return null;
    }
}
