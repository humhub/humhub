<?php
/**
 * CMaskedTextField class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMaskedTextField generates a masked text field.
 *
 * CMaskedTextField is similar to {@link CHtml::textField} except that
 * an input mask will be used to help users enter properly formatted data.
 * The masked text field is implemented based on the jQuery masked input plugin
 * (see {@link http://digitalbush.com/projects/masked-input-plugin}).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CMaskedTextField extends CInputWidget
{
	/**
	 * @var string the input mask (e.g. '99/99/9999' for date input). The following characters are predefined:
	 * <ul>
	 * <li>a: represents an alpha character (A-Z,a-z).</li>
	 * <li>9: represents a numeric character (0-9).</li>
	 * <li>*: represents an alphanumeric character (A-Z,a-z,0-9).</li>
	 * <li>?: anything listed after '?' within the mask is considered optional user input.</li>
	 * </ul>
	 * Additional characters can be defined by specifying the {@link charMap} property.
	 */
	public $mask;
	/**
	 * @var array the mapping between mask characters and the corresponding patterns.
	 * For example, array('~'=>'[+-]') specifies that the '~' character expects '+' or '-' input.
	 * Defaults to null, meaning using the map as described in {@link mask}.
	 */
	public $charMap;
	/**
	 * @var string the character prompting for user input. Defaults to underscore '_'.
	 */
	public $placeholder;
	/**
	 * @var string a JavaScript function callback that will be invoked when user finishes the input.
	 */
	public $completed;

	/**
	 * Executes the widget.
	 * This method registers all needed client scripts and renders
	 * the text field.
	 */
	public function run()
	{
		if($this->mask=='')
			throw new CException(Yii::t('yii','Property CMaskedTextField.mask cannot be empty.'));

		list($name,$id)=$this->resolveNameID();
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];

		$this->registerClientScript();

		if($this->hasModel())
			echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
		else
			echo CHtml::textField($name,$this->value,$this->htmlOptions);
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 */
	public function registerClientScript()
	{
		$id=$this->htmlOptions['id'];
		$miOptions=$this->getClientOptions();
		$options=$miOptions!==array() ? ','.CJavaScript::encode($miOptions) : '';
		$js='';
		if(is_array($this->charMap))
			$js.='jQuery.mask.definitions='.CJavaScript::encode($this->charMap).";\n";
		$js.="jQuery(\"#{$id}\").mask(\"{$this->mask}\"{$options});";

		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('maskedinput');
		$cs->registerScript('Yii.CMaskedTextField#'.$id,$js);
	}

	/**
	 * @return array the options for the text field
	 */
	protected function getClientOptions()
	{
		$options=array();
		if($this->placeholder!==null)
			$options['placeholder']=$this->placeholder;

		if($this->completed!==null)
		{
			if($this->completed instanceof CJavaScriptExpression)
				$options['completed']=$this->completed;
			else
				$options['completed']=new CJavaScriptExpression($this->completed);
		}

		return $options;
	}
}