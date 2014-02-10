<?php
/**
 * CJuiDraggable class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiDraggable displays a draggable widget.
 *
 * CJuiDraggable encapsulates the {@link http://jqueryui.com/demos/draggable/ JUI Draggable}
 * plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->beginWidget('zii.widgets.jui.CJuiDraggable',array(
 *     // additional javascript options for the draggable plugin
 *     'options'=>array(
 *         'scope'=>'myScope',
 *     ),
 * ));
 *     echo 'Your draggable content here';
 *
 * $this->endWidget();
 *
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI Draggable plugin. Please refer to
 * the {@link http://jqueryui.com/demos/draggable/ JUI Draggable} documentation
 * for possible options (name-value pairs).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiDraggable extends CJuiWidget
{
	/**
	 * @var string the name of the Draggable element. Defaults to 'div'.
	 */
	public $tagName='div';

	/**
	 * Renders the open tag of the draggable element.
	 * This method also registers the necessary javascript code.
	 */
	public function init()
	{
		parent::init();

		$id=$this->getId();
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		$options=CJavaScript::encode($this->options);
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').draggable($options);");

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";
	}

	/**
	 * Renders the close tag of the draggable element.
	 */
	public function run()
	{
		echo CHtml::closeTag($this->tagName);
	}
}