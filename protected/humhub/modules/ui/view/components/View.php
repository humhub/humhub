<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\components;

use humhub\assets\AppAsset;
use humhub\assets\CoreBundleAsset;
use humhub\components\assets\AssetBundle;
use humhub\libs\Html;
use humhub\modules\web\pwa\widgets\LayoutHeader;
use humhub\modules\web\pwa\widgets\SiteIcon;
use humhub\widgets\CoreJsConfig;
use humhub\widgets\LayoutAddons;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Class View
 *
 * @property Theme $theme the Theme component
 * @inheritdoc
 */
class View extends \yii\web\View
{
    /**
     * the id of the sidebar block
     */
    const BLOCK_SIDEBAR = 'sidebar';

    /**
     * @var string page title
     * @see View::setPageTitle
     */
    private $_pageTitle;

    /**
     * @var array Contains javascript configurations, which will be appended to the view.
     * @see View::endBody
     */
    private $jsConfig = [];

    /**
     * @var array contains static core style and script assets which should be pre-loaded as `<link rel="preload">` element.
     */
    protected static $preload = [
        'theme.css',
        'bootstrap.css',
    ];

    /**
     * @var array contains already pre-loaded asset urls.
     */
    private static $preloaded = [];

    /**
     * A viewContext is a simple string value which usually is set by a controller to define the context of a resulting view.
     * If a viewContext is set, it will be sent to the client in case of pjax and full page loads. The HumHub Client API
     * will then use the viewContext in all ajax (non pjax) requests as HTTP request header `HUMHUB-VIEW-CONTEXT`
     * until the next pjax or full page load.
     *
     * The viewContext is usually used to influence the view, e.g. a viewContext 'modal' indicates that the resulting view
     * will be contained in a modal, a 'dashboard' context will indicate that the ajax request was sent from the dashboard.
     *
     * A controller usually sets the viewContext within the init function e.g.:
     *
     * ```php
     * public function init()
     * {
     *   parent::init();
     *   $this->view->setViewContext('dashboard');
     * }
     * ```
     *
     * Note: This variable won't contain the value of the request header but is only used to set the client viewContext.
     * Use `Yii::$app->request->getViewContext()` to determine the active viewContext of a request.
     *
     * @var string defines a view context used in ajax requests
     * @since 1.7
     */
    private static $viewContext;

    /**
     * Sets current page title
     *
     * @param string $title
     */
    public function setPageTitle($title)
    {
        $this->_pageTitle = $title;
    }

    /**
     * Returns current page title
     *
     * @return string the page title
     */
    public function getPageTitle()
    {
        return (($this->_pageTitle) ? $this->_pageTitle . " - " : '') . Yii::$app->name;
    }


    /**
     * Registers the javascript module configuration for one or multiple modules.
     *
     * ```javascript
     * $this->registerJsConfig('moduleId', [
     *     'someKey' => 'someValue'
     * ]);
     *
     * // or
     *
     * $this->registerJsConfig([
     *      'module1' => [
     *         'someKey' => 'someValue'
     *      ],
     *      'module2' => [
     *          'someKey' => 'anotherValue';
     *      ]
     * ]);
     * ```
     * @param $module
     * @param null $params
     */
    public function registerJsConfig($module, $params = null)
    {
        if (is_array($module)) {
            foreach ($module as $moduleId => $value) {
                $this->registerJsConfig($moduleId, $value);
            }
            return;
        }

        if (isset($this->jsConfig[$module])) {
            $this->jsConfig[$module] = ArrayHelper::merge($this->jsConfig[$module], $params);
        } else {
            $this->jsConfig[$module] = $params;
        }
    }

    /**
     * Renders a string as Ajax including assets.
     *
     * @param string $content
     * @return string Rendered content
     */
    public function renderAjaxContent($content)
    {
        ob_start();
        ob_implicit_flush(false);

        $this->beginPage();
        $this->head();
        $this->beginBody();
        echo $content;
        $this->endBody();
        $this->endPage(true);

        return ob_get_clean();
    }

    /**
     * Renders a string as Ajax including assets without end page so it can be called several times.
     *
     * @param string $content
     * @return string Rendered content
     */
    public function renderAjaxPartial(string $content): string
    {
        ob_start();
        ob_implicit_flush(false);

        $this->beginPage();
        $this->head();
        $this->beginBody();
        echo $content;
        $this->endBody();
        $this->renderEndPage();

        return ob_get_clean();
    }

