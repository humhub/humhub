<?php
/**
 * CJuiSlider class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiSlider displays a slider.
 *
 * CJuiSlider encapsulates the {@link http://jqueryui.com/demos/slider/ JUI
 * slider} plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiSlider',array(
 *     'value'=>37,
 *     // additional javascript options for the slider plugin
 *     'options'=>array(
 *         'min'=>10,
 *         'max'=>50,
 *     ),
 *     'htmlOptions'=>array(
 *         'style'=>'height:20px;',
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI slider plugin. Please refer to
 * the {@link http://jqueryui.com/demos/slider/ JUI slider} documentation
 * for possible options (name-value pairs).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiSlider extends CJuiWidget
{
	/**
	 * @var string the name of the container element that contains the slider. Defaults to 'div'.
	 */
	public $tagName='div';
	/**
	 * @var integer determines the value of the slider, if there's only one handle. If there is more than one handle, determines the value of the first handle.
	 */
	public $value;

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

		echo CHtml::tag($this->tagName,$this->htmlOptions,'');

		if($this->value!==null)
			$this->options['value']=$this->value;

		$options=CJavaScript::encode($this->options);
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').slider($options);");
	}
}