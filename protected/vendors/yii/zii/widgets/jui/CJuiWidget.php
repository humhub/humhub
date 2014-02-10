<?php
/**
 * CJuiWidget class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * This is the base class for all JUI widget classes.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
abstract class CJuiWidget extends CWidget
{
	/**
	 * @var string the root URL that contains all JUI JavaScript files.
	 * If this property is not set (default), Yii will publish the JUI package included in the zii release and use
	 * that to infer the root script URL. You should set this property if you intend to use
	 * a JUI package whose version is different from the one included in zii.
	 * Note that under this URL, there must be a file whose name is specified by {@link scriptFile}.
	 * Do not append any slash character to the URL.
	 */
	public $scriptUrl;
	/**
	 * @var string the root URL that contains all JUI theme folders.
	 * If this property is not set (default), Yii will publish the JUI package included in the zii release and use
	 * that to infer the root theme URL. You should set this property if you intend to use
	 * a theme that is not found in the JUI package included in zii.
	 * Note that under this URL, there must be a directory whose name is specified by {@link theme}.
	 * Do not append any slash character to the URL.
	 */
	public $themeUrl;
	/**
	 * @var string the JUI theme name. Defaults to 'base'. Make sure that under {@link themeUrl} there
	 * is a directory whose name is the same as this property value (case-sensitive).
	 */
	public $theme='base';
	/**
	 * @var mixed the main JUI JavaScript file. Defaults to 'jquery-ui.min.js'.
	 * Note the file must exist under the URL specified by {@link scriptUrl}.
	 * If you need to include multiple script files (e.g. during development, you want to include individual
	 * plugin script files rather than the minized JUI script file), you may set this property
	 * as an array of the script file names.
	 * This property can also be set as false, which means the widget will not include any script file,
	 * and it is your responsibility to explicitly include it somewhere else.
	 */
	public $scriptFile='jquery-ui.min.js';
	/**
	 * @var mixed the theme CSS file name. Defaults to 'jquery-ui.css'.
	 * Note the file must exist under the URL specified by {@link themeUrl}/{@link theme}.
	 * If you need to include multiple theme CSS files (e.g. during development, you want to include individual
	 * plugin CSS files), you may set this property as an array of the CSS file names.
	 * This property can also be set as false, which means the widget will not include any theme CSS file,
	 * and it is your responsibility to explicitly include it somewhere else.
	 */
	public $cssFile='jquery-ui.css';
	/**
	 * @var array the initial JavaScript options that should be passed to the JUI plugin.
	 */
	public $options=array();
	/**
	 * @var array the HTML attributes that should be rendered in the HTML tag representing the JUI widget.
	 */
	public $htmlOptions=array();

	/**
	 * Initializes the widget.
	 * This method will publish JUI assets if necessary.
	 * It will also register jquery and JUI JavaScript files and the theme CSS file.
	 * If you override this method, make sure you call the parent implementation first.
	 */
	public function init()
	{
		$this->resolvePackagePath();
		$this->registerCoreScripts();
		parent::init();
	}

	/**
	 * Determine the JUI package installation path.
	 * This method will identify the JavaScript root URL and theme root URL.
	 * If they are not explicitly specified, it will publish the included JUI package
	 * and use that to resolve the needed paths.
	 */
	protected function resolvePackagePath()
	{
		if($this->scriptUrl===null || $this->themeUrl===null)
		{
			$cs=Yii::app()->getClientScript();
			if($this->scriptUrl===null)
				$this->scriptUrl=$cs->getCoreScriptUrl().'/jui/js';
			if($this->themeUrl===null)
				$this->themeUrl=$cs->getCoreScriptUrl().'/jui/css';
		}
	}

	/**
	 * Registers the core script files.
	 * This method registers jquery and JUI JavaScript files and the theme CSS file.
	 */
	protected function registerCoreScripts()
	{
		$cs=Yii::app()->getClientScript();
		if(is_string($this->cssFile))
			$cs->registerCssFile($this->themeUrl.'/'.$this->theme.'/'.$this->cssFile);
		elseif(is_array($this->cssFile))
		{
			foreach($this->cssFile as $cssFile)
				$cs->registerCssFile($this->themeUrl.'/'.$this->theme.'/'.$cssFile);
		}

		$cs->registerCoreScript('jquery');
		if(is_string($this->scriptFile))
			$this->registerScriptFile($this->scriptFile);
		elseif(is_array($this->scriptFile))
		{
			foreach($this->scriptFile as $scriptFile)
				$this->registerScriptFile($scriptFile);
		}
	}

	/**
	 * Registers a JavaScript file under {@link scriptUrl}.
	 * Note that by default, the script file will be rendered at the end of a page to improve page loading speed.
	 * @param string $fileName JavaScript file name
	 * @param integer $position the position of the JavaScript file. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * </ul>
	 */
	protected function registerScriptFile($fileName,$position=CClientScript::POS_END)
	{
		Yii::app()->getClientScript()->registerScriptFile($this->scriptUrl.'/'.$fileName,$position);
	}
}