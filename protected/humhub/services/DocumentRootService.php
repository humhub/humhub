<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use Yii;

/**
 * Knows where the document root is and how it is addressed.
 *
 * HumHub serves from `public/`; everything else in the installation root -
 * `protected/`, `uploads/`, `themes/`, the Composer and npm metadata - is meant
 * to stay out of reach of the web server. The deprecated entry script in the
 * installation root keeps older setups running, which means the entry script's
 * directory and the document root are no longer necessarily the same place.
 *
 * @since 1.20
 */
final class DocumentRootService
{
    /**
     * Name of the document root directory inside the installation root.
     */
    public const PUBLIC_DIR = 'public';

    /**
     * Set by the deprecated entry script in the installation root to the directory it lives in.
     *
     * Legacy mode is never inferred from paths: managed hosting runs its own entry scripts and
     * path layouts, which would produce both a false deprecation warning and a wrong URL prefix.
     * Only the shipped shim declares itself.
     */
    public const ENV_LEGACY_ENTRY_SCRIPT = 'HUMHUB_LEGACY_ENTRY_SCRIPT';

    /**
     * Overrides the URL the document root is reachable under, for layouts the derivation from the
     * entry script's location cannot cover.
     */
    public const ENV_PUBLIC_URL = 'HUMHUB_PUBLIC_URL';

    private readonly string $rootPath;
    private readonly string $publicPath;
    private readonly ?string $legacyEntryScriptPath;
    private readonly ?string $publicUrlOverride;

    /**
     * @param string $rootPath installation root
     * @param string|null $publicPath document root, defaults to [[PUBLIC_DIR]] below the installation root
     * @param string|null $legacyEntryScriptPath directory of the deprecated entry script, `null` when it is not in use
     * @param string|null $publicUrlOverride URL the document root is reachable under, for layouts the derivation cannot cover
     */
    public function __construct(
        string $rootPath,
        ?string $publicPath = null,
        ?string $legacyEntryScriptPath = null,
        ?string $publicUrlOverride = null,
    ) {
        $this->rootPath = self::normalizePath($rootPath);
        $this->publicPath = $publicPath === null
            ? $this->rootPath . '/' . self::PUBLIC_DIR
            : self::normalizePath($publicPath);
        $this->legacyEntryScriptPath = $legacyEntryScriptPath === null
            ? null
            : self::normalizePath($legacyEntryScriptPath);
        $this->publicUrlOverride = $publicUrlOverride === null
            ? null
            : rtrim($publicUrlOverride, '/');
    }

    /**
     * Builds the service from the `@root` alias and the environment.
     */
    public static function instance(): self
    {
        return new self(
            rootPath: Yii::getAlias('@root'),
            legacyEntryScriptPath: self::env(self::ENV_LEGACY_ENTRY_SCRIPT),
            publicUrlOverride: self::env(self::ENV_PUBLIC_URL),
        );
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    /**
     * @return bool whether the request came in through the deprecated entry script in the installation root
     */
    public function isLegacyEntryScript(): bool
    {
        return $this->legacyEntryScriptPath !== null;
    }

    public function getLegacyEntryScriptPath(): ?string
    {
        return $this->legacyEntryScriptPath;
    }

    /**
     * The URL the document root is reachable under - the value of the `@web` alias.
     *
     * Serving from `public/` makes this the request base URL. Behind the deprecated entry script
     * the document root sits one or more levels below the base URL, and the difference is measured
     * between the two real paths rather than assumed to be [[PUBLIC_DIR]].
     *
     * @param string $baseUrl base URL of the current request
     */
    public function getWebUrl(string $baseUrl): string
    {
        if ($this->publicUrlOverride !== null) {
            return $this->publicUrlOverride;
        }

        return rtrim($baseUrl, '/') . ($this->getDerivedUrlPrefix() ?? '');
    }

    /**
     * Whether the document root cannot be addressed: the deprecated entry script is in use, the
     * document root is not below it, and no override says where it is. Asset URLs are broken in
     * that case, which has to surface as a check instead of silently serving missing files.
     */
    public function hasUnresolvableWebUrl(): bool
    {
        return $this->isLegacyEntryScript()
            && $this->publicUrlOverride === null
            && $this->getDerivedUrlPrefix() === null;
    }

    /**
     * The URL the installation root would be reachable under, which it must not be.
     *
     * The deprecated entry script is served from the installation root, so there the base URL is
     * already that URL. Otherwise a base URL ending in the document root's directory name means the
     * web server hands out the level above it; anything else cannot address the installation root
     * through this site.
     *
     * @param string $baseUrl absolute base URL of the site
     */
    public function getInstallationRootUrl(string $baseUrl): ?string
    {
        $baseUrl = rtrim($baseUrl, '/');

        if ($this->isLegacyEntryScript()) {
            return $baseUrl;
        }

        $suffix = '/' . basename($this->publicPath);

        return str_ends_with($baseUrl, $suffix)
            ? substr($baseUrl, 0, -strlen($suffix))
            : null;
    }

    /**
     * @return string|null the path segments between the legacy entry script and the document root,
     *                     an empty string when they are the same directory, `null` when the document
     *                     root is not below the entry script
     */
    private function getDerivedUrlPrefix(): ?string
    {
        if (!$this->isLegacyEntryScript()) {
            return '';
        }

        if ($this->publicPath === $this->legacyEntryScriptPath) {
            return '';
        }

        if (!str_starts_with($this->publicPath, $this->legacyEntryScriptPath . '/')) {
            return null;
        }

        return substr($this->publicPath, strlen($this->legacyEntryScriptPath));
    }

    /**
     * @return string|null the environment value, `null` when unset or empty - an empty assignment
     *                     in a `.env` file means "not set", not "set to nothing"
     */
    private static function env(string $name): ?string
    {
        $value = $_ENV[$name] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
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
