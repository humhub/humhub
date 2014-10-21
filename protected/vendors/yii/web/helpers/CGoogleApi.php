<?php
/**
 * CGoogleApi class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGoogleApi provides helper methods to easily access the {@link https://developers.google.com/loader/ Google API loader}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.helpers
 */
class CGoogleApi
{
	/**
	* @var string Protocol relative url to the Google API loader which allows easy access
	* to most of the Google AJAX APIs
	*/
	public static $bootstrapUrl='//www.google.com/jsapi';

	/**
	 * Renders the jsapi script file.
	 * @param string $apiKey the API key. Null if you do not have a key.
	 * @return string the script tag that loads Google jsapi.
	 */
	public static function init($apiKey=null)
	{
		if($apiKey===null)
			return CHtml::scriptFile(self::$bootstrapUrl);
		else
			return CHtml::scriptFile(self::$bootstrapUrl.'?key='.$apiKey);
	}

	/**
	 * Loads the specified API module.
	 * Note that you should call {@link init} first.
	 * @param string $name the module name
	 * @param string $version the module version
	 * @param array $options additional js options that are to be passed to the load() function.
	 * @return string the js code for loading the module. You can use {@link CHtml::script()}
	 * to enclose it in a script tag.
	 */
	public static function load($name,$version='1',$options=array())
	{
		if(empty($options))
			return "google.load(\"{$name}\",\"{$version}\");";
		else
			return "google.load(\"{$name}\",\"{$version}\",".CJavaScript::encode($options).");";
	}

	/**
	 * Registers the specified API module.
	 * This is similar to {@link load} except that it registers the loading code
	 * with {@link CClientScript} instead of returning it.
	 * This method also registers the jsapi script needed by the loading call.
	 * @param string $name the module name
	 * @param string $version the module version
	 * @param array $options additional js options that are to be passed to the load() function.
	 * @param string $apiKey the API key. Null if you do not have a key.
	 */
	public static function register($name,$version='1',$options=array(),$apiKey=null)
	{
		$cs=Yii::app()->getClientScript();
		$url=$apiKey===null?self::$bootstrapUrl:self::$bootstrapUrl.'?key='.$apiKey;
		$cs->registerScriptFile($url,CClientScript::POS_HEAD);

		$js=self::load($name,$version,$options);
		$cs->registerScript($name,$js,CClientScript::POS_HEAD);
	}
}