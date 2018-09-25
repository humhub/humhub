<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\components;

use humhub\assets\AppAsset;
use humhub\libs\BaseSettingsManager;
use humhub\modules\ui\view\helpers\ThemeHelper;
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
 * - Using less variables
 * Using this theme class you can also access all LESS style variables of the current theme.
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
 */
class Theme extends BaseTheme
{
    /**
     * @var string the name of the theme
     */
    public $name;

    /**
     * @inheritdoc
     */
    private $_baseUrl = null;

    /**
     * @var boolean indicates that resources should be published via assetManager
     */
    public $publishResources = false;

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
        if ($this->_baseUrl !== null) {
            return $this->_baseUrl;
        }

        $this->_baseUrl = ($this->publishResources) ? $this->publishResources() : rtrim(Yii::getAlias('@web/themes/' . $this->name), '/');
        return $this->_baseUrl;
    }

    /**
     * Registers theme css and resources to the view
     * @param bool $includeParents also register parent themes
     */
    public function register($includeParents = true)
    {
        if ($includeParents) {
            foreach (array_reverse($this->getParents()) as $parent) {
                /** @var Theme $parent */
                $parent->register(false);
            }
        }

        if (file_exists($this->getBasePath() . '/css/theme.css')) {
            $mtime = filemtime($this->getBasePath() . '/css/theme.css');
            Yii::$app->view->registerCssFile($this->getBaseUrl() . '/css/theme.css?v=' . $mtime, ['depends' => AppAsset::class]);
        }

    }


    /**
     * Activate this theme
     */
    public function activate()
    {
        $this->publishResources(true);
        $this->variables->flushCache();
        Yii::$app->settings->set('theme', $this->getBasePath());
        Yii::$app->settings->delete('themeParents');
    }

    /**
     * Checks whether the Theme is currently active.
     *
     * @return boolean
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
     * Publishs theme assets (e.g. images or css)
     *
     * @param boolean|null $force
     * @return string url of published resources
     */
    public function publishResources($force = null)
    {
        if ($force === null) {
            $force = (YII_DEBUG);
        }

        $published = Yii::$app->assetManager->publish(
            $this->getBasePath(), ['forceCopy' => $force, 'except' => ['views/']]
        );

        return $published[1];
    }

    /**
     * Returns the value of a given theme variable
     *
     * @since 1.2
     * @param string $key the variable name
     * @return string the variable value
     */
    public function variable($key, $default = null)
    {
        return $this->variables->get($key, $default);
    }

    /**
     * Returns the base/parent themes of this theme.
     * The parent is specified in the LESS Variable file as variable "baseTheme".
     *
     * @see ThemeVariables
     * @return Theme[] the theme parents
     */
    public function getParents()
    {
        if ($this->parents !== null) {
            return $this->parents;
        }

        if ($this->isActive() && BaseSettingsManager::isDatabaseInstalled()) {
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

                if (BaseSettingsManager::isDatabaseInstalled()) {
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
