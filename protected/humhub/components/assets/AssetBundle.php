<?php


namespace humhub\components\assets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\web\AssetBundle as BaseAssetBundle;
use humhub\assets\CoreBundleAsset;
use humhub\modules\ui\view\components\View;


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
        CoreBundleAsset::class
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $useProdAssets = static::useProductionAssets();

        if($this->isAsync()) {
            $this->defer = false;
        }

        if($this->jsProd !== null && $useProdAssets) {
            $this->js = $this->jsProd;
        }

        if($this->cssProd !== null && $useProdAssets) {
            $this->css = $this->cssProd;
        }

        if(!$this->isAsync() && $this->isDefer()) {
            $this->jsOptions['defer'] = 'defer';
        } else if($this->isAsync()) {
            $this->jsOptions['async'] = 'async';
        }

        $this->jsOptions['position'] = $this->getJsPosition();

        if(!$useProdAssets && $this->forceCopy && !isset($this->publishOptions['forceCopy'])) {
            $this->publishOptions['forceCopy'] = true;
        } else if(!isset($this->publishOptions['forceCopy'])) {
            $this->publishOptions['forceCopy'] = false;
        }

        if((!(Yii::$app instanceof Application) || !Yii::$app->request->isAjax) && !empty($this->dependsDefault)) {
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
        if(isset($this->publishOptions['position'])) {
            return $this->publishOptions['position'];
        }

        if($this->jsPosition !== null) {
            return $this->jsPosition;
        }

        if($this->isAsync() || $this->isDefer()) {
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
