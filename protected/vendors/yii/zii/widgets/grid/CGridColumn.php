<?php
/**
 * CGridColumn class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGridColumn is the base class for all grid view column classes.
 *
 * A CGridColumn object represents the specification for rendering the cells in
 * a particular grid view column.
 *
 * In a column, there is one header cell, multiple data cells, and an optional footer cell.
 * Child classes may override {@link renderHeaderCellContent}, {@link renderDataCellContent}
 * and {@link renderFooterCellContent} to customize how these cells are rendered.
 *
 * @property boolean $hasFooter Whether this column has a footer cell.
 * This is determined based on whether {@link footer} is set.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package zii.widgets.grid
 * @since 1.1
 */
abstract class CGridColumn extends CComponent
{
	/**
	 * @var string the ID of this column. This value should be unique among all grid view columns.
	 * If this is not set, it will be assigned one automatically.
	 */
	public $id;
	/**
	 * @var CGridView the grid view object that owns this column.
	 */
	public $grid;
	/**
	 * @var string the header cell text. Note that it will not be HTML-encoded.
	 */
	public $header;
	/**
	 * @var string the footer cell text. Note that it will not be HTML-encoded.
	 */
	public $footer;
	/**
	 * @var boolean whether this column is visible. Defaults to true.
	 */
	public $visible=true;
	/**
	 * @var string a PHP expression that is evaluated for every data cell and whose result
	 * is used as the CSS class name for the data cell. In this expression, you can use the following variables:
	 * <ul>
	 *   <li><code>$row</code> the row number (zero-based)</li>
	 *   <li><code>$data</code> the data model for the row</li>
	 *   <li><code>$this</code> the column object</li>
	 * </ul>
	 * The PHP expression will be evaluated using {@link evaluateExpression}.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
	 */
	public $cssClassExpression;
	/**
	 * @var array the HTML options for the data cell tags.
	 */
	public $htmlOptions=array();
	/**
	 * @var array the HTML options for the filter cell tag.
	 */
	public $filterHtmlOptions=array();
	/**
	 * @var array the HTML options for the header cell tag.
	 */
	public $headerHtmlOptions=array();
	/**
	 * @var array the HTML options for the footer cell tag.
	 */
	public $footerHtmlOptions=array();

	/**
	 * Constructor.
	 * @param CGridView $grid the grid view that owns this column.
	 */
	public function __construct($grid)
	{
		$this->grid=$grid;
	}

	/**
	 * Initializes the column.
	 * This method is invoked by the grid view when it initializes itself before rendering.
	 * You may override this method to prepare the column for rendering.
	 */
	public function init()
	{
	}

	/**
	 * @return boolean whether this column has a footer cell.
	 * This is determined based on whether {@link footer} is set.
	 */
	public function getHasFooter()
	{
		return $this->footer!==null;
	}

	/**
	 * Renders the filter cell.
	 * @since 1.1.1
	 */
	public function renderFilterCell()
	{
		echo CHtml::openTag('td',$this->filterHtmlOptions);
		$this->renderFilterCellContent();
		echo "</td>";
	}

	/**
	 * Renders the header cell.
	 */
	public function renderHeaderCell()
	{
		$this->headerHtmlOptions['id']=$this->id;
		echo CHtml::openTag('th',$this->headerHtmlOptions);
		$this->renderHeaderCellContent();
		echo "</th>";
	}

	/**
	 * Renders a data cell.
	 * @param integer $row the row number (zero-based)
	 */
	public function renderDataCell($row)
	{
		$data=$this->grid->dataProvider->data[$row];
		$options=$this->htmlOptions;
		if($this->cssClassExpression!==null)
		{
			$class=$this->evaluateExpression($this->cssClassExpression,array('row'=>$row,'data'=>$data));
			if(!empty($class))
			{
				if(isset($options['class']))
					$options['class'].=' '.$class;
				else
					$options['class']=$class;
			}
		}
		echo CHtml::openTag('td',$options);
		$this->renderDataCellContent($row,$data);
		echo '</td>';
	}

	/**
	 * Renders the footer cell.
	 */
	public function renderFooterCell()
	{
		echo CHtml::openTag('td',$this->footerHtmlOptions);
		$this->renderFooterCellContent();
		echo '</td>';
	}

	/**
	 * Renders the header cell content.
	 * The default implementation simply renders {@link header}.
	 * This method may be overridden to customize the rendering of the header cell.
	 */
	protected function renderHeaderCellContent()
	{
		echo trim($this->header)!=='' ? $this->header : $this->grid->blankDisplay;
	}

	/**
	 * Renders the footer cell content.
	 * The default implementation simply renders {@link footer}.
	 * This method may be overridden to customize the rendering of the footer cell.
	 */
	protected function renderFooterCellContent()
	{
		echo trim($this->footer)!=='' ? $this->footer : $this->grid->blankDisplay;
	}

	/**
	 * Renders the data cell content.
	 * This method SHOULD be overridden to customize the rendering of the data cell.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 */
	protected function renderDataCellContent($row,$data)
	{
		echo $this->grid->blankDisplay;
	}

	/**
	 * Renders the filter cell content.
	 * The default implementation simply renders a space.
	 * This method may be overridden to customize the rendering of the filter cell (if any).
	 * @since 1.1.1
	 */
	protected function renderFilterCellContent()
	{
		echo $this->grid->blankDisplay;
	}
}
