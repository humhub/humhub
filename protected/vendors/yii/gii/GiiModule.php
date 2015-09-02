<?php
/**
 * GiiModule class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('system.gii.CCodeGenerator');
Yii::import('system.gii.CCodeModel');
Yii::import('system.gii.CCodeFile');
Yii::import('system.gii.CCodeForm');

/**
 * GiiModule is a module that provides Web-based code generation capabilities.
 *
 * To use GiiModule, you must include it as a module in the application configuration like the following:
 * <pre>
 * return array(
 *     ......
 *     'modules'=>array(
 *         'gii'=>array(
 *             'class'=>'system.gii.GiiModule',
 *             'password'=>***choose a password***
 *         ),
 *     ),
 * )
 * </pre>
 *
 * Because GiiModule generates new code files on the server, you should only use it on your own
 * development machine. To prevent other people from using this module, it is required that
 * you specify a secret password in the configuration. Later when you access
 * the module via browser, you will be prompted to enter the correct password.
 *
 * By default, GiiModule can only be accessed by localhost. You may configure its {@link ipFilters}
 * property if you want to make it accessible on other machines.
 *
 * With the above configuration, you will be able to access GiiModule in your browser using
 * the following URL:
 *
 * http://localhost/path/to/index.php?r=gii
 *
 * If your application is using path-format URLs with some customized URL rules, you may need to add
 * the following URLs in your application configuration in order to access GiiModule:
 * <pre>
 * 'components'=>array(
 *     'urlManager'=>array(
 *         'urlFormat'=>'path',
 *         'rules'=>array(
 *             'gii'=>'gii',
 *             'gii/<controller:\w+>'=>'gii/<controller>',
 *             'gii/<controller:\w+>/<action:\w+>'=>'gii/<controller>/<action>',
 *             ...other rules...
 *         ),
 *     )
 * )
 * </pre>
 *
 * You can then access GiiModule via:
 *
 * http://localhost/path/to/index.php/gii
 *
 * @property string $assetsUrl The base URL that contains all published asset files of gii.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.gii
 * @since 1.1.2
 */
class GiiModule extends CWebModule
{
	/**
	 * @var string the password that can be used to access GiiModule.
	 * If this property is set false, then GiiModule can be accessed without password
	 * (DO NOT DO THIS UNLESS YOU KNOW THE CONSEQUENCE!!!)
	 */
	public $password;
	/**
	 * @var array the IP filters that specify which IP addresses are allowed to access GiiModule.
	 * Each array element represents a single filter. A filter can be either an IP address
	 * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
	 * If you want to allow all IPs to access gii, you may set this property to be false
	 * (DO NOT DO THIS UNLESS YOU KNOW THE CONSEQUENCE!!!)
	 * The default value is array('127.0.0.1', '::1'), which means GiiModule can only be accessed
	 * on the localhost.
	 */
	public $ipFilters=array('127.0.0.1','::1');
	/**
	 * @var array a list of path aliases that refer to the directories containing code generators.
	 * The directory referred by a single path alias may contain multiple code generators, each stored
	 * under a sub-directory whose name is the generator name.
	 * Defaults to array('application.gii').
	 */
	public $generatorPaths=array('application.gii');
	/**
	 * @var integer the permission to be set for newly generated code files.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0666, meaning the file is read-writable by all users.
	 */
	public $newFileMode=0666;
	/**
	 * @var integer the permission to be set for newly generated directories.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0777, meaning the directory can be read, written and executed by all users.
	 */
	public $newDirMode=0777;

	private $_assetsUrl;

	/**
	 * Initializes the gii module.
	 */
	public function init()
	{
		parent::init();
		Yii::setPathOfAlias('gii',dirname(__FILE__));
		Yii::app()->setComponents(array(
			'errorHandler'=>array(
				'class'=>'CErrorHandler',
				'errorAction'=>$this->getId().'/default/error',
			),
			'user'=>array(
				'class'=>'CWebUser',
				'stateKeyPrefix'=>'gii',
				'loginUrl'=>Yii::app()->createUrl($this->getId().'/default/login'),
			),
			'widgetFactory' => array(
				'class'=>'CWidgetFactory',
				'widgets' => array()
			)
		), false);
		$this->generatorPaths[]='gii.generators';
		$this->controllerMap=$this->findGenerators();
	}

	/**
	 * @return string the base URL that contains all published asset files of gii.
	 */
	public function getAssetsUrl()
	{
		if($this->_assetsUrl===null)
			$this->_assetsUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('gii.assets'));
		return $this->_assetsUrl;
	}

	/**
	 * @param string $value the base URL that contains all published asset files of gii.
	 */
	public function setAssetsUrl($value)
	{
		$this->_assetsUrl=$value;
	}

	/**
	 * Performs access check to gii.
	 * This method will check to see if user IP and password are correct if they attempt
	 * to access actions other than "default/login" and "default/error".
	 * @param CController $controller the controller to be accessed.
	 * @param CAction $action the action to be accessed.
	 * @throws CHttpException if access denied
	 * @return boolean whether the action should be executed.
	 */
	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			$route=$controller->id.'/'.$action->id;
			if(!$this->allowIp(Yii::app()->request->userHostAddress) && $route!=='default/error')
				throw new CHttpException(403,"You are not allowed to access this page.");

			$publicPages=array(
				'default/login',
				'default/error',
			);
			if($this->password!==false && Yii::app()->user->isGuest && !in_array($route,$publicPages))
				Yii::app()->user->loginRequired();
			else
				return true;
		}
		return false;
	}

	/**
	 * Checks to see if the user IP is allowed by {@link ipFilters}.
	 * @param string $ip the user IP
	 * @return boolean whether the user IP is allowed by {@link ipFilters}.
	 */
	protected function allowIp($ip)
	{
		if(empty($this->ipFilters))
			return true;
		foreach($this->ipFilters as $filter)
		{
			if($filter==='*' || $filter===$ip || (($pos=strpos($filter,'*'))!==false && !strncmp($ip,$filter,$pos)))
				return true;
		}
		return false;
	}

	/**
	 * Finds all available code generators and their code templates.
	 * @return array
	 */
	protected function findGenerators()
	{
		$generators=array();
		$n=count($this->generatorPaths);
		for($i=$n-1;$i>=0;--$i)
		{
			$alias=$this->generatorPaths[$i];
			$path=Yii::getPathOfAlias($alias);
			if($path===false || !is_dir($path))
				continue;

			$names=scandir($path);
			foreach($names as $name)
			{
				if($name[0]!=='.' && is_dir($path.'/'.$name))
				{
					$className=ucfirst($name).'Generator';
					if(is_file("$path/$name/$className.php"))
					{
						$generators[$name]=array(
							'class'=>"$alias.$name.$className",
						);
					}

					if(isset($generators[$name]) && is_dir("$path/$name/templates"))
					{
						$templatePath="$path/$name/templates";
						$dirs=scandir($templatePath);
						foreach($dirs as $dir)
						{
							if($dir[0]!=='.' && is_dir($templatePath.'/'.$dir))
								$generators[$name]['templates'][$dir]=strtr($templatePath.'/'.$dir,array('/'=>DIRECTORY_SEPARATOR,'\\'=>DIRECTORY_SEPARATOR));
						}
					}
				}
			}
		}
		return $generators;
	}
}