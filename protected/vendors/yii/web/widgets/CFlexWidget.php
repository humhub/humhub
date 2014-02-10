<?php
/**
 * CFlexWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFlexWidget embeds a Flex 3.x application into a page.
 *
 * To use CFlexWidget, set {@link name} to be the Flex application name
 * (without the .swf suffix), and set {@link baseUrl} to be URL (without the ending slash)
 * of the directory containing the SWF file of the Flex application.
 *
 * @property string $flashVarsAsString The flash parameter string.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CFlexWidget extends CWidget
{
	/**
	 * @var string name of the Flex application.
	 * This should be the SWF file name without the ".swf" suffix.
	 */
	public $name;
	/**
	 * @var string the base URL of the Flex application.
	 * This refers to the URL of the directory containing the SWF file.
	 */
	public $baseUrl;
	/**
	 * @var string width of the application region. Defaults to 450.
	 */
	public $width='100%';
	/**
	 * @var string height of the application region. Defaults to 300.
	 */
	public $height='100%';
	/**
	 * @var string quality of the animation. Defaults to 'high'.
	 */
	public $quality='high';
	/**
	 * @var string background color of the application region. Defaults to '#FFFFFF', meaning white.
	 */
	public $bgColor='#FFFFFF';
	/**
	 * @var string align of the application region. Defaults to 'middle'.
	 */
	public $align='middle';
	/**
	 * @var string the access method of the script. Defaults to 'sameDomain'.
	 */
	public $allowScriptAccess='sameDomain';
	/**
	 * @var boolean whether to allow running the Flash in full screen mode. Defaults to false.
	 * @since 1.1.1
	 */
	public $allowFullScreen=false;
	/**
	 * @var string the HTML content to be displayed if Flash player is not installed.
	 */
	public $altHtmlContent;
	/**
	 * @var boolean whether history should be enabled. Defaults to true.
	 */
	public $enableHistory=true;
	/**
	 * @var array parameters to be passed to the Flex application.
	 */
	public $flashVars=array();

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		if(empty($this->name))
			throw new CException(Yii::t('yii','CFlexWidget.name cannot be empty.'));
		if(empty($this->baseUrl))
			throw new CException(Yii::t('yii','CFlexWidget.baseUrl cannot be empty.'));
		if($this->altHtmlContent===null)
			$this->altHtmlContent=Yii::t('yii','This content requires the <a href="http://www.adobe.com/go/getflash/">Adobe Flash Player</a>.');

		$this->registerClientScript();

		$this->render('flexWidget');
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 */
	public function registerClientScript()
	{
		$cs=Yii::app()->getClientScript();
		$cs->registerScriptFile($this->baseUrl.'/AC_OETags.js');

		if($this->enableHistory)
		{
			$cs->registerCssFile($this->baseUrl.'/history/history.css');
			$cs->registerScriptFile($this->baseUrl.'/history/history.js');
		}
	}

	/**
	 * Generates the properly quoted flash parameter string.
	 * @return string the flash parameter string.
	 */
	public function getFlashVarsAsString()
	{
		$params=array();
		foreach($this->flashVars as $k=>$v)
			$params[]=urlencode($k).'='.urlencode($v);
		return CJavaScript::quote(implode('&',$params));
	}
}