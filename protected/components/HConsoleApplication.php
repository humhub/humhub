<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * HConsoleApplication is used as base console application.
 *
 * HConsoleApplication extends the default console application with some
 * functionalities about events and theming support.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.8
 */
class HConsoleApplication extends CConsoleApplication
{

    /**
     * Current theme name
     *
     * @var String
     */
    public $theme;
    private $_viewPath;

    /**
     * Initializes the console application and setup some event handlers
     */
    protected function init()
    {

        parent::init();

        $this->interceptor->start();
        $this->moduleManager->start();

        $this->interceptor->intercept($this);

        if ($this->hasEventHandler('onInit'))
            $this->onInit(new CEvent($this));

        $this->setupRequestInfo();
    }

    /**
     * Sets some mandatory request infos to ensure absolute url creation.
     * These values are extracted from baseUrl which is stored as HSetting.
     */
    private function setupRequestInfo()
    {

        $parsedUrl = parse_url(HSetting::Get('baseUrl'));

        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

        Yii::app()->request->setHostInfo($parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $port);
        Yii::app()->request->setBaseUrl(HSetting::Get('baseUrl'));
        Yii::app()->request->setScriptUrl($path . '/index.php');
    }

    /**
     * Creates an absolute URL based on the given controller and action information.
     * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
     * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
     * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
     * @param string $ampersand the token separating name-value pairs in the URL.
     * @return string the constructed URL
     */
    public function createUrl($route, $params = array(), $schema = '', $ampersand = '&')
    {
        $url = parent::createUrl($route, $params, $ampersand);
        if (strpos($url, 'http') === 0)
            return $url;
        else
            return $this->getRequest()->getHostInfo($schema) . $url;
    }

    /**
     * Creates an absolute URL based on the given controller and action information.
     * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
     * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
     * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
     * @param string $ampersand the token separating name-value pairs in the URL.
     * @return string the constructed URL
     */
    public function createAbsoluteUrl($route, $params = array(), $schema = '', $ampersand = '&')
    {
        return $this->createUrl($route, $params, $schema, $ampersand);
    }

    /**
     * Raised after the application inits.
     * @param CEvent $event the event parameter
     */
    public function onInit($event)
    {
        $this->raiseEvent('onInit', $event);
    }

    /**
     * Adds a new command to the Console Application
     *
     * @param String $name
     * @param String $file
     */
    public function addCommand($name, $file)
    {
        $this->getCommandRunner()->commands[$name] = $file;
    }

    /**
     * Registers the core application components.
     * This method overrides the parent implementation by registering additional core components.
     * @see setComponents
     */
    protected function registerCoreComponents()
    {
        parent::registerCoreComponents();

        $components = array(
            'widgetFactory' => array(
                'class' => 'CWidgetFactory',
            ),
            'themeManager' => array(
                'class' => 'CThemeManager',
            ),
        );

        $this->setComponents($components);
    }

    /**
     * Returns the widget factory.
     * @return IWidgetFactory the widget factory
     * @since 1.1
     */
    public function getWidgetFactory()
    {
        return $this->getComponent('widgetFactory');
    }

    /**
     * @return string the root directory of view files. Defaults to 'protected/views'.
     */
    public function getViewPath()
    {
        if ($this->_viewPath !== null)
            return $this->_viewPath;
        else
            return $this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
    }

}

?>
