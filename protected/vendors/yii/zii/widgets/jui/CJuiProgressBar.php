<?php
/**
 * CJuiProgressBar class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiProgressBar displays a progress bar widget.
 *
 * CJuiProgressBar encapsulates the {@link http://jqueryui.com/demos/progressbar/ JUI
 * Progressbar} plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiProgressBar',array(
 *     'value'=>75,
 *     // additional javascript options for the progress bar plugin
 *     'options'=>array(
 *         'change'=>new CJavaScriptExpression('function(event, ui) {...}'),
 *     ),
 *     'htmlOptions'=>array(
 *         'style'=>'height:20px;',
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI progressbar plugin. Please refer to
 * the {@link http://jqueryui.com/demos/progressbar/ JUI Progressbar} documentation
 * for possible options (name-value pairs).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiProgressBar extends CJuiWidget
{
	/**
	 * @var string the name of the container element that contains the progress bar. Defaults to 'div'.
	 */
	public $tagName='div';
	/**
	 * @var integer the percentage of the progress. This must be an integer between 0 and 100. Defaults to 0.
	 */
	public $value=0;

	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	 */
	public function run()
	{
		$id=$this->getId();
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		echo CHtml::openTag($this->tagName,$this->htmlOptions);
		echo CHtml::closeTag($this->tagName);

		$this->options['value']=$this->value;
		$options=CJavaScript::encode($this->options);
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').progressbar($options);");
	}
}