<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health;

use humhub\models\ModuleEnabled;
use humhub\services\ModuleDiscoveryService;
use Yii;
use yii\db\Query;

/**
 * Migrations that exist on disk but are not applied, for core (`base` and core modules) and for enabled modules.
 *
 * Cheap enough for readiness probes: a directory listing per source and one query on the migration table.
 * No migration class and no module config.php is loaded.
 *
 * @since 1.20
 */
class PendingMigrations
{
    /** Same file pattern as yii\console\controllers\BaseMigrateController */
    private const FILE_PATTERN = '/^(m(\d{6}_?\d{6})\D.*?)\.php$/is';

    private ?array $corePending = null;
    private ?array $modulePending = null;

    /**
     * @param array<string, string>|null $corePaths source => migrations directory; null discovers them
     * @param array<string, string>|null $modulePaths module id => migrations directory; null discovers them
     * @param string[]|null $applied applied migration names; null reads the migration table
     */
    public function __construct(
        private ?array $corePaths = null,
        private ?array $modulePaths = null,
        private ?array $applied = null,
    ) {
    }

    /**
     * @return array<string, string[]>
     */
    public function getCorePending(): array
    {
        $this->discover();

        return $this->corePending ??= $this->findPending($this->corePaths);
    }

    /**
     * @return array<string, string[]>
     */
    public function getModulePending(): array
    {
        $this->discover();

        return $this->modulePending ??= $this->findPending($this->modulePaths);
    }

    private function findPending(array $paths): array
    {
        $applied = array_flip($this->applied ??= (new Query())->select('version')->from('{{%migration}}')->column());

        $pending = [];
        foreach ($paths as $source => $dir) {
            $names = [];
            foreach (is_dir($dir) ? scandir($dir) : [] as $file) {
                if (preg_match(self::FILE_PATTERN, $file, $matches) && is_file($dir . DIRECTORY_SEPARATOR . $file)
                    && !isset($applied[$matches[1]])) {
                    $names[] = $matches[1];
                }
            }
            if ($names !== []) {
                sort($names);
                $pending[$source] = $names;
            }
        }

        return $pending;
    }

    private function discover(): void
    {
        if ($this->corePaths !== null && $this->modulePaths !== null) {
            return;
        }

        $coreModulesDir = realpath(Yii::getAlias('@humhub/modules')) . DIRECTORY_SEPARATOR;
        $enabled = ModuleEnabled::getEnabledIds();
        $core = [];
        $modules = [];

        foreach (ModuleDiscoveryService::findMigrationPaths('@humhub/migrations') as $source => $dir) {
            $dir = Yii::getAlias($dir);
            if ($source === 'base' || str_starts_with((string)realpath($dir), $coreModulesDir)) {
                $core[$source] = $dir;
            } elseif (in_array($source, $enabled, true)) {
                $modules[$source] = $dir;
            }
        }

        $this->corePaths ??= $core;
        $this->modulePaths ??= $modules;
    }
}
