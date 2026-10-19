<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\services;

use Codeception\Test\Unit;
use humhub\services\BootstrapService;
use Yii;

/**
 * @since 1.20
 */
class BootstrapServiceTest extends Unit
{
    private string $configPath;
    private string|false $originalConfigAlias;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configPath = sys_get_temp_dir() . '/humhub-bootstrap-config-' . uniqid();
        mkdir($this->configPath, 0777, true);
        file_put_contents(
            $this->configPath . '/common.php',
            "<?php\nreturn ['params' => ['bootstrapServiceTest' => 'relocated']];",
        );
        $this->originalConfigAlias = Yii::getAlias('@config', false);
    }

    protected function tearDown(): void
    {
        unset($_ENV['HUMHUB_ALIASES__CONFIG']);
        Yii::setAlias('@config', $this->originalConfigAlias ?: null);
        unlink($this->configPath . '/common.php');
        rmdir($this->configPath);

        parent::tearDown();
    }

    public function testTheEnvironmentRelocatesTheConfigurationDirectory()
    {
        $_ENV['HUMHUB_ALIASES__CONFIG'] = $this->configPath;

        $config = (new BootstrapService())->getConfig('console');

        $this->assertSame('relocated', $config['params']['bootstrapServiceTest']);
        $this->assertSame($this->configPath, $config['aliases']['@config']);
    }

    public function testSetPathsRelocatesTheConfigurationDirectory()
    {
        $bootstrap = new BootstrapService();
        $bootstrap->setPaths(config: $this->configPath);

        $config = $bootstrap->getConfig('console');

        $this->assertSame('relocated', $config['params']['bootstrapServiceTest']);
        $this->assertSame($this->configPath, $config['aliases']['@config']);
    }

    public function testTheConfigurationDirectoryDefaultsToTheInstallationRoot()
    {
        $config = (new BootstrapService())->getConfig('console');

        $this->assertSame(Yii::getAlias('@root') . '/config', $config['aliases']['@config']);
        $this->assertArrayNotHasKey('bootstrapServiceTest', $config['params']);
    }
}
