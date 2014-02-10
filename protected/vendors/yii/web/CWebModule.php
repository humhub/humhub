<?php
/**
 * CWebModule class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebModule represents an application module.
 *
 * An application module may be considered as a self-contained sub-application
 * that has its own controllers, models and views and can be reused in a different
 * project as a whole. Controllers inside a module must be accessed with routes
 * that are prefixed with the module ID.
 *
 * @property string $name The name of this module.
 * @property string $description The description of this module.
 * @property string $version The version of this module.
 * @property string $controllerPath The directory that contains the controller classes. Defaults to 'moduleDir/controllers'
 * where moduleDir is the directory containing the module class.
 * @property string $viewPath The root directory of view files. Defaults to 'moduleDir/views' where moduleDir is
 * the directory containing the module class.
 * @property string $layoutPath The root directory of layout files. Defaults to 'moduleDir/views/layouts' where
 * moduleDir is the directory containing the module class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 */
class CWebModule extends CModule
{
	/**
	 * @var string the ID of the default controller for this module. Defaults to 'default'.
	 */
	public $defaultController='default';
	/**
	 * @var mixed the layout that is shared by the controllers inside this module.
	 * If a controller has explicitly declared its own {@link CController::layout layout},
	 * this property will be ignored.
	 * If this is null (default), the application's layout or the parent module's layout (if available)
	 * will be used. If this is false, then no layout will be used.
	 */
	public $layout;
	/**
	 * @var string Namespace that should be used when loading controllers.
	 * Default is to use global namespace.
	 * @since 1.1.11
	 */
	public $controllerNamespace;
	/**
	 * @var array mapping from controller ID to controller configurations.
	 * Pleaser refer to {@link CWebApplication::controllerMap} for more details.
	 */
	public $controllerMap=array();

	private $_controllerPath;
	private $_viewPath;
	private $_layoutPath;


	/**
	 * Returns the name of this module.
	 * The default implementation simply returns {@link id}.
	 * You may override this method to customize the name of this module.
	 * @return string the name of this module.
	 */
	public function getName()
	{
		return basename($this->getId());
	}

	/**
	 * Returns the description of this module.
	 * The default implementation returns an empty string.
	 * You may override this method to customize the description of this module.
	 * @return string the description of this module.
	 */
	public function getDescription()
	{
		return '';
	}

	/**
	 * Returns the version of this module.
	 * The default implementation returns '1.0'.
	 * You may override this method to customize the version of this module.
	 * @return string the version of this module.
	 */
	public function getVersion()
	{
		return '1.0';
	}

	/**
	 * @return string the directory that contains the controller classes. Defaults to 'moduleDir/controllers' where
     * moduleDir is the directory containing the module class.
	 */
	public function getControllerPath()
	{
		if($this->_controllerPath!==null)
			return $this->_controllerPath;
		else
			return $this->_controllerPath=$this->getBasePath().DIRECTORY_SEPARATOR.'controllers';
	}

	/**
	 * @param string $value the directory that contains the controller classes.
	 * @throws CException if the directory is invalid
	 */
	public function setControllerPath($value)
	{
		if(($this->_controllerPath=realpath($value))===false || !is_dir($this->_controllerPath))
			throw new CException(Yii::t('yii','The controller path "{path}" is not a valid directory.',
				array('{path}'=>$value)));
	}

	/**
	 * @return string the root directory of view files. Defaults to 'moduleDir/views' where
	 * moduleDir is the directory containing the module class.
	 */
	public function getViewPath()
	{
		if($this->_viewPath!==null)
			return $this->_viewPath;
		else
			return $this->_viewPath=$this->getBasePath().DIRECTORY_SEPARATOR.'views';
	}

	/**
	 * @param string $path the root directory of view files.
	 * @throws CException if the directory does not exist.
	 */
	public function setViewPath($path)
	{
		if(($this->_viewPath=realpath($path))===false || !is_dir($this->_viewPath))
			throw new CException(Yii::t('yii','The view path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string the root directory of layout files. Defaults to 'moduleDir/views/layouts' where
	 * moduleDir is the directory containing the module class.
	 */
	public function getLayoutPath()
	{
		if($this->_layoutPath!==null)
			return $this->_layoutPath;
		else
			return $this->_layoutPath=$this->getViewPath().DIRECTORY_SEPARATOR.'layouts';
	}

	/**
	 * @param string $path the root directory of layout files.
	 * @throws CException if the directory does not exist.
	 */
	public function setLayoutPath($path)
	{
		if(($this->_layoutPath=realpath($path))===false || !is_dir($this->_layoutPath))
			throw new CException(Yii::t('yii','The layout path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * The pre-filter for controller actions.
	 * This method is invoked before the currently requested controller action and all its filters
	 * are executed. You may override this method in the following way:
	 * <pre>
	 * if(parent::beforeControllerAction($controller,$action))
	 * {
	 *     // your code
	 *     return true;
	 * }
	 * else
	 *     return false;
	 * </pre>
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 * @return boolean whether the action should be executed.
	 */
	public function beforeControllerAction($controller,$action)
	{
		if(($parent=$this->getParentModule())===null)
			$parent=Yii::app();
		return $parent->beforeControllerAction($controller,$action);
	}

	/**
	 * The post-filter for controller actions.
	 * This method is invoked after the currently requested controller action and all its filters
	 * are executed. If you override this method, make sure you call the parent implementation at the end.
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 */
	public function afterControllerAction($controller,$action)
	{
		if(($parent=$this->getParentModule())===null)
			$parent=Yii::app();
		$parent->afterControllerAction($controller,$action);
	}
}
