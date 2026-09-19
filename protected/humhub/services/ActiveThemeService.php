<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\components\InstallationState;
use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use Throwable;
use Yii;

/**
 * ActiveThemeService keeps everything the application knows about the active theme
 * in a single setting.
 *
 * The `theme` setting names the active theme and nothing else. Everything derived
 * from that name - the resolved base path, the parent chain and the SCSS variables -
 * lives in the `theme.state` setting, because the base settings are loaded as one
 * blob on every request: reading from them is free, whereas resolving a theme means
 * scanning `@humhub/themes`, `@themes` and the `themes/` directory of every
 * registered module.
 *
 * The stored state is validated on every access against four conditions, none of
 * which costs an additional cache or database request:
 *
 * 1. the theme name still matches the `theme` setting
 * 2. the system revision is unchanged - which already covers theme activation, saved
 *    design settings, enabled/disabled/updated modules and core updates, because
 *    {@see \humhub\components\bootstrap\SystemRevision} listens to all of them
 * 3. the Custom SCSS is unchanged - its variables are merged into the stored ones
 * 4. the modification time of every `scss/variables.scss` feeding the variables is
 *    unchanged - which covers a moved installation, a deleted theme directory and a
 *    theme developer editing the variables
 *
 * Unlike the per-variable settings cache this replaces, writing the state needs no
 * mutex: it is a single row instead of ~152, and {@see \humhub\libs\BaseSettingsManager::set()}
 * already retries on the IntegrityException of a concurrent insert.
 *
 * @since 1.20
 */
class ActiveThemeService
{
    public const SETTING_KEY = 'theme.state';

    /**
     * @var array|null the state validated during this request
     */
    private static ?array $state = null;

    /**
     * @var bool whether the state was resolved during this request, including a
     *           resolution that failed - so the theme scan is not repeated
     */
    private static bool $resolved = false;

    /**
     * @var Theme|null the theme built from the state during this request
     */
    private static ?Theme $theme = null;

    /**
     * @var Theme[]|null the parent themes built from the state during this request
     */
    private static ?array $parents = null;

    /**
     * Returns the state of the active theme, rebuilding it when the stored one is
     * missing or stale. Returns null while the database is not available yet, and
     * when not a single theme can be resolved - the caller then keeps the theme
     * configured in `common.php`.
     *
     * @return array{rev: string, scss: int, name: string, path: string, parents: string[], mtimes: array<string, int|null>, vars: array<string, string>}|null
     */
    public static function getState(): ?array
    {
        if (self::$state !== null) {
            return self::$state;
        }

        // A failed resolution is remembered as well, so the theme scan behind it does
        // not run again for every single caller of this request
        if (self::$resolved) {
            return null;
        }

        if (!Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            return null;
        }

        $state = Yii::$app->settings->getSerialized(self::SETTING_KEY);

        if (!self::isValid($state)) {
            $state = self::build();

            if ($state === null) {
                self::$resolved = true;
                return null;
            }

            Yii::$app->settings->setSerialized(self::SETTING_KEY, $state);
        }

        self::$resolved = true;

        return self::$state = $state;
    }

    /**
     * Returns the active theme, or null when it cannot be resolved.
     */
    public static function getTheme(): ?Theme
    {
        if (self::$theme !== null) {
            return self::$theme;
        }

        $state = self::getState();

        return self::$theme = $state === null ? null : ThemeHelper::getThemeByPath($state['path']);
    }

    /**
     * Returns the parent themes of the active theme, keyed by name like
     * {@see ThemeHelper::getThemeTree()}.
     *
     * @return Theme[]
     */
    public static function getParents(): array
    {
        if (self::$parents !== null) {
            return self::$parents;
        }

        $parents = [];
        $state = self::getState();

        foreach ($state['parents'] ?? [] as $path) {
            $theme = ThemeHelper::getThemeByPath($path);

            if ($theme === null) {
                Yii::error('Could not resolve the parent theme of the active theme at "' . $path . '".', 'ui');
                continue;
            }

            $parents[$theme->name] = $theme;
        }

        return self::$parents = $parents;
    }

    /**
     * Returns the SCSS variables of the active theme, including the ones set in the
     * Custom SCSS unless that failed to parse.
     */
    public static function getVariables(): array
    {
        return self::getState()['vars'] ?? [];
    }

