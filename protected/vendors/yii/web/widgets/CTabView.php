<?php
/**
 * CTabView class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTabView displays contents in multiple tabs.
 *
 * At any time, only one tab is visible. Users can click on the tab header
 * to switch to see another tab of content.
 *
 * JavaScript is used to control the tab switching. If JavaScript is disabled,
 * CTabView still manages to display the content in a semantically appropriate way.
 *
 * To specify contents and their tab structure, configure the {@link tabs} property.
 * The {@link tabs} property takes an array with tab ID being mapped tab definition.
 * Each tab definition is an array of the following structure:
 * <ul>
 * <li>title: the tab title.</li>
 * <li>content: the content to be displayed in the tab.</li>
 * <li>view: the name of the view to be displayed in this tab.
 * The view will be rendered using the current controller's
 * {@link CController::renderPartial} method.
 * When both 'content' and 'view' are specified, 'content' will take precedence.
 * </li>
 * <li>url: a URL that the user browser will be redirected to when clicking on this tab.</li>
 * <li>data: array (name=>value), this will be passed to the view when 'view' is specified.</li>
 * </ul>
 *
 * For example, the {@link tabs} property can be configured as follows,
 * <pre>
 * $this->widget('CTabView', array(
 *     'tabs'=>array(
 *         'tab1'=>array(
 *             'title'=>'tab 1 title',
 *             'view'=>'view1',
 *             'data'=>array('model'=>$model),
 *         ),
 *         'tab2'=>array(
 *             'title'=>'tab 2 title',
 *             'url'=>'http://www.yiiframework.com/',
 *         ),
 *     ),
 * ));
 * </pre>
 *
 * By default, the first tab will be activated. To activate a different tab
 * when the page is initially loaded, set {@link activeTab} to be the ID of the desired tab.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CTabView extends CWidget
{
	/**
	 * Default CSS class for the tab container
	 */
	const CSS_CLASS='yiiTab';

	/**
	 * @var mixed the CSS file used for the widget. Defaults to null, meaning
	 * using the default CSS file included together with the widget.
	 * If false, no CSS file will be used. Otherwise, the specified CSS file
	 * will be included when using this widget.
	 */
	public $cssFile;
	/**
	 * @var string the ID of the tab that should be activated when the page is initially loaded.
	 * If not set, the first tab will be activated.
	 */
	public $activeTab;
	/**
	 * @var array the data that will be passed to the partial view rendered by each tab.
	 */
	public $viewData;
	/**
	 * @var array additional HTML options to be rendered in the container tag.
	 */
	public $htmlOptions;
	/**
	 * @var array tab definitions. The array keys are the IDs,
	 * and the array values are the corresponding tab contents.
	 * Each array value must be an array with the following elements:
	 * <ul>
	 * <li>title: the tab title. You need to make sure this is HTML-encoded.</li>
	 * <li>content: the content to be displayed in the tab.</li>
	 * <li>view: the name of the view to be displayed in this tab.
	 * The view will be rendered using the current controller's
	 * {@link CController::renderPartial} method.
	 * When both 'content' and 'view' are specified, 'content' will take precedence.
	 * </li>
	 * <li>url: a URL that the user browser will be redirected to when clicking on this tab.</li>
	 * <li>data: array (name=>value), this will be passed to the view when 'view' is specified.
	 * This option is available since version 1.1.1.</li>
	 * <li>visible: whether this tab is visible. Defaults to true.
	 * this option is available since version 1.1.11.</li>
	 * </ul>
	 * <pre>
	 * array(
	 *     'tab1'=>array(
	 *           'title'=>'tab 1 title',
	 *           'view'=>'view1',
	 *     ),
	 *     'tab2'=>array(
	 *           'title'=>'tab 2 title',
	 *           'url'=>'http://www.yiiframework.com/',
	 *     ),
	 * )
	 * </pre>
	 */
	public $tabs=array();

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		foreach($this->tabs as $id=>$tab)
			if(isset($tab['visible']) && $tab['visible']==false)
				unset($this->tabs[$id]);

		if(empty($this->tabs))
			return;

		if($this->activeTab===null || !isset($this->tabs[$this->activeTab]))
		{
			reset($this->tabs);
			list($this->activeTab, )=each($this->tabs);
		}

		$htmlOptions=$this->htmlOptions;
		if(isset($this->htmlOptions['id']))
			$this->id=$this->htmlOptions['id'];
		else
			$htmlOptions['id']=$this->id;
		if(!isset($htmlOptions['class']))
			$htmlOptions['class']=self::CSS_CLASS;

		$this->registerClientScript();

		echo CHtml::openTag('div',$htmlOptions)."\n";
		$this->renderHeader();
		$this->renderBody();
		echo CHtml::closeTag('div');
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 */
	public function registerClientScript()
	{
		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('yiitab');
		$id=$this->getId();
		$cs->registerScript('Yii.CTabView#'.$id,"jQuery(\"#{$id}\").yiitab();");

		if($this->cssFile!==false)
			self::registerCssFile($this->cssFile);
	}

	/**
	 * Registers the needed CSS file.
	 * @param string $url the CSS URL. If null, a default CSS URL will be used.
	 */
	public static function registerCssFile($url=null)
	{
		$cs=Yii::app()->getClientScript();
		if($url===null)
			$url=$cs->getCoreScriptUrl().'/yiitab/jquery.yiitab.css';
		$cs->registerCssFile($url,'screen');
	}

	/**
	 * Renders the header part.
	 */
	protected function renderHeader()
	{
		echo "<ul class=\"tabs\">\n";
		foreach($this->tabs as $id=>$tab)
		{
			$title=isset($tab['title'])?$tab['title']:'undefined';
			$active=$id===$this->activeTab?' class="active"' : '';
			$url=isset($tab['url'])?$tab['url']:"#{$id}";
			echo "<li><a href=\"{$url}\"{$active}>{$title}</a></li>\n";
		}
		echo "</ul>\n";
	}

	/**
	 * Renders the body part.
	 */
	protected function renderBody()
	{
		foreach($this->tabs as $id=>$tab)
		{
			$inactive=$id!==$this->activeTab?' style="display:none"' : '';
			echo "<div class=\"view\" id=\"{$id}\"{$inactive}>\n";
			if(isset($tab['content']))
				echo $tab['content'];
			elseif(isset($tab['view']))
			{
				if(isset($tab['data']))
				{
					if(is_array($this->viewData))
						$data=array_merge($this->viewData, $tab['data']);
					else
						$data=$tab['data'];
				}
				else
					$data=$this->viewData;
				$this->getController()->renderPartial($tab['view'], $data);
			}
			echo "</div><!-- {$id} -->\n";
		}
	}
}