    /**
     * Render of ending page with all attached assets.
     * This method doesn't mark a page is ended in order to allow to call it several times.
     */
    private function renderEndPage()
    {
        $this->trigger(self::EVENT_END_PAGE);

        $content = ob_get_clean();

        echo strtr($content, [
            self::PH_HEAD => $this->renderHeadHtml(),
            self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PH_BODY_END => $this->renderBodyEndHtml(true),
        ]);

        $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function renderAjax($view, $params = [], $context = null)
    {
        $viewFile = $this->findViewFile($view, $context);

        ob_start();
        ob_implicit_flush(false);

        $this->beginPage();
        $this->head();
        $this->beginBody();
        echo $this->renderFile($viewFile, $params, $context);
        $this->endBody();
        $this->endPage(true);

        return ob_get_clean();
    }

    /**
     * @inheritDoc
     */
    public function registerAssetBundle($name, $position = null)
    {
        $bundle = parent::registerAssetBundle($name, $position);

        if($bundle instanceof AssetBundle && !empty($bundle->preload)) {
            static::$preload = ArrayHelper::merge(static::$preload, $bundle->preload);
        }

        return $bundle;
    }

    protected function registerAssetFiles($name)
    {
        if(Yii::$app->request->isAjax
            && (in_array($name, AppAsset::STATIC_DEPENDS)
                || in_array($name, CoreBundleAsset::STATIC_DEPENDS)
                || in_array($name, [AppAsset::BUNDLE_NAME, CoreBundleAsset::BUNDLE_NAME]))) {
            return;
        }

       return parent::registerAssetFiles($name);
    }

    /**
     * @inheritdoc
     */
    protected function renderBodyBeginHtml()
    {
        $lines = [];
        if (!empty($this->js[self::POS_BEGIN])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_BEGIN]));
        }

        $this->js[self::POS_BEGIN] = null;

        return parent::renderBodyBeginHtml() . (empty($lines) ? '' : implode("\n", $lines));
    }

    /**
     * @inheritdoc
     */
    protected function renderBodyEndHtml($ajaxMode)
    {
        $lines = [];

        if (!empty($this->jsFiles[self::POS_END])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_END]);
        }

        if ($ajaxMode) {
            $scripts = [];
            if (!empty($this->js[self::POS_END])) {
                $scripts[] = implode("\n", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $scripts[] = implode("\n", $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $scripts[] = implode("\n", $this->js[self::POS_LOAD]);
            }
            if (!empty($scripts)) {
                $lines[] = Html::script(implode("\n", $scripts));
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = Html::script(implode("\n", $this->js[self::POS_END]));
            }
            if (!empty($this->js[self::POS_READY])) {
                $js = "jQuery(function ($) {\n" . implode("\n", $this->js[self::POS_READY]) . "\n});";
                $lines[] = Html::script($js);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $js = "jQuery(window).on('load', function () {\n" . implode("\n", $this->js[self::POS_LOAD]) . "\n});";
                $lines[] = Html::script($js);
            }
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * @inheritdoc
     */
    protected function renderHeadHtml()
    {
        if (!Yii::$app->request->isAjax) {
            SiteIcon::registerMetaTags($this);
            LayoutHeader::registerHeadTags($this);
            parent::registerCsrfMetaTags();
        }

        $lines = [];

        if (!empty($this->js[self::POS_HEAD])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_HEAD]));
        }

        $this->js[self::POS_HEAD] = null;

        return parent::renderHeadHtml(). (empty($lines) ? '' : implode("\n", $lines));
    }

    /**
     * @inheritdoc
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $cacheBustedUrl = $this->addCacheBustQuery($url);
        foreach (static::$preload as $fileName) {
            if(strpos($url,$fileName)) {
                $this->registerPreload($cacheBustedUrl, 'script');
            }
        }

        Html::setNonce($options);
        parent::registerJsFile($this->addCacheBustQuery($url), $options, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $cacheBustedUrl = $this->addCacheBustQuery($url);
        foreach (static::$preload as $fileName) {
            if(strpos($url,$fileName)) {
                $this->registerPreload($cacheBustedUrl, 'style');
            }
        }

        parent::registerCssFile($cacheBustedUrl, $options, $key);
    }

    protected function registerPreload($url, $as)
    {
        if(!in_array($url, static::$preloaded, true)) {
            $this->registerLinkTag((['rel' => 'preload', 'as' => $as, 'href' => $url]));
            static::$preloaded[] = $url;
        }
    }

    /**
     * Adds cache bust query string to given url if no query is present
     *
     * @param string $url
     * @return string the URL with cache bust paramter
     */
    protected function addCacheBustQuery($url)
    {
        if (strpos($url, '?') === false) {
            $file = str_replace('@web', '@webroot', $url);
            if (substr($file, 0, 1) === '/') {
                $file = '@webroot' . $file;
            }

            $file = Yii::getAlias($file);
            if (file_exists($file)) {
                $url .= '?v=' . filemtime($file);
            } else {
                $url .= '?v=' . urlencode(Yii::$app->version);
            }
        }

        return $url;
    }

    public function setStatusMessage($type, $message)
    {
        Yii::$app->getSession()->setFlash('view-status', [$type => $message]);
    }

    public function saved()
    {
        $this->success(Yii::t('base', 'Saved'));
    }

    public function info($message)
    {
        $this->setStatusMessage('info', $message);
    }

    public function success($message)
    {
        $this->setStatusMessage('success', $message);
    }

    public function error($message)
    {
        $this->setStatusMessage('error', $message);
    }

    public function warn($message)
    {
        $this->setStatusMessage('warn', $message);
    }

    /**
     * @inheritdoc
     */
    public function endBody()
    {
        // Flush jsConfig needed for all types of requests (including pjax/ajax)
        $this->flushJsConfig();

        // In case of pjax we have to add the title manually, pjax will remove this node
        if (Yii::$app->request->isPjax) {
            echo '<title>' . $this->getPageTitle() . '</title>';
        }

        if (Yii::$app->params['installed']) {
            if (Yii::$app->getSession()->hasFlash('view-status')) {
                $viewStatus = Yii::$app->getSession()->getFlash('view-status');
                $type = strtolower(key($viewStatus));
                $value = Html::encode(array_values($viewStatus)[0]);
                $value = str_replace('&quot;', '', $value);
                $value = trim($value);
                $this->registerJs('humhub.modules.ui.status.' . $type . '("' . $value . '")', View::POS_END, 'viewStatusMessage');
            }

            if (Yii::$app->session->hasFlash('executeJavascript')) {
                $position = self::POS_READY;
                if (Yii::$app->session->hasFlash('executeJavascriptPosition')) {
                    $position = Yii::$app->session->hasFlash('executeJavascriptPosition');
                }
                $this->registerJs(Yii::$app->session->getFlash('executeJavascript'), $position);
            }
        }

        if (Yii::$app->request->isPjax) {
            $this->registerViewContext();
            echo LayoutAddons::widget();
            $this->flushJsConfig();
        }

        if (Yii::$app->request->isAjax) {
            return parent::endBody();
        }

        // Since the JsConfig accesses user queries it fails before installation.
        if (Yii::$app->params['installed']) {
            CoreJsConfig::widget();
        }

        // Add LayoutAddons and jsConfig registered by addons
        echo LayoutAddons::widget();
        $this->registerViewContext();
        $this->flushJsConfig();

        return parent::endBody();
    }

    /**
     * Registers the client viewContext.
     */
    private function registerViewContext()
    {
        if(!empty(static::$viewContext)) {
            $this->registerJs('humhub.modules.ui.view.setViewContext("'.static::$viewContext.'")', View::POS_END, 'viewContext');
        }
    }

    /**
     * Writes the currently registered jsConfig entries and flushes the the config array.
     *
     * @param string $key see View::registerJs
     * @since v1.2
     */
    protected function flushJsConfig($key = null)
    {
        if (!empty($this->jsConfig)) {
            $this->registerJs("humhub.config.set(" . json_encode($this->jsConfig) . ");", View::POS_BEGIN, $key);
            $this->jsConfig = [];
        }
    }

    /**
     * @return bool checks if a sidebar exists
     */
    public function hasSidebar()
    {
        return (isset($this->blocks[static::BLOCK_SIDEBAR]) && !ctype_space($this->blocks[static::BLOCK_SIDEBAR]));
    }


    /**
     * Returns the sidebar which is stored in the block called 'sidebar'
     *
     * @return string returns the rendered sidebar
     */
    public function getSidebar()
    {
        if ($this->hasSidebar()) {
            return $this->blocks[static::BLOCK_SIDEBAR];
        }

        return '';
    }

    /**
     * Sets the currently active viewContext.
     * @param $vctx
     * @since 1.7
     */
    public function setViewContext($vctx)
    {
        static::$viewContext = $vctx;
    }

}
