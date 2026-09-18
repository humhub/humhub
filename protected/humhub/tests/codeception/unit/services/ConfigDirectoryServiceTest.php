<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\services;

use Codeception\Test\Unit;
use humhub\services\ConfigDirectoryService;

/**
 * @since 1.20
 */
class ConfigDirectoryServiceTest extends Unit
{
    private string $rootPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rootPath = sys_get_temp_dir() . '/humhub-config-' . uniqid();
        mkdir($this->rootPath . '/config', 0777, true);
        mkdir($this->rootPath . '/protected/config', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->rootPath);

        parent::tearDown();
    }

    public function testBothDirectoriesAreDerivedFromTheInstallationRoot()
    {
        $service = new ConfigDirectoryService('/var/www/humhub');

        $this->assertSame('/var/www/humhub/config', $service->getPath());
        $this->assertSame('/var/www/humhub/protected/config', $service->getLegacyPath());
    }

    public function testTrailingSeparatorsOnTheInstallationRootAreIgnored()
    {
        $service = new ConfigDirectoryService('/var/www/humhub/');

        $this->assertSame('/var/www/humhub/config', $service->getPath());
    }

    public function testAnInstallationWithoutALegacyDirectoryHasNothingToMove()
    {
        $this->removeDirectory($this->rootPath . '/protected/config');

        $this->assertSame([], $this->service()->getLegacyEntries());
        $this->assertFalse($this->service()->hasLegacyEntries());
    }

    public function testTheFilesHumHubShippedIntoTheLegacyDirectoryAreNotReported()
    {
        $this->writeLegacy('.gitignore', "/dynamic.php\n");
        $this->writeLegacy('README.md', "# Local Configuration\n");
        $this->writeLegacy('common.php', '<?php return [];');
        $this->writeLegacy('web.php', '<?php return [];');
        $this->writeLegacy('console.php', '<?php return [];');
        mkdir($this->rootPath . '/protected/config/messages');
        $this->writeLegacy('messages/.gitignore', "*\n");

        $this->assertSame([], $this->service()->getLegacyEntries());
        $this->assertFalse($this->service()->hasLegacyEntries());
    }

    public function testConfigurationLeftInTheLegacyDirectoryIsReported()
    {
        $this->writeLegacy('common.php', '<?php return ["name" => "Test"];');
        $this->writeLegacy('web.php', '<?php return [];');
        $this->writeLegacy('security.json', '{}');
        mkdir($this->rootPath . '/protected/config/views');
        $this->writeLegacy('views/login.php', '');

        $this->assertSame(['common.php', 'security.json', 'views'], $this->service()->getLegacyEntries());
        $this->assertTrue($this->service()->hasLegacyEntries());
    }

    public function testTheDynamicConfigurationIsWrittenToTheNewDirectory()
    {
        $this->assertSame(
            $this->rootPath . '/config/dynamic.php',
            $this->service()->getDynamicConfigFile(),
        );
    }

    public function testAnUnmovedDynamicConfigurationKeepsBeingUsedWhereItIs()
    {
        $this->writeLegacy('dynamic.php', '<?php return ["components" => ["db" => []]];');

        $this->assertSame(
            $this->rootPath . '/protected/config/dynamic.php',
            $this->service()->getDynamicConfigFile(),
        );
    }

    private function service(): ConfigDirectoryService
    {
        return new ConfigDirectoryService($this->rootPath);
    }

    private function writeLegacy(string $name, string $content): void
    {
        file_put_contents($this->rootPath . '/protected/config/' . $name, $content);
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (array_diff(scandir($path), ['.', '..']) as $entry) {
            $child = $path . '/' . $entry;
            is_dir($child) ? $this->removeDirectory($child) : unlink($child);
        }

        rmdir($path);
    }
}
