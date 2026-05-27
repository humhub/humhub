<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\assets\CoreBundleAsset;
use humhub\helpers\ThemeHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Theme as BaseTheme;
use yii\helpers\FileHelper;

/**
 * Theme represents a HumHub theme.
 *
 * - Overwrite views
 * When [[View]] renders a view file, it will check the [[View::theme|active theme]]
 * to see if there is a themed version of the view file exists. If so, the themed version will be rendered instead.
 * See [[ThemeViews]] for more details.
 *
 * - Using scss variables
 * Using this theme class you can also access all SCSS style variables of the current theme.
 *
 * Examples:
 *
 * ```php
 * $primaryColorCode = Yii::$app->view->theme->variable('primary');
 * $isFluid = (boolean) Yii::$app->view->theme->variable('isFluid');
 * ```
 *
 * See [[ThemeVariables]] for more details.
 *
 * @since 1.3
 * @inheritdoc
 *
 * @property-read string $publishedResourcesPath
 */
class Theme extends BaseTheme
{
    public const EVENT_AFTER_THEME_ACTIVATE = 'afterThemeActivate';

    /**
     * @since 1.18
     */
    public const CORE_THEME_NAME = 'HumHub';

    /**
     * @var string the name of the theme
     */
    public $name;

    /**
     * @inheritdoc
     */
    private $_baseUrl = null;
    private $_basePath = null;

    /**
     * @var bool indicates that resources should be published via assetManager
     */
    public $publishResources = true;

    /**
     * @var ThemeVariables
     */
    public $variables = null;

    /**
     * @var ThemeViews
     */
    public $views = null;

    /**
     * @var Theme[] the parent themes
     */
    protected $parents;

