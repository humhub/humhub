<?php
/**
 * CJuiDialog class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiDialog displays a dialog widget.
 *
 * CJuiDialog encapsulates the {@link http://jqueryui.com/dialog/ JUI Dialog}
 * plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
 *     'id'=>'mydialog',
 *     // additional javascript options for the dialog plugin
 *     'options'=>array(
 *         'title'=>'Dialog box 1',
 *         'autoOpen'=>false,
 *     ),
 * ));
 *
 *     echo 'dialog content here';
 *
 * $this->endWidget('zii.widgets.jui.CJuiDialog');
 *
 * // the link that may open the dialog
 * echo CHtml::link('open dialog', '#', array(
 *    'onclick'=>'$("#mydialog").dialog("open"); return false;',
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI dialog plugin. Please refer to
 * the {@link http://api.jqueryui.com/dialog/ JUI Dialog API} documentation
 * for possible options (name-value pairs) and
 * {@link http://jqueryui.com/dialog/ JUI Dialog page} for general description
 * and demo.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiDialog extends CJuiWidget
{
	/**
	 * @var string the name of the container element that contains all panels. Defaults to 'div'.
	 */
	public $tagName='div';

	/**
	 * Renders the open tag of the dialog.
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
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').dialog($options);");

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";
	}

	/**
	 * Renders the close tag of the dialog.
	 */
	public function run()
	{
		echo CHtml::closeTag($this->tagName);
	}
}