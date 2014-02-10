<?php
/**
 * CJuiButton class file.
 *
 * @author Sebastian Thierer <sebas@artfos.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiInputWidget');

/**
 * CJuiButton displays a button widget.
 *
 * CJuiButton encapsulates the {@link http://jqueryui.com/demos/button/ JUI Button}
 * plugin.
 *
 * To use this widget as a submit button, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiButton',array(
 *     'name'=>'submit',
 *     'caption'=>'Save',
 *     'options'=>array(
 *     'onclick'=>new CJavaScriptExpression('function(){alert("Yes");}'),
 * ));
 * </pre>
 *
 * To use this widget as a button, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiButton',array(
 *     'name'=>'button',
 *     'caption'=>'Save',
 *     'value'=>'asd',
 *     'onclick'=>new CJavaScriptExpression('function(){alert("Save button clicked"); this.blur(); return false;}'),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI button plugin. Please refer to
 * the {@link http://jqueryui.com/demos/button/ JUI Button} documentation
 * for possible options (name-value pairs).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1.3
 */
class CJuiButton extends CJuiInputWidget
{
	/**
	 * @var string The button type (possible types: submit, button, link, radio, checkbox, buttonset).
	 * "submit" is used as default.
	 */
	public $buttonType='submit';
	/**
	 * @var string The default html tag for the buttonset
	 */
	public $htmlTag='div';
	/**
	 * @var mixed a URL or an action route that can be used to create a URL. Used when a buttonType "link" is selected.
	 * See {@link normalizeUrl} for more details about how to specify this parameter.
	 */
	public $url=null;
	/**
	 * @var mixed The value of the current item. Used only for "radio" and "checkbox"
	 */
	public $value;
	/**
	 * @var string The button text
	 */
	public $caption="";
	/**
	 * @var string The javascript function to be raised when this item is clicked (client event).
	 */
	public $onclick;

	/**
	 * (non-PHPdoc)
	 * @see framework/zii/widgets/jui/CJuiWidget::init()
	 */
	public function init()
	{
		parent::init();

		if($this->buttonType=='buttonset')
		{
			if(!isset($this->htmlOptions['id']))
				$this->htmlOptions['id']=$this->getId();

			echo CHtml::openTag($this->htmlTag,$this->htmlOptions);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see framework/CWidget::run()
	 */
	public function run()
	{
		$cs=Yii::app()->getClientScript();
		list($name,$id)=$this->resolveNameID();

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];
		else
			$this->htmlOptions['name']=$name;

		if($this->buttonType=='buttonset')
		{
			echo CHtml::closeTag($this->htmlTag);
			$cs->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').buttonset();");
		}
		else
		{
			switch($this->buttonType)
			{
				case 'submit':
					echo CHtml::submitButton($this->caption,$this->htmlOptions)."\n";
					break;
				case 'button':
					echo CHtml::htmlButton($this->caption,$this->htmlOptions)."\n";
					break;
				case 'link':
					echo CHtml::link($this->caption,$this->url,$this->htmlOptions)."\n";
					break;
				case 'radio':
					if($this->hasModel())
					{
						echo CHtml::activeRadioButton($this->model,$this->attribute,$this->htmlOptions);
						echo CHtml::label($this->caption,CHtml::activeId($this->model,$this->attribute))."\n";
					}
					else
					{
						echo CHtml::radioButton($name,$this->value,$this->htmlOptions);
						echo CHtml::label($this->caption,$id)."\n";
					}
					break;
				case 'checkbox':
					if($this->hasModel())
					{
						echo CHtml::activeCheckbox($this->model,$this->attribute,$this->htmlOptions);
						echo CHtml::label($this->caption,CHtml::activeId($this->model,$this->attribute))."\n";
					}
					else
					{
						echo CHtml::checkbox($name,$this->value,$this->htmlOptions);
						echo CHtml::label($this->caption,$id)."\n";
					}
					break;
				default:
					throw new CException(Yii::t('zii','The button type "{type}" is not supported.',array('{type}'=>$this->buttonType)));
			}

			$options=CJavaScript::encode($this->options);
			if($this->onclick!==null)
			{
				if(!($this->onclick instanceof CJavaScriptExpression))
					$this->onclick=new CJavaScriptExpression($this->onclick);
				$click=CJavaScript::encode($this->onclick);
				$cs->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').button($options).click($click);");
			}
			else
				$cs->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').button($options);");
		}
	}
}