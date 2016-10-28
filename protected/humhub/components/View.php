<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use yii\helpers\Html;

/**
 * @inheritdoc
 */
class View extends \yii\web\View
{

    private $_pageTitle;
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

    /**
     * Registers a Javascript variable
     * 
     * @param string $name
     * @param string $value
     */
    public function registerJsVar($name, $value)
    {
        $jsCode = "var " . $name . " = '" . addslashes($value) . "';\n";
        $this->registerJs($jsCode, View::POS_HEAD, $name);
    }
    
    public function registerJsConfig($module, $params = null) {
        if(is_array($module)) {
            foreach($module as $moduleId => $value) {
                $this->registerJsConfig($moduleId, $value);
            }
            return;
        }
        
        if(isset($this->jsConfig[$module])) {
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
        $this->endBody();
        $this->endPage(true);

        echo $content;

        return ob_get_clean();
    }

    /**
     * @inheritdoc
     */
    public function registerJsFile($url, $options = array(), $key = null)
    {
        parent::registerJsFile($this->addCacheBustQuery($url), $options, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerCssFile($url, $options = array(), $key = null)
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

    /**
     * @inheritdoc
     */
    public function endBody()
    {
        $this->registerJs("humhub.config.set(".json_encode($this->jsConfig).");", View::POS_BEGIN, 'jsConfig');
        
        if (Yii::$app->request->isAjax) {
            return parent::endBody();
        }
        
        echo \humhub\widgets\LayoutAddons::widget();
   
        // Add Layout Addons
        return parent::endBody();
    }

}
