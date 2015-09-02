<?php
/**
 * CWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWidget is the base class for widgets.
 *
 * A widget is a self-contained component that may generate presentation
 * based on model data.  It can be viewed as a micro-controller that embeds
 * into the controller-managed views.
 *
 * Compared with {@link CController controller}, a widget has neither actions nor filters.
 *
 * Usage is described at {@link CBaseController} and {@link CBaseController::widget}.
 *
 * @property CBaseController $owner Owner/creator of this widget. It could be either a widget or a controller.
 * @property string $id Id of the widget.
 * @property CController $controller The controller that this widget belongs to.
 * @property string $viewPath The directory containing the view files for this widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CWidget extends CBaseController
{
	/**
	 * @var string the prefix to the IDs of the {@link actions}.
	 * When a widget is declared an action provider in {@link CController::actions},
	 * a prefix can be specified to differentiate its action IDs from others.
	 * The same prefix should then also be used to configure this property
	 * when the widget is used in a view of the controller.
	 */
	public $actionPrefix;
	/**
	 * @var mixed the name of the skin to be used by this widget. Defaults to 'default'.
	 * If this is set as false, no skin will be applied to this widget.
	 * @see CWidgetFactory
	 * @since 1.1
	 */
	public $skin='default';

	/**
	 * @var array view paths for different types of widgets
	 */
	private static $_viewPaths;
	/**
	 * @var integer the counter for generating implicit IDs.
	 */
	private static $_counter=0;
	/**
	 * @var string id of the widget.
	 */
	private $_id;
	/**
	 * @var CBaseController owner/creator of this widget. It could be either a widget or a controller.
	 */
	private $_owner;

	/**
	 * Returns a list of actions that are used by this widget.
	 * The structure of this method's return value is similar to
	 * that returned by {@link CController::actions}.
	 *
	 * When a widget uses several actions, you can declare these actions using
	 * this method. The widget will then become an action provider, and the actions
	 * can be easily imported into a controller.
	 *
	 * Note, when creating URLs referring to the actions listed in this method,
	 * make sure the action IDs are prefixed with {@link actionPrefix}.
	 *
	 * @return array
	 *
	 * @see actionPrefix
	 * @see CController::actions
	 */
	public static function actions()
	{
		return array();
	}

	/**
	 * Constructor.
	 * @param CBaseController $owner owner/creator of this widget. It could be either a widget or a controller.
	 */
	public function __construct($owner=null)
	{
		$this->_owner=$owner===null?Yii::app()->getController():$owner;
	}

	/**
	 * Returns the owner/creator of this widget.
	 * @return CBaseController owner/creator of this widget. It could be either a widget or a controller.
	 */
	public function getOwner()
	{
		return $this->_owner;
	}

	/**
	 * Returns the ID of the widget or generates a new one if requested.
	 * @param boolean $autoGenerate whether to generate an ID if it is not set previously
	 * @return string id of the widget.
	 */
	public function getId($autoGenerate=true)
	{
		if($this->_id!==null)
			return $this->_id;
		elseif($autoGenerate)
			return $this->_id='yw'.self::$_counter++;
	}

	/**
	 * Sets the ID of the widget.
	 * @param string $value id of the widget.
	 */
	public function setId($value)
	{
		$this->_id=$value;
	}

	/**
	 * Returns the controller that this widget belongs to.
	 * @return CController the controller that this widget belongs to.
	 */
	public function getController()
	{
		if($this->_owner instanceof CController)
			return $this->_owner;
		else
			return Yii::app()->getController();
	}

	/**
	 * Initializes the widget.
	 * This method is called by {@link CBaseController::createWidget}
	 * and {@link CBaseController::beginWidget} after the widget's
	 * properties have been initialized.
	 */
	public function init()
	{
	}

	/**
	 * Executes the widget.
	 * This method is called by {@link CBaseController::endWidget}.
	 */
	public function run()
	{
	}

	/**
	 * Returns the directory containing the view files for this widget.
	 * The default implementation returns the 'views' subdirectory of the directory containing the widget class file.
	 * If $checkTheme is set true, the directory "ThemeID/views/ClassName" will be returned when it exists.
	 * @param boolean $checkTheme whether to check if the theme contains a view path for the widget.
	 * @return string the directory containing the view files for this widget.
	 */
	public function getViewPath($checkTheme=false)
	{
		$className=get_class($this);
		$scope=$checkTheme?'theme':'local';
		if(isset(self::$_viewPaths[$className][$scope]))
			return self::$_viewPaths[$className][$scope];
		else
		{
			if($checkTheme && ($theme=Yii::app()->getTheme())!==null)
			{
				$path=$theme->getViewPath().DIRECTORY_SEPARATOR;
				if(strpos($className,'\\')!==false) // namespaced class
					$path.=str_replace('\\','_',ltrim($className,'\\'));
				else
					$path.=$className;
				if(is_dir($path))
					return self::$_viewPaths[$className]['theme']=$path;
			}

			$class=new ReflectionClass($className);
			return self::$_viewPaths[$className]['local']=dirname($class->getFileName()).DIRECTORY_SEPARATOR.'views';
		}
	}

	/**
	 * Looks for the view script file according to the view name.
	 * This method will look for the view under the widget's {@link getViewPath viewPath}.
	 * The view script file is named as "ViewName.php". A localized view file
	 * may be returned if internationalization is needed. See {@link CApplication::findLocalizedFile}
	 * for more details.
	 * The view name can also refer to a path alias if it contains dot characters.
	 * @param string $viewName name of the view (without file extension)
	 * @return string the view file path. False if the view file does not exist
	 * @see CApplication::findLocalizedFile
	 */
	public function getViewFile($viewName)
	{
		if(($renderer=Yii::app()->getViewRenderer())!==null)
			$extension=$renderer->fileExtension;
		else
			$extension='.php';
		if(strpos($viewName,'.')) // a path alias
			$viewFile=Yii::getPathOfAlias($viewName);
		else
		{
			$viewFile=$this->getViewPath(true).DIRECTORY_SEPARATOR.$viewName;
			if(is_file($viewFile.$extension))
				return Yii::app()->findLocalizedFile($viewFile.$extension);
			elseif($extension!=='.php' && is_file($viewFile.'.php'))
				return Yii::app()->findLocalizedFile($viewFile.'.php');
			$viewFile=$this->getViewPath(false).DIRECTORY_SEPARATOR.$viewName;
		}

		if(is_file($viewFile.$extension))
			return Yii::app()->findLocalizedFile($viewFile.$extension);
		elseif($extension!=='.php' && is_file($viewFile.'.php'))
			return Yii::app()->findLocalizedFile($viewFile.'.php');
		else
			return false;
	}

	/**
	 * Renders a view.
	 *
	 * The named view refers to a PHP script (resolved via {@link getViewFile})
	 * that is included by this method. If $data is an associative array,
	 * it will be extracted as PHP variables and made available to the script.
	 *
	 * @param string $view name of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 * @param array $data data to be extracted into PHP variables and made available to the view script
	 * @param boolean $return whether the rendering result should be returned instead of being displayed to end users
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @throws CException if the view does not exist
	 * @see getViewFile
	 */
	public function render($view,$data=null,$return=false)
	{
		if(($viewFile=$this->getViewFile($view))!==false)
			return $this->renderFile($viewFile,$data,$return);
		else
			throw new CException(Yii::t('yii','{widget} cannot find the view "{view}".',
				array('{widget}'=>get_class($this), '{view}'=>$view)));
	}
}