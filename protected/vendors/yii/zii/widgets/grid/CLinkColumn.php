<?php
/**
 * CLinkColumn class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.grid.CGridColumn');

/**
 * CLinkColumn represents a grid view column that renders a hyperlink in each of its data cells.
 *
 * The {@link label} and {@link url} properties determine how each hyperlink will be rendered.
 * The {@link labelExpression}, {@link urlExpression} properties may be used instead if they are available.
 * In addition, if {@link imageUrl} is set, an image link will be rendered.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package zii.widgets.grid
 * @since 1.1
 */
class CLinkColumn extends CGridColumn
{
	/**
	 * @var string the label to the hyperlinks in the data cells. Note that the label will not
	 * be HTML-encoded when rendering. This property is ignored if {@link labelExpression} is set.
	 * @see labelExpression
	 */
	public $label='Link';
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will be rendered
	 * as the label of the hyperlink of the data cells. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $labelExpression;
	/**
	 * @var string the URL to the image. If this is set, an image link will be rendered.
	 */
	public $imageUrl;
	/**
	 * @var string the URL of the hyperlinks in the data cells.
	 * This property is ignored if {@link urlExpression} is set.
	 * @see urlExpression
	 */
	public $url='javascript:void(0)';
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will be rendered
	 * as the URL of the hyperlink of the data cells. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $urlExpression;
	/**
	 * @var array the HTML options for the data cell tags.
	 */
	public $htmlOptions=array('class'=>'link-column');
	/**
	 * @var array the HTML options for the header cell tag.
	 */
	public $headerHtmlOptions=array('class'=>'link-column');
	/**
	 * @var array the HTML options for the footer cell tag.
	 */
	public $footerHtmlOptions=array('class'=>'link-column');
	/**
	 * @var array the HTML options for the hyperlinks
	 */
	public $linkHtmlOptions=array();

	/**
	 * Renders the data cell content.
	 * This method renders a hyperlink in the data cell.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 */
	protected function renderDataCellContent($row,$data)
	{
		if($this->urlExpression!==null)
			$url=$this->evaluateExpression($this->urlExpression,array('data'=>$data,'row'=>$row));
		else
			$url=$this->url;
		if($this->labelExpression!==null)
			$label=$this->evaluateExpression($this->labelExpression,array('data'=>$data,'row'=>$row));
		else
			$label=$this->label;
		$options=$this->linkHtmlOptions;
		if(is_string($this->imageUrl))
			echo CHtml::link(CHtml::image($this->imageUrl,$label),$url,$options);
		else
			echo CHtml::link($label,$url,$options);
	}
}
