<?php

namespace humhub\components\assets;

use humhub\assets\CoreBundleAsset;
use humhub\components\View;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\web\AssetBundle as BaseAssetBundle;

/**
 * This base asset bundle class adds some additional properties as well ass default loading behavior for HumHub assets.
 *
 * Some of the properties will affect settings as `jsOptions` or `publishOptions`. Note that settings defined
 * directly within those option arrays will have priority over the settings defined as property.
 *
 * For example:
 *
 * ```
 * public $position = View::POS_HEAD;
 * ```
 *
 * will be overwritten by
 *
 * ```
 * public $jsOptions = [
 *     'position' => VIEW::POS_END;
 * ]
 * ```
 *
 * ### Production vs Debug
 *
 * The additional `$jsProd` property allows the definition of production assets which will be used in production and
 * acceptance test environments e.g. for minified scripts and stylesheets. This serves to facilitate development without the need
 * to rebuild assets while development and also allows a preview of all un-minified/combined assets.
 *
 *  - `jsProd` can be used to define script assets used in production, if not set `$js` settings will be used instead
 *  - `cssProd` can be used to define stylesheet assets used in production, if not set `$css` settings will be used instead
 *  - `forceProductionAssets` property can be used while development in order to force production asset usage also in debug mode
 *
 * The `forceCopy` property can be used to set the forceCopy publishOption.
 *
 * > Note: This asset bundle prevents `forceCopy` on production environments due to performance issues,
 * but only if not directly set in publishOptions array
 *
 * ### Default script loading behavior
 *
 * The default script settings are described in the following:
 *
 *  - `defer` script loading is active by default and can be activated by the `$defer` property
 *  - `async` script loading is not active by default and can be activated by the `$async` property
 *  - `position` if no jsOption position is set and either `async` or `defer` is set (default) `View::POS_HEAD` is used
 *
 * @package humhub\components
 */
class AssetBundle extends BaseAssetBundle
{
    /**
     * Default publish options for all bundles publishing the whole `@humhub/resources`
     * tree. The AssetManager keys the published copy by source path, so every bundle
     * sharing this sourcePath must publish with identical options — whichever bundle
     * publishes first defines the copied file set.
     *
     * `build/` is deliberately NOT excluded here: the compiled `css/humhub-app.css`
     * references its contents at runtime via relative `url(../build/<hash>/...)`
     * (e.g. FontAwesome, OpenSans, jplayer), so it must always be published alongside
     * the rest of the tree. `scss/` only contains sources, never referenced at runtime.
     */
    public const HUMHUB_RESOURCES_PUBLISH_OPTIONS = [
        'except' => [
            'scss/',
            '/vue/', // Vue SFC compile-time sources, see docs/develop/ui-js-vuejs.md
            '.gitignore',
        ],
    ];

    /**
     * @var bool can be used to force production asset usage while testing
     */
    protected static $forceProductionAssets = false;

    /**
     * @var [] can be defined to use a different set of js assets in production mode e.g. for minified/combined scripts.
     */
    public $jsProd;

    /**
     * @var [] can be defined to use a different set of css assets in production mode e.g. for minified/combined stylesheets.
     */
    public $cssProd;

    /**
     * @var bool if true (default) the `$jsOptions['defer']` will be activated only if this jsOption is not explicitly set.
     */
    public $defer = true;

    /**
     * @var bool if true the `$jsOptions['async']` will be activated only if this jsOption is not explicitly set.
     */
    public $async = false;

    /**
     * @var int can be used to set `$publishOptions['position']`. This property will only have an affect if the publishOption
     * is not already set explicitly.
     */
    public $jsPosition;

    /**
     * @var bool can be used to set `$publishOptions['forceCopy']`. This property will be ignored in production environment
     */
    public $forceCopy = false;

    /**
     * @var array may contain scripts or stylesheets which should be pre-loaded by a `<link rel="preload">` tag
     */
    public $preload = [];

    /**
     * @var array|false default dependencies not required to be mentioned in $depends. Normally only deactivated in some
     * core assets.
     */
    public $defaultDepends = [
        CoreBundleAsset::class,
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $useProdAssets = static::useProductionAssets();

        if ($this->isAsync()) {
            $this->defer = false;
        }

        if ($this->jsProd !== null && $useProdAssets) {
            $this->js = $this->jsProd;
        }

        if ($this->cssProd !== null && $useProdAssets) {
            $this->css = $this->cssProd;
        }

        if (!$this->isAsync() && $this->isDefer()) {
            $this->jsOptions['defer'] = 'defer';
        } elseif ($this->isAsync()) {
            $this->jsOptions['async'] = 'async';
        }

        $this->jsOptions['position'] = $this->getJsPosition();

        if (
            // `parent::init()` above already resolved `sourcePath` from its alias
            // to an absolute path, so it must be compared against the resolved
            // alias rather than the literal `@humhub/resources` string.
            $this->sourcePath === rtrim(Yii::getAlias('@humhub/resources'), '/\\')
            && !isset($this->publishOptions['only'])
            && !isset($this->publishOptions['except'])
        ) {
            $this->publishOptions += self::HUMHUB_RESOURCES_PUBLISH_OPTIONS;
        } elseif (
            $this->sourcePath !== null
            && !isset($this->publishOptions['only'])
            && !isset($this->publishOptions['except'])
        ) {
            // Vue SFC sources are compile-time input only, never published
            // (see docs/develop/ui-js-vuejs.md). Applied uniformly so bundles
            // sharing a sourcePath keep identical publish options.
            $this->publishOptions['except'] = ['/vue/'];
        }

        if (!$useProdAssets && $this->forceCopy && !isset($this->publishOptions['forceCopy'])) {
            $this->publishOptions['forceCopy'] = true;
        } elseif (!isset($this->publishOptions['forceCopy'])) {
            $this->publishOptions['forceCopy'] = false;
        }

        if ((!(Yii::$app instanceof Application) || !Yii::$app->request->isAjax) && !empty($this->dependsDefault)) {
            $this->depends[] = ArrayHelper::merge($this->depends, $this->defaultDepends);
        }
    }

    /**
     * @return int determines the $jsOptions['position'] value. By default the position will be `View::POS_HEAD` if
     * the `defer` or `async` is set. Otherwise `View::POS_END` is returned as default if no `$this->publishOptions['position']`,
     * `$jsPosition` given.
     */
    protected function getJsPosition()
    {
        if (isset($this->publishOptions['position'])) {
            return $this->publishOptions['position'];
        }

        if ($this->jsPosition !== null) {
            return $this->jsPosition;
        }

        if ($this->isAsync() || $this->isDefer()) {
            return View::POS_HEAD;
        }

        return View::POS_END;
    }

    /**
     * @return bool whether or not the `async` script option is set
     */
    protected function isAsync()
    {
        return $this->async || isset($this->jsOptions['async']);
    }

    /**
     * @return bool whether or not the `defer` script option is set
     */
    protected function isDefer()
    {
        return $this->defer || isset($this->jsOptions['defer']);
    }

    /**
     * @return bool whether or not to use the production assets
     */
    protected static function useProductionAssets()
    {
        return static::$forceProductionAssets || YII_ENV_PROD || YII_ENV_TEST;
    }
}
