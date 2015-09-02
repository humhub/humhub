<?php
/**
 * CBaseListView class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CBaseListView is the base class for {@link CListView} and {@link CGridView}.
 *
 * CBaseListView implements the common features needed by a view wiget for rendering multiple models.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package zii.widgets
 * @since 1.1
 */
abstract class CBaseListView extends CWidget
{
	/**
	 * @var IDataProvider the data provider for the view.
	 */
	public $dataProvider;
	/**
	 * @var string the tag name for the view container. Defaults to 'div'.
	 */
	public $tagName='div';
	/**
	 * @var array the HTML options for the view container tag.
	 */
	public $htmlOptions=array();
	/**
	 * @var boolean whether to enable sorting. Note that if the {@link IDataProvider::sort} property
	 * of {@link dataProvider} is false, this will be treated as false as well. When sorting is enabled,
	 * sortable columns will have their headers clickable to trigger sorting along that column.
	 * Defaults to true.
	 * @see sortableAttributes
	 */
	public $enableSorting=true;
	/**
	 * @var boolean whether to enable pagination. Note that if the {@link IDataProvider::pagination} property
	 * of {@link dataProvider} is false, this will be treated as false as well. When pagination is enabled,
	 * a pager will be displayed in the view so that it can trigger pagination of the data display.
	 * Defaults to true.
	 */
	public $enablePagination=true;
	/**
	 * @var array|string the configuration for the pager. Defaults to <code>array('class'=>'CLinkPager')</code>.
	 * String value will be treated as the class name of the pager (<code>'ClassName'</code> value is similar
	 * to the <code>array('class'=>'ClassName')</code> value). See {@link CBasePager} and {@link CLinkPager}
	 * for more details about pager configuration array values.
	 * @see enablePagination
	 */
	public $pager=array('class'=>'CLinkPager');
	/**
	 * @var string the template to be used to control the layout of various sections in the view.
	 * These tokens are recognized: {summary}, {items} and {pager}. They will be replaced with the
	 * summary text, the items, and the pager.
	 */
	public $template="{summary}\n{items}\n{pager}";
	/**
	 * @var string the summary text template for the view. These tokens are recognized and will be replaced
	 * with the corresponding values:
	 * <ul>
	 *   <li>{start}: the starting row number (1-based) currently being displayed</li>
	 *   <li>{end}: the ending row number (1-based) currently being displayed</li>
	 *   <li>{count}: the total number of rows</li>
	 *   <li>{page}: the page number (1-based) current being displayed, available since version 1.1.3</li>
	 *   <li>{pages}: the total number of pages, available since version 1.1.3</li>
	 * </ul>
	 */
	public $summaryText;
	/**
	 * @var string the message to be displayed when {@link dataProvider} does not have any data.
	 */
	public $emptyText;
	/**
	 * @var string the HTML tag name for the container of the {@link emptyText} property.
	 */
	public $emptyTagName='span';
	/**
	 * @var string the CSS class name for the container of all data item display. Defaults to 'items'.
	 */
	public $itemsCssClass='items';
	/**
	 * @var string the CSS class name for the summary text container. Defaults to 'summary'.
	 */
	public $summaryCssClass='summary';
	/**
	 * @var string the CSS class name for the pager container. Defaults to 'pager'.
	 */
	public $pagerCssClass='pager';
	/**
	 * @var string the CSS class name that will be assigned to the widget container element
	 * when the widget is updating its content via AJAX. Defaults to 'loading'.
	 * @since 1.1.1
	 */
	public $loadingCssClass='loading';

	/**
	 * Initializes the view.
	 * This method will initialize required property values and instantiate {@link columns} objects.
	 */
	public function init()
	{
		if($this->dataProvider===null)
			throw new CException(Yii::t('zii','The "dataProvider" property cannot be empty.'));

		$this->dataProvider->getData();

		if(isset($this->htmlOptions['id']))
			$this->id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$this->id;

		if($this->enableSorting && $this->dataProvider->getSort()===false)
			$this->enableSorting=false;
		if($this->enablePagination && $this->dataProvider->getPagination()===false)
			$this->enablePagination=false;
	}

	/**
	 * Renders the view.
	 * This is the main entry of the whole view rendering.
	 * Child classes should mainly override {@link renderContent} method.
	 */
	public function run()
	{
		$this->registerClientScript();

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";

		$this->renderContent();
		$this->renderKeys();

		echo CHtml::closeTag($this->tagName);
	}