    /**
     * Drops the stored state, so the next access rebuilds it.
     */
    public static function flush(): void
    {
        self::$state = null;
        self::$resolved = false;
        self::$theme = null;
        self::$parents = null;

        if (Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            Yii::$app->settings->delete(self::SETTING_KEY);
        }
    }

    private static function isValid(mixed $state): bool
    {
        // getSerialized() returns the raw string when the stored JSON is malformed,
        // so this is not merely a null check
        if (!is_array($state)) {
            return false;
        }

        if (!isset($state['name'], $state['path'], $state['rev'], $state['scss'], $state['vars'], $state['mtimes'])) {
            return false;
        }

        // Cast because BaseSettingsManager::get() runs the value through
        // FILTER_VALIDATE_INT: a theme directory named e.g. `2024` comes back as an int,
        // and the strict comparison would then rebuild the state on every request
        if ($state['name'] !== (string)Yii::$app->settings->get('theme')) {
            return false;
        }

        if ($state['rev'] !== Yii::$app->systemRevision->getPublicSignature()) {
            return false;
        }

        if ($state['scss'] !== crc32((string)Yii::$app->settings->get('themeCustomScss'))) {
            return false;
        }

        foreach ($state['mtimes'] as $path => $mtime) {
            if (self::getVariablesMtime($path) !== $mtime) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolves the active theme from scratch - the only place that pays for a theme
     * directory scan.
     *
     * A name that cannot be resolved falls back to the core theme but is kept in the
     * state as requested, so the admin's choice survives a module that merely happens
     * to be disabled right now - and so a valid state does not immediately go stale
     * again, which would rebuild on every request.
     */
    private static function build(): ?array
    {
        // Cast for the same reason as in isValid(), so both sides stay comparable
        $name = (string)Yii::$app->settings->get('theme');
        $theme = $name === '' ? null : ThemeHelper::getThemeByName($name);

        if ($theme === null) {
            if ($name !== '') {
                Yii::error('Could not resolve the active theme "' . $name . '", falling back to the core theme.', 'ui');
            }
            $theme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);
        }

        if ($theme === null) {
            Yii::error('Could not resolve any theme, keeping the configured one.', 'ui');
            return null;
        }

        $parentPaths = [];

        // getAllVariables() merges the core variables before any theme file, so an edit
        // to them has to invalidate the state just like an edit to a theme's own ones
        $mtimes = [
            Yii::getAlias('@humhub/resources') => self::getVariablesMtime(Yii::getAlias('@humhub/resources')),
            $theme->getBasePath() => self::getVariablesMtime($theme->getBasePath()),
        ];

        foreach (ThemeHelper::getThemeTree($theme, false) as $parent) {
            $parentPaths[] = $parent->getBasePath();
            $mtimes[$parent->getBasePath()] = self::getVariablesMtime($parent->getBasePath());
        }

        return [
            'rev' => Yii::$app->systemRevision->getPublicSignature(),
            // The Custom SCSS feeds straight into `vars`, so watch it directly rather than
            // relying on DesignSettingsForm::save() happening to call Theme::activate()
            'scss' => crc32((string)Yii::$app->settings->get('themeCustomScss')),
            // Always the requested name, never the resolved one: isValid() compares this
            // against the `theme` setting, so storing anything else - including the core
            // theme a fallback landed on - makes the state stale on the very next request
            'name' => $name,
            'path' => $theme->getBasePath(),
            'parents' => $parentPaths,
            'mtimes' => $mtimes,
            'vars' => self::buildVariables($theme),
        ];
    }

    /**
     * The Custom SCSS is admin-supplied and may not compile. A broken snippet must not
     * take the whole site down, so the variables are rebuilt without it.
     */
    private static function buildVariables(Theme $theme): array
    {
        try {
            return ThemeHelper::getAllVariables($theme);
        } catch (Throwable $e) {
            Yii::error('Could not read the variables of the custom SCSS: ' . $e->getMessage(), 'ui');
            return ThemeHelper::getAllVariables($theme, false);
        }
    }

    private static function getVariablesMtime(string $basePath): ?int
    {
        $file = $basePath . DIRECTORY_SEPARATOR . 'scss' . DIRECTORY_SEPARATOR . 'variables.scss';

        return is_file($file) ? filemtime($file) : null;
    }
}
