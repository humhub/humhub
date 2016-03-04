<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;

/**
 * @inheritdoc
 */
class View extends \yii\web\View
{

    private $_pageTitle;

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

}
