<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use Yii;

/**
 * Knows where the local configuration lives.
 *
 * Since 1.20 the local configuration directory sits in the installation root beside the `.env`
 * file, instead of below `protected/`. The old directory is still read so installations keep
 * working across the update, which means both locations can carry configuration at the same time
 * and something has to say which `dynamic.php` is the one HumHub writes to.
 *
 * @since 1.20
 */
final class ConfigDirectoryService
{
    /**
     * Name of the configuration directory inside the installation root.
     */
    public const CONFIG_DIR = 'config';

    /**
     * Configuration directory of installations that predate 1.20, relative to the installation root.
     */
    public const LEGACY_CONFIG_DIR = 'protected/config';

    /**
     * The one configuration file HumHub writes itself.
     */
    public const DYNAMIC_CONFIG_FILE = 'dynamic.php';

    /**
     * Files the legacy directory was shipped with. They carry no configuration, so finding them
     * there says nothing about whether the directory has been migrated.
     */
    private const SHIPPED_ENTRIES = ['.gitignore', 'README.md'];

    /**
     * Files that are configuration, and therefore only count as left behind when they are not empty.
     * Every installation predating 1.20 was shipped with the first three returning an empty array.
     */
    private const CONFIG_FILES = ['common.php', 'web.php', 'console.php', self::DYNAMIC_CONFIG_FILE];

    private readonly string $path;
    private readonly string $legacyPath;

    /**
     * @param string $rootPath installation root
     * @param string|null $configPath the configuration directory, defaults to [[CONFIG_DIR]] below
     *                                the installation root
     */
    public function __construct(string $rootPath, ?string $configPath = null)
    {
        $rootPath = self::normalizePath($rootPath);

        $this->path = $configPath === null
            ? $rootPath . '/' . self::CONFIG_DIR
            : self::normalizePath($configPath);
        $this->legacyPath = $rootPath . '/' . self::LEGACY_CONFIG_DIR;
    }

    /**
     * Builds the service from the aliases.
     *
     * The directory comes from `@config` rather than from the installation root, because an
     * installation is free to keep its configuration somewhere else entirely - the Docker image
     * does, pointing the alias at its mounted data volume.
     */
    public static function instance(): self
    {
        return new self(Yii::getAlias('@root'), Yii::getAlias('@config'));
    }

    /**
     * @return string the configuration directory in the installation root
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string the configuration directory of installations that predate 1.20
     */
    public function getLegacyPath(): string
    {
        return $this->legacyPath;
    }

    /**
     * The entries of the legacy directory that carry configuration, so that an installation which
     * has nothing but the files HumHub itself shipped there is not reported as unmigrated.
     *
     * @return string[] sorted names, empty when there is nothing left to move
     */
    public function getLegacyEntries(): array
    {
        $entries = @scandir($this->legacyPath);

        if ($entries === false) {
            return [];
        }

        $entries = array_values(array_filter(
            $entries,
            fn(string $entry): bool => $this->isLeftover($entry),
        ));

        sort($entries);

        return $entries;
    }

    /**
     * @return bool whether the legacy directory still carries configuration
     */
    public function hasLegacyEntries(): bool
    {
        return $this->getLegacyEntries() !== [];
    }

    /**
     * The dynamic configuration file in effect, as an absolute path.
     *
     * An installation that has not moved its old `dynamic.php` yet keeps being written to where it
     * already is. Pointing at the new location instead would have the installer, or any migration
     * touching the dynamic configuration, create a second file which then shadows the database
     * credentials in the old one.
     */
    public function getDynamicConfigFile(): string
    {
        return $this->hasLegacyDynamicConfigFile()
            ? $this->getLegacyDynamicConfigFile()
            : $this->path . '/' . self::DYNAMIC_CONFIG_FILE;
    }

    /**
     * The same file for `params.dynamicConfigFile`, but named through `@config` where it can be.
     *
     * That parameter is read long after the application configuration is built, and `@config` is
     * resolved only once the environment has had its say - an installation that relocates the
     * configuration directory through `HUMHUB_ALIASES__CONFIG`, as the Docker image does, would
     * otherwise have its database credentials written to the directory the alias replaced. Only an
     * unmoved legacy file is named absolutely: it does not live under the alias.
     */
    public function getDynamicConfigFileReference(): string
    {
        return $this->hasLegacyDynamicConfigFile()
            ? $this->getLegacyDynamicConfigFile()
            : '@' . self::CONFIG_DIR . '/' . self::DYNAMIC_CONFIG_FILE;
    }

    /**
     * @return string the dynamic configuration of an installation that predates 1.20, whether or
     *                not it is there
     */
    public function getLegacyDynamicConfigFile(): string
    {
        return $this->legacyPath . '/' . self::DYNAMIC_CONFIG_FILE;
    }

    public function hasLegacyDynamicConfigFile(): bool
    {
        return is_file($this->getLegacyDynamicConfigFile());
    }

    /**
     * @param string $entry name of an entry in the legacy directory
     * @return bool whether it is something the administrator has to move
     */
    private function isLeftover(string $entry): bool
    {
        if ($entry === '.' || $entry === '..' || in_array($entry, self::SHIPPED_ENTRIES, true)) {
            return false;
        }

        $path = $this->legacyPath . '/' . $entry;

        if (is_dir($path)) {
            return !$this->isEmptyDirectory($path);
        }

        return !in_array($entry, self::CONFIG_FILES, true) || !$this->isEmptyConfigFile($path);
    }

    /**
     * @return bool whether the directory holds nothing but the files HumHub shipped there
     */
    private function isEmptyDirectory(string $path): bool
    {
        $entries = @scandir($path);

        if ($entries === false) {
            return true;
        }

        return array_diff($entries, ['.', '..'], self::SHIPPED_ENTRIES) === [];
    }

    /**
     * Whether the file configures nothing. It is executed rather than parsed because that is what
     * the bootstrap does with it on every request anyway - a file that cannot be executed has
     * already brought the application down before this is reached.
     */
    private function isEmptyConfigFile(string $path): bool
    {
        if (!is_readable($path)) {
            return false;
        }

        $config = require $path;

        return $config === [];
    }

    /**
     * Normalizes to forward slashes without a trailing separator, so paths coming from Windows
     * hosts and paths carrying a trailing separator compare and concatenate the same way.
     */
    private static function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
