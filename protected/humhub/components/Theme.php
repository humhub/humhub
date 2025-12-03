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
use yii\base\Theme as BaseTheme;

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
     * @inheritdoc
     */
    public function init()
    {
        if ($this->getBasePath() == '') {
            $this->setBasePath('@webroot/themes/' . $this->name);
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
        $cssFile = $this->publishedResourcesPath . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'theme.css';
        if (!file_exists($cssFile)) {
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
     */
    public function applyTo($path)
    {
        $this->initPathMap();

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

        return parent::applyTo($path);
    }

    /**
     * Initialize the default view path map including all parent themes
     */
    protected function initPathMap()
    {
        if ($this->pathMap === null) {
            $this->pathMap = ['@humhub/views' => [$this->getBasePath() . '/views']];

            foreach ($this->getParents() as $theme) {
                $this->pathMap['@humhub/views'][] = $theme->getBasePath() . '/views';
            }
        }
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
     * @param bool|null $force
     *
     * @return string URL of published resources
     */
    public function publishResources($force = null)
    {
        if ($force === null) {
            $force = YII_DEBUG;
        }

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
