<?php
/**
 * CJuiAccordion class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @author Qiang XUe <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiAccordion displays an accordion widget.
 *
 * CJuiAccordion encapsulates the {@link http://jqueryui.com/demos/accordion/ JUI Accordion}
 * plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiAccordion',array(
 *     'panels'=>array(
 *         'panel 1'=>'content for panel 1',
 *         'panel 2'=>'content for panel 2',
 *         // panel 3 contains the content rendered by a partial view
 *         'panel 3'=>$this->renderPartial('_partial',null,true),
 *     ),
 *     // additional javascript options for the accordion plugin
 *     'options'=>array(
 *         'animated'=>'bounceslide',
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI accordion plugin. Please refer to
 * the {@link http://jqueryui.com/demos/accordion/ JUI Accordion} documentation
 * for possible options (name-value pairs).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @author Qiang XUe <qiang.xue@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiAccordion extends CJuiWidget
{
	/**
	 * @var array list of panels (panel title=>panel content).
	 * Note that neither panel title nor panel content will be HTML-encoded.
	 */
	public $panels=array();
	/**
	 * @var string the name of the container element that contains all panels. Defaults to 'div'.
	 */
	public $tagName='div';
	/**
	 * @var string the template that is used to generated every panel header.
	 * The token "{title}" in the template will be replaced with the panel title.
	 * Note that if you make change to this template, you may also need to adjust
	 * the 'header' setting in {@link options}.
	 */
	public $headerTemplate='<h3><a href="#">{title}</a></h3>';
	/**
	 * @var string the template that is used to generated every panel content.
	 * The token "{content}" in the template will be replaced with the panel content.
	 */
	public $contentTemplate='<div>{content}</div>';

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

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";
		foreach($this->panels as $title=>$content)
		{
			echo strtr($this->headerTemplate,array('{title}'=>$title))."\n";
			echo strtr($this->contentTemplate,array('{content}'=>$content))."\n";
		}
		echo CHtml::closeTag($this->tagName);

		$options=CJavaScript::encode($this->options);
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').accordion($options);");
	}
}