    /**
     * @var bool
     */
    private bool $pathMapInitialized = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->getBasePath())) {
            throw new InvalidConfigException('The "basePath" property must be set.');
        }

        $this->variables = new ThemeVariables(['theme' => $this]);
        $this->views = new ThemeViews(['theme' => $this]);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getBaseUrl()
    {
        if (!$this->_baseUrl) {
            $this->_baseUrl = $this->publishResources ? $this->publishResources() : rtrim(Yii::getAlias('@web/themes/' . $this->name), '/');
        }

        return $this->_baseUrl;
    }

    /**
     * Registers theme css and resources to the view
     */
    public function register()
    {
        if (Yii::$app->request->isAjax) {
            return;
        }

        // Get bas URL and make sure resources are published
        $baseUrl = $this->getBaseUrl();

        // Build CSS if not already done
        $cssFile = $this->getPublishedResourcesPath() . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'theme.css';
        if (!Yii::$app->assetManager->fileExists($cssFile)) {
            $buildResult = ThemeHelper::buildCss();
            // If SCSS error in a Child Theme or Custom SCSS
            if ($buildResult !== true) {
                // Fallback to HumHub theme with no Custom SCSS for a minimal working styling
                $coreTheme = ThemeHelper::getThemeByName(self::CORE_THEME_NAME);
                ThemeHelper::buildCss($coreTheme, false);
                $coreTheme->activate();
                Yii::$app->response->refresh();
            }
        }

        $mtime = file_exists($cssFile) ? filemtime($cssFile) : '';
        Yii::$app->view->registerCssFile($baseUrl . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'theme.css?v=' . $mtime, ['depends' => CoreBundleAsset::class]);
    }


    /**
     * Activate this theme
     */
    public function activate()
    {
        Yii::$app->settings->set('theme', $this->getBasePath());
        Yii::$app->settings->delete('themeParents');

        // Publish resources to assets (the CSS will be automatically generated on layout rendering)
        $this->publishResources(true);

        $this->trigger(static::EVENT_AFTER_THEME_ACTIVATE, new Event());
    }

    /**
     * Checks whether the Theme is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        return ($this->name === Yii::$app->view->theme->name);
    }

    /**
     * @inheritdoc
     *
     * In addition to the standard Yii2 directory-based [[pathMap]] entries, this
     * implementation also accepts per-file overrides. Any [[pathMap]] key ending
     * in `.php` is treated as an exact source-file path; the value (or values,
     * if an array) is the override file used in its place. Both keys and values
     * may use Yii path aliases.
     *
     * Example:
     *
     * ```php
     * 'pathMap' => [
     *     // per-file override
     *     '@humhub/modules/user/views/auth/login.php' => '@config/views/login.php',
     *     // directory override (Yii2 default behaviour)
     *     '@humhub/modules/space/widgets/views' => '@config/views/space-widgets',
     * ],
     * ```
     */
    public function applyTo($path)
    {
        $this->initPathMap();

        $resolved = $this->resolveFromPathMap($path);
        if ($resolved !== null) {
            return $resolved;
        }

        $translated = $this->views->translate($path);
        if ($translated !== null) {
            return $translated;
        }

        // Check if a parent theme may translate this view
        foreach ($this->getParents() as $theme) {
            $translated = $theme->views->translate($path);
            if ($translated !== null) {
                return $translated;
            }
        }

        return $path;
    }

    /**
     * Resolves a view file through [[pathMap]], supporting both per-file
     * (`.php` keys, exact match) and directory-prefix entries in the same map.
     * Returns the override file path if one matches and exists, otherwise null.
     *
     * @since 1.19
     */
    private function resolveFromPathMap(string $path): ?string
    {
        if (empty($this->pathMap)) {
            return null;
        }

        $normalized = FileHelper::normalizePath($path);

        foreach ($this->pathMap as $from => $tos) {
            $fromResolved = FileHelper::normalizePath(Yii::getAlias($from));

            // Per-file entry: keyed by an exact .php path
            if (str_ends_with($fromResolved, '.php')) {
                if ($fromResolved !== $normalized) {
                    continue;
                }
                foreach ((array) $tos as $to) {
                    $resolved = Yii::getAlias($to);
                    if (is_file($resolved)) {
                        return $resolved;
                    }
                }
                continue;
            }

            // Directory entry: prefix substitution (Yii2 default behaviour)
            $fromDir = $fromResolved . DIRECTORY_SEPARATOR;
            if (strpos($normalized, $fromDir) !== 0) {
                continue;
            }
            $rest = substr($normalized, strlen($fromDir));
            foreach ((array) $tos as $to) {
                $file = FileHelper::normalizePath(Yii::getAlias($to)) . DIRECTORY_SEPARATOR . $rest;
                if (is_file($file)) {
                    return $file;
                }
            }
        }

        return null;
    }

    /**
     * Ensures the default `@humhub/views` mapping is present in [[pathMap]],
     * merging with any user-supplied entries so that explicit overrides take
     * precedence over the active theme's view directory.
     */
    protected function initPathMap()
    {
        if ($this->pathMapInitialized) {
            return;
        }
        $this->pathMapInitialized = true;

        if ($this->pathMap === null) {
            $this->pathMap = [];
        }

        $defaultViewPaths = [$this->getBasePath() . '/views'];
        foreach ($this->getParents() as $theme) {
            $defaultViewPaths[] = $theme->getBasePath() . '/views';
        }

        $existing = (array) ($this->pathMap['@humhub/views'] ?? []);
        $this->pathMap['@humhub/views'] = array_merge($existing, $defaultViewPaths);
    }

    /**
     * @return string Path of published resources
     *
     * @since 1.18
     */
    public function getPublishedResourcesPath(): string
    {
        if (!$this->_basePath) {
            $publishedPath = Yii::$app->assetManager->getPublishedPath($this->getBasePath());
            $this->_basePath = $publishedPath . '/resources';
        }
        return $this->_basePath;
    }

    /**
     * Published theme assets (e.g. images or css)
     *
     * @param bool $force publish of resources
     * @return string URL of published resources
     */
    public function publishResources(bool $force = false)
    {
        $published = Yii::$app->assetManager->publish(
            $this->getBasePath(),
            ['forceCopy' => $force, 'except' => ['views/', 'scss/']],
        );

        return $published[1];
    }

    /**
     * Returns the value of a given theme variable
     *
     * @param string $key the variable name
     *
     * @return string|null the variable value
     * @since 1.2
     *
     */
    public function variable($key, $default = null)
    {
        return $this->variables->get($key, $default);
    }

    /**
     * Returns the base/parent themes of this theme.
     * The parent is specified in the LESS Variable file as variable "baseTheme".
     *
     * @return Theme[] the theme parents
     * @see ThemeVariables
     */
    public function getParents()
    {
        if ($this->parents !== null) {
            return $this->parents;
        }

        if ($this->isActive() && Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            $this->parents = static::getActiveParents();
        }

        if ($this->parents === null) {
            $this->parents = ThemeHelper::getThemeTree($this, false);

            if ($this->isActive()) {
                // Store parent path of currently active theme as settings
                // This avoids theme paths lookups
                $parentPaths = [];
                foreach ($this->parents as $theme) {
                    $parentPaths[] = $theme->getBasePath();
                }

                if (Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
                    Yii::$app->settings->setSerialized('themeParents', $parentPaths);
                }
            }
        }

        return $this->parents;
    }

    /**
     * Returns the parent themes of the currently active theme.
     * These parents are stored in the setting variable "themeParents" for faster lookup.
     *
     * @return Theme[]|null the themes or null
     */
    protected static function getActiveParents()
    {
        $parentPaths = Yii::$app->settings->getSerialized('themeParents');

        if (!is_array($parentPaths)) {
            return null;
        }

        $parents = [];
        foreach ($parentPaths as $parentPath) {
            $theme = ThemeHelper::getThemeByPath($parentPath);
            if ($theme === null) {
                Yii::$app->settings->delete('themeParents');
                Yii::error('Could not load stored theme parent! - Deleted parent path.', 'ui');
                return null;
            }
            $parents[] = $theme;
        }
        return $parents;
    }
}
