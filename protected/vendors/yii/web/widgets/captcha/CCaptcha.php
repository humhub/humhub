<?php
/**
 * CCaptcha class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCaptcha renders a CAPTCHA image element.
 *
 * CCaptcha is used together with {@link CCaptchaAction} to provide {@link http://en.wikipedia.org/wiki/Captcha CAPTCHA}
 * - a way of preventing site spam.
 *
 * The image element rendered by CCaptcha will display a CAPTCHA image generated
 * by an action of class {@link CCaptchaAction} belonging to the current controller.
 * By default, the action ID should be 'captcha', which can be changed by setting {@link captchaAction}.
 *
 * CCaptcha may also render a button next to the CAPTCHA image. Clicking on the button
 * will change the CAPTCHA image to be a new one in an AJAX way.
 *
 * If {@link clickableImage} is set true, clicking on the CAPTCHA image
 * will refresh the CAPTCHA.
 *
 * A {@link CCaptchaValidator} may be used to validate that the user enters
 * a verification code matching the code displayed in the CAPTCHA image.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets.captcha
 * @since 1.0
 */
class CCaptcha extends CWidget
{
	/**
	 * @var string the ID of the action that should provide CAPTCHA image. Defaults to 'captcha',
	 * meaning the 'captcha' action of the current controller. This property may also
	 * be in the format of 'ControllerID/ActionID'. Underneath, this property is used
	 * by {@link CController::createUrl} to create the URL that would serve the CAPTCHA image.
	 * The action has to be of {@link CCaptchaAction}.
	 */
	public $captchaAction='captcha';
	/**
	 * @var boolean whether to display a button next to the CAPTCHA image. Clicking on the button
	 * will cause the CAPTCHA image to be changed to a new one. Defaults to true.
	 */
	public $showRefreshButton=true;
	/**
	 * @var boolean whether to allow clicking on the CAPTCHA image to refresh the CAPTCHA letters.
	 * Defaults to false. Hint: you may want to set {@link showRefreshButton} to false if you set
	 * this property to be true because they serve for the same purpose.
	 * To enhance accessibility, you may set {@link imageOptions} to provide hints to end-users that
	 * the image is clickable.
	 */
	public $clickableImage=false;
	/**
	 * @var string the label for the refresh button. Defaults to 'Get a new code'.
	 */
	public $buttonLabel;
	/**
	 * @var string the type of the refresh button. This should be either 'link' or 'button'.
	 * The former refers to hyperlink button while the latter a normal push button.
	 * Defaults to 'link'.
	 */
	public $buttonType='link';
	/**
	 * @var array HTML attributes to be applied to the rendered image element.
	 */
	public $imageOptions=array();
	/**
	 * @var array HTML attributes to be applied to the rendered refresh button element.
	 */
	public $buttonOptions=array();


	/**
	 * Renders the widget.
	 */
	public function run()
	{
		if(self::checkRequirements('imagick') || self::checkRequirements('gd'))
		{
			$this->renderImage();
			$this->registerClientScript();
		}
		else
			throw new CException(Yii::t('yii','GD with FreeType or ImageMagick PHP extensions are required.'));
	}

	/**
	 * Renders the CAPTCHA image.
	 */
	protected function renderImage()
	{
		if(!isset($this->imageOptions['id']))
			$this->imageOptions['id']=$this->getId();

		$url=$this->getController()->createUrl($this->captchaAction,array('v'=>uniqid()));
		$alt=isset($this->imageOptions['alt'])?$this->imageOptions['alt']:'';
		echo CHtml::image($url,$alt,$this->imageOptions);
	}

	/**
	 * Registers the needed client scripts.
	 */
	public function registerClientScript()
	{
		$cs=Yii::app()->clientScript;
		$id=$this->imageOptions['id'];
		$url=$this->getController()->createUrl($this->captchaAction,array(CCaptchaAction::REFRESH_GET_VAR=>true));

		$js="";
		if($this->showRefreshButton)
		{
			// reserve a place in the registered script so that any enclosing button js code appears after the captcha js
			$cs->registerScript('Yii.CCaptcha#'.$id,'// dummy');
			$label=$this->buttonLabel===null?Yii::t('yii','Get a new code'):$this->buttonLabel;
			$options=$this->buttonOptions;
			if(isset($options['id']))
				$buttonID=$options['id'];
			else
				$buttonID=$options['id']=$id.'_button';
			if($this->buttonType==='button')
				$html=CHtml::button($label, $options);
			else
				$html=CHtml::link($label, $url, $options);
			$js="jQuery('#$id').after(".CJSON::encode($html).");";
			$selector="#$buttonID";
		}

		if($this->clickableImage)
			$selector=isset($selector) ? "$selector, #$id" : "#$id";

		if(!isset($selector))
			return;

		$js.="
jQuery(document).on('click', '$selector', function(){
	jQuery.ajax({
		url: ".CJSON::encode($url).",
		dataType: 'json',
		cache: false,
		success: function(data) {
			jQuery('#$id').attr('src', data['url']);
			jQuery('body').data('{$this->captchaAction}.hash', [data['hash1'], data['hash2']]);
		}
	});
	return false;
});
";
		$cs->registerScript('Yii.CCaptcha#'.$id,$js);
	}

	/**
	 * Checks if specified graphic extension support is loaded.
	 * @param string extension name to be checked. Possible values are 'gd', 'imagick' and null.
	 * Default value is null meaning that both extensions will be checked. This parameter
	 * is available since 1.1.13.
	 * @return boolean true if ImageMagick extension with PNG support or GD with FreeType support is loaded,
	 * otherwise false
	 * @since 1.1.5
	 */
	public static function checkRequirements($extension=null)
	{
		if(extension_loaded('imagick'))
		{
			$imagick=new Imagick();
			$imagickFormats=$imagick->queryFormats('PNG');
		}
		if(extension_loaded('gd'))
		{
			$gdInfo=gd_info();
		}
		if($extension===null)
		{
			if(isset($imagickFormats) && in_array('PNG',$imagickFormats))
				return true;
			if(isset($gdInfo) && $gdInfo['FreeType Support'])
				return true;
		}
		elseif($extension=='imagick' && isset($imagickFormats) && in_array('PNG',$imagickFormats))
			return true;
		elseif($extension=='gd' && isset($gdInfo) && $gdInfo['FreeType Support'])
			return true;
		return false;
	}
}
