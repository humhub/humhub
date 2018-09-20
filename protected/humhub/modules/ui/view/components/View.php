<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\components;

use humhub\libs\Html;
use humhub\widgets\CoreJsConfig;
use humhub\widgets\LayoutAddons;
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


    private $_pageTitle;

    /**
     * Holds all javascript configurations, which will be appended to the view.
     * @see View::endBody
     * @var array
     */
    private $jsConfig = [];

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


    public function registerJsConfig($module, $params = null)
    {
        if (is_array($module)) {
            foreach ($module as $moduleId => $value) {
                $this->registerJsConfig($moduleId, $value);
            }
            return;
        }

        if (isset($this->jsConfig[$module])) {
            $this->jsConfig[$module] = yii\helpers\ArrayHelper::merge($this->jsConfig[$module], $params);
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
        $this->unregisterAjaxAssets();
        $this->endBody();
        $this->endPage(true);

        return ob_get_clean();
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
        $this->unregisterAjaxAssets();
        $this->endBody();

        $this->endPage(true);

        return ob_get_clean();
    }

    /**
     * Unregisters standard assets on ajax requests
     */
    protected function unregisterAjaxAssets()
    {
        unset($this->assetBundles['yii\bootstrap\BootstrapAsset']);
        unset($this->assetBundles['yii\web\JqueryAsset']);
        unset($this->assetBundles['yii\web\YiiAsset']);
        unset($this->assetBundles['all']);
    }

    /**
     * @inheritdoc
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        parent::registerJsFile($this->addCacheBustQuery($url), $options, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        parent::registerCssFile($this->addCacheBustQuery($url), $options, $key);
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
            $file = Yii::getAlias($file);

            if (file_exists($file)) {
                $url .= '?v=' . filemtime($file);
            } else {
                $url .= '?v=' . urlencode(Yii::$app->version);
            }
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    protected function renderHeadHtml()
    {
        return (!Yii::$app->request->isAjax) ? Html::csrfMetaTags() . parent::renderHeadHtml() : parent::renderHeadHtml();
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
                $this->registerJs('humhub.modules.ui.status.' . $type . '("' . $value . '")', View::POS_END, 'viewStatusMessage');
            }
            if (Yii::$app->session->hasFlash('executeJavascript')) {
                $position = self::POS_READY;
                if (Yii::$app->session->hasFlash('executeJavascriptPosition')) {
                    $position = Yii::$app->session->hasFlash('executeJavascriptPosition');
                }
                $this->registerJs(Yii::$app->session->getFlash('executeJavascript'));
            }
        }

        if (Yii::$app->request->isPjax) {
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
        $this->flushJsConfig();

        return parent::endBody();
    }

    /**
     * Writes the currently registered jsConfig entries and flushes the the config array.
     *
     * @since v1.2
     * @param string $key see View::registerJs
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
        return (isset($this->blocks[static::BLOCK_SIDEBAR]));
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

}
