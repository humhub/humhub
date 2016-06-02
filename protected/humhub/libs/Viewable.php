<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\base\Component;
use yii\base\ViewContextInterface;

/**
 * Viewable provides view rendering support including layout and different
 * output formats (e.g. text, web or mail=.
 *
 * @since 1.1
 * @author Luke
 */
abstract class Viewable extends Component implements ViewContextInterface
{

    const OUTPUT_WEB = 'web';
    const OUTPUT_MAIL = 'mail';
    const OUTPUT_MAIL_PLAINTEXT = 'mail_plaintext';
    const OUTPUT_TEXT = 'text';

    /**
     * Name of the view, used for rendering the event
     * 
     * @var string
     */
    public $viewName = null;

    /**
     * View path
     * 
     * @var string
     */
    public $viewPath = null;

    /**
     * Layout file for web version
     *
     * @var string
     */
    protected $layoutWeb;

    /**
     * Layout file for mail version
     *
     * @var string
     */
    protected $layoutMail;

    /**
     * Layout file for mail plaintext version
     *
     * @var string
     */
    protected $layoutMailPlaintext;

    /**
     * Assambles all parameter required for rendering the view.
     * 
     * @return array all view parameter
     */
    protected function getViewParams($params = [])
    {
        $params['originator'] = $this->originator;
        $params['source'] = $this->source;
        $params['contentContainer'] = $this->container;
        $params['record'] = $this->record;
        $params['url'] = $this->getUrl();

        return $params;
    }

    /**
     * Renders the notification
     *
     * @return string
     */
    public function render($mode = self::OUTPUT_WEB, $params = [])
    {
        $viewFile = $this->getViewFile($mode);
        $viewParams = $this->getViewParams($params);

        $result = Yii::$app->getView()->renderFile($viewFile, $viewParams, $this);

        if ($mode == self::OUTPUT_TEXT) {
            return strip_tags($result);
        }

        $viewParams['content'] = $result;
        return Yii::$app->getView()->renderFile($this->getLayoutFile($mode), $viewParams, $this);
    }

    /**
     * Returns the correct view file 
     * 
     * @param string $mode the output mode
     * @return string the view file
     */
    protected function getViewFile($mode)
    {
        $viewFile = $this->getViewPath() . '/' . $this->viewName . '.php';
        $alternativeViewFile = "";

        // Lookup alternative view file based on view mode
        if ($mode == self::OUTPUT_MAIL) {
            $alternativeViewFile = $this->getViewPath() . '/mail/' . $this->viewName . '.php';
        } elseif ($mode === self::OUTPUT_MAIL_PLAINTEXT) {
            $alternativeViewFile = $this->getViewPath() . '/mail/plaintext/' . $this->viewName . '.php';
        }

        if ($alternativeViewFile != "" && file_exists($alternativeViewFile)) {
            $viewFile = $alternativeViewFile;
        }

        return $viewFile;
    }

    /**
     * Returns the layout file
     * 
     * @param string $mode the output mode
     * @return string the layout file
     */
    protected function getLayoutFile($mode)
    {
        if ($mode == self::OUTPUT_MAIL_PLAINTEXT) {
            return $this->layoutMailPlaintext;
        } elseif ($mode == self::OUTPUT_MAIL) {
            return $this->layoutMail;
        }

        return $this->layoutWeb;
    }

    /**
     * Returns the directory containing the view files for this event.
     * The default implementation returns the 'views' subdirectory under the directory containing the notification class file.
     * @return string the directory containing the view files for this notification.
     */
    public function getViewPath()
    {
        if ($this->viewPath !== null) {
            return Yii::getAlias($this->viewPath);
        }

        $class = new \ReflectionClass($this);
        return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
    }

}