	/**
	 * Renders the main content of the view.
	 * The content is divided into sections, such as summary, items, pager.
	 * Each section is rendered by a method named as "renderXyz", where "Xyz" is the section name.
	 * The rendering results will replace the corresponding placeholders in {@link template}.
	 */
	public function renderContent()
	{
		ob_start();
		echo preg_replace_callback("/{(\w+)}/",array($this,'renderSection'),$this->template);
		ob_end_flush();
	}

	/**
	 * Renders a section.
	 * This method is invoked by {@link renderContent} for every placeholder found in {@link template}.
	 * It should return the rendering result that would replace the placeholder.
	 * @param array $matches the matches, where $matches[0] represents the whole placeholder,
	 * while $matches[1] contains the name of the matched placeholder.
	 * @return string the rendering result of the section
	 */
	protected function renderSection($matches)
	{
		$method='render'.$matches[1];
		if(method_exists($this,$method))
		{
			$this->$method();
			$html=ob_get_contents();
			ob_clean();
			return $html;
		}
		else
			return $matches[0];
	}

	/**
	 * Renders the empty message when there is no data.
	 */
	public function renderEmptyText()
	{
		$emptyText=$this->emptyText===null ? Yii::t('zii','No results found.') : $this->emptyText;
		echo CHtml::tag($this->emptyTagName, array('class'=>'empty'), $emptyText);
	}

	/**
	 * Renders the key values of the data in a hidden tag.
	 */
	public function renderKeys()
	{
		echo CHtml::openTag('div',array(
			'class'=>'keys',
			'style'=>'display:none',
			'title'=>Yii::app()->getRequest()->getUrl(),
		));
		foreach($this->dataProvider->getKeys() as $key)
			echo "<span>".CHtml::encode($key)."</span>";
		echo "</div>\n";
	}

	/**
	 * Renders the summary text.
	 */
	public function renderSummary()
	{
		if(($count=$this->dataProvider->getItemCount())<=0)
			return;

		echo '<div class="'.$this->summaryCssClass.'">';
		if($this->enablePagination)
		{
			$pagination=$this->dataProvider->getPagination();
			$total=$this->dataProvider->getTotalItemCount();
			$start=$pagination->currentPage*$pagination->pageSize+1;
			$end=$start+$count-1;
			if($end>$total)
			{
				$end=$total;
				$start=$end-$count+1;
			}
			if(($summaryText=$this->summaryText)===null)
				$summaryText=Yii::t('zii','Displaying {start}-{end} of 1 result.|Displaying {start}-{end} of {count} results.',$total);
			echo strtr($summaryText,array(
				'{start}'=>$start,
				'{end}'=>$end,
				'{count}'=>$total,
				'{page}'=>$pagination->currentPage+1,
				'{pages}'=>$pagination->pageCount,
			));
		}
		else
		{
			if(($summaryText=$this->summaryText)===null)
				$summaryText=Yii::t('zii','Total 1 result.|Total {count} results.',$count);
			echo strtr($summaryText,array(
				'{count}'=>$count,
				'{start}'=>1,
				'{end}'=>$count,
				'{page}'=>1,
				'{pages}'=>1,
			));
		}
		echo '</div>';
	}

	/**
	 * Renders the pager.
	 */
	public function renderPager()
	{
		if(!$this->enablePagination)
			return;

		$pager=array();
		$class='CLinkPager';
		if(is_string($this->pager))
			$class=$this->pager;
		elseif(is_array($this->pager))
		{
			$pager=$this->pager;
			if(isset($pager['class']))
			{
				$class=$pager['class'];
				unset($pager['class']);
			}
		}
		$pager['pages']=$this->dataProvider->getPagination();

		if($pager['pages']->getPageCount()>1)
		{
			echo '<div class="'.$this->pagerCssClass.'">';
			$this->widget($class,$pager);
			echo '</div>';
		}
		else
			$this->widget($class,$pager);
	}

	/**
	 * Registers necessary client scripts.
	 * This method is invoked by {@link run}.
	 * Child classes may override this method to register customized client scripts.
	 */
	public function registerClientScript()
	{
	}

	/**
	 * Renders the data items for the view.
	 * Each item is corresponding to a single data model instance.
	 * Child classes should override this method to provide the actual item rendering logic.
	 */
	abstract public function renderItems();
}
