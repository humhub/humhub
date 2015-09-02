<?php
/**
 * CLogFilter class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLogFilter preprocesses the logged messages before they are handled by a log route.
 *
 * CLogFilter is meant to be used by a log route to preprocess the logged messages
 * before they are handled by the route. The default implementation of CLogFilter
 * prepends additional context information to the logged messages. In particular,
 * by setting {@link logVars}, predefined PHP variables such as
 * $_SERVER, $_POST, etc. can be saved as a log message, which may help identify/debug
 * issues encountered.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.logging
 */
class CLogFilter extends CComponent implements ILogFilter
{
	/**
	 * @var boolean whether to prefix each log message with the current user session ID.
	 * Defaults to false.
	 */
	public $prefixSession=false;
	/**
	 * @var boolean whether to prefix each log message with the current user
	 * {@link CWebUser::name name} and {@link CWebUser::id ID}. Defaults to false.
	 */
	public $prefixUser=false;
	/**
	 * @var boolean whether to log the current user name and ID. Defaults to true.
	 */
	public $logUser=true;
	/**
	 * @var array list of the PHP predefined variables that should be logged.
	 * Note that a variable must be accessible via $GLOBALS. Otherwise it won't be logged.
	 */
	public $logVars=array('_GET','_POST','_FILES','_COOKIE','_SESSION','_SERVER');
	/**
	 * @var callable or function which will be used to dump context information.
	 * Defaults to `var_export`. If you're experiencing issues with circular references
	 * problem change it to `print_r`. Any kind of callable (static methods, user defined
	 * functions, lambdas, etc.) could also be used.
	 * @since 1.1.14
	 */
	public $dumper='var_export';


	/**
	 * Filters the given log messages.
	 * This is the main method of CLogFilter. It processes the log messages
	 * by adding context information, etc.
	 * @param array $logs the log messages
	 * @return array
	 */
	public function filter(&$logs)
	{
		if (!empty($logs))
		{
			if(($message=$this->getContext())!=='')
				array_unshift($logs,array($message,CLogger::LEVEL_INFO,'application',YII_BEGIN_TIME));
			$this->format($logs);
		}
		return $logs;
	}

	/**
	 * Formats the log messages.
	 * The default implementation will prefix each message with session ID
	 * if {@link prefixSession} is set true. It may also prefix each message
	 * with the current user's name and ID if {@link prefixUser} is true.
	 * @param array $logs the log messages
	 */
	protected function format(&$logs)
	{
		$prefix='';
		if($this->prefixSession && ($id=session_id())!=='')
			$prefix.="[$id]";
		if($this->prefixUser && ($user=Yii::app()->getComponent('user',false))!==null)
			$prefix.='['.$user->getName().']['.$user->getId().']';
		if($prefix!=='')
		{
			foreach($logs as &$log)
				$log[0]=$prefix.' '.$log[0];
		}
	}

	/**
	 * Generates the context information to be logged.
	 * The default implementation will dump user information, system variables, etc.
	 * @return string the context information. If an empty string, it means no context information.
	 */
	protected function getContext()
	{
		$context=array();
		if($this->logUser && ($user=Yii::app()->getComponent('user',false))!==null)
			$context[]='User: '.$user->getName().' (ID: '.$user->getId().')';

		if($this->dumper==='var_export' || $this->dumper==='print_r')
		{
			foreach($this->logVars as $name)
				if(($value=$this->getGlobalsValue($name))!==null)
					$context[]="\${$name}=".call_user_func($this->dumper,$value,true);
		}
		else
		{
			foreach($this->logVars as $name)
				if(($value=$this->getGlobalsValue($name))!==null)
					$context[]="\${$name}=".call_user_func($this->dumper,$value);
		}

		return implode("\n\n",$context);
	}

	/**
	 * @param string[] $path
	 * @return string|null
	 */
	private function getGlobalsValue(&$path)
	{
		if(is_scalar($path))
			return !empty($GLOBALS[$path]) ? $GLOBALS[$path] : null;
		$pathAux=$path;
		$parts=array();
		$value=$GLOBALS;
		do
		{
			$value=$value[$parts[]=array_shift($pathAux)];
		}
		while(!empty($value) && !empty($pathAux) && !is_string($value));
		$path=implode('.',$parts);
		return $value;
	}
}
