<?php
/**
 * CTreeView class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTreeView displays a tree view of hierarchical data.
 *
 * It encapsulates the excellent tree view plugin for jQuery
 * ({@link http://bassistance.de/jquery-plugins/jquery-plugin-treeview/}).
 *
 * To use CTreeView, simply sets {@link data} to the data that you want
 * to present and you are there.
 *
 * CTreeView also supports dynamic data loading via AJAX. To do so, set
 * {@link url} to be the URL that can serve the tree view data upon request.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CTreeView extends CWidget
{
	/**
	 * @var array the data that can be used to generate the tree view content.
	 * Each array element corresponds to a tree view node with the following structure:
	 * <ul>
	 * <li>text: string, required, the HTML text associated with this node.</li>
	 * <li>expanded: boolean, optional, whether the tree view node is expanded.</li>
	 * <li>id: string, optional, the ID identifying the node. This is used
	 *   in dynamic loading of tree view (see {@link url}).</li>
	 * <li>hasChildren: boolean, optional, defaults to false, whether clicking on this
	 *   node should trigger dynamic loading of more tree view nodes from server.
	 *   The {@link url} property must be set in order to make this effective.</li>
	 * <li>children: array, optional, child nodes of this node.</li>
	 * <li>htmlOptions: array, additional HTML attributes (see {@link CHtml::tag}).
	 *   This option has been available since version 1.1.7.</li>
	 * </ul>
	 * Note, anything enclosed between the beginWidget and endWidget calls will
	 * also be treated as tree view content, which appends to the content generated
	 * from this data.
	 */
	public $data;
	/**
	 * @var mixed the CSS file used for the widget. Defaults to null, meaning
	 * using the default CSS file included together with the widget.
	 * If false, no CSS file will be used. Otherwise, the specified CSS file
	 * will be included when using this widget.
	 */
	public $cssFile;
	/**
	 * @var string|array the URL to which the treeview can be dynamically loaded (in AJAX).
	 * See {@link CHtml::normalizeUrl} for possible URL formats.
	 * Setting this property will enable the dynamic treeview loading.
	 * When the page is displayed, the browser will request this URL with a GET parameter
	 * named 'root' whose value is 'source'. The server script should then generate the
	 * needed tree view data corresponding to the root of the tree (see {@link saveDataAsJson}.)
	 * When a node has a CSS class 'hasChildren', then expanding this node will also
	 * cause a dynamic loading of its child nodes. In this case, the value of the 'root' GET parameter
	 * is the 'id' property of the node.
	 */
	public $url;
	/**
	 * @var string|integer animation speed. This can be one of the three predefined speeds
	 * ("slow", "normal", or "fast") or the number of milliseconds to run the animation (e.g. 1000).
	 * If not set, no animation is used.
	 */
	public $animated;
	/**
	 * @var boolean whether the tree should start with all branches collapsed. Defaults to false.
	 */
	public $collapsed;
	/**
	 * @var string container for a tree-control, allowing the user to expand, collapse and toggle all branches with one click.
	 * In the container, clicking on the first hyperlink will collapse the tree;
	 * the second hyperlink will expand the tree; while the third hyperlink will toggle the tree.
	 * The property should be a valid jQuery selector (e.g. '#treecontrol' where 'treecontrol' is
	 * the ID of the 'div' element containing the hyperlinks.)
	 */
	public $control;
	/**
	 * @var boolean set to allow only one branch on one level to be open (closing siblings which opening).
	 * Defaults to false.
	 */
	public $unique;
	/**
	 * @var string Callback when toggling a branch. Arguments: "this" refers to the UL that was shown or hidden
	 */
	public $toggle;
	/**
	 * @var string Persist the tree state in cookies or the page location. If set to "location", looks for
	 * the anchor that matches location.href and activates that part of the treeview it.
	 * Great for href-based state-saving. If set to "cookie", saves the state of the tree on
	 * each click to a cookie and restores that state on page load.
	 */
	public $persist;
	/**
	 * @var string The cookie name to use when persisting via persist:"cookie". Defaults to 'treeview'.
	 */
	public $cookieId;
	/**
	 * @var boolean Set to skip rendering of classes and hitarea divs, assuming that is done by the serverside. Defaults to false.
	 */
	public $prerendered;
	/**
	 * @var array additional options that can be passed to the constructor of the treeview js object.
	 */
	public $options=array();
	/**
	 * @var array additional HTML attributes that will be rendered in the UL tag.
	 * The default tree view CSS has defined the following CSS classes which can be enabled
	 * by specifying the 'class' option here:
	 * <ul>
	 * <li>treeview-black</li>
	 * <li>treeview-gray</li>
	 * <li>treeview-red</li>
	 * <li>treeview-famfamfam</li>
	 * <li>filetree</li>
	 * </ul>
	 */
	public $htmlOptions;


	/**
	 * Initializes the widget.
	 * This method registers all needed client scripts and renders
	 * the tree view content.
	 */
	public function init()
	{
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$id=$this->htmlOptions['id']=$this->getId();
		if($this->url!==null)
			$this->url=CHtml::normalizeUrl($this->url);
		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('treeview');
		$options=$this->getClientOptions();
		$options=$options===array()?'{}' : CJavaScript::encode($options);
		$cs->registerScript('Yii.CTreeView#'.$id,"jQuery(\"#{$id}\").treeview($options);");
		if($this->cssFile===null)
			$cs->registerCssFile($cs->getCoreScriptUrl().'/treeview/jquery.treeview.css');
		elseif($this->cssFile!==false)
			$cs->registerCssFile($this->cssFile);

		echo CHtml::tag('ul',$this->htmlOptions,false,false)."\n";
		echo self::saveDataAsHtml($this->data);
	}

	/**
	 * Ends running the widget.
	 */
	public function run()
	{
		echo "</ul>";
	}

	/**
	 * @return array the javascript options
	 */
	protected function getClientOptions()
	{
		$options=$this->options;
		foreach(array('url','animated','collapsed','control','unique','toggle','persist','cookieId','prerendered') as $name)
		{
			if($this->$name!==null)
				$options[$name]=$this->$name;
		}
		return $options;
	}

	/**
	 * Generates tree view nodes in HTML from the data array.
	 * @param array $data the data for the tree view (see {@link data} for possible data structure).
	 * @return string the generated HTML for the tree view
	 */
	public static function saveDataAsHtml($data)
	{
		$html='';
		if(is_array($data))
		{
			foreach($data as $node)
			{
				if(!isset($node['text']))
					continue;

				if(isset($node['expanded']))
					$css=$node['expanded'] ? 'open' : 'closed';
				else
					$css='';

				if(isset($node['hasChildren']) && $node['hasChildren'])
				{
					if($css!=='')
						$css.=' ';
					$css.='hasChildren';
				}

				$options=isset($node['htmlOptions']) ? $node['htmlOptions'] : array();
				if($css!=='')
				{
					if(isset($options['class']))
						$options['class'].=' '.$css;
					else
						$options['class']=$css;
				}

				if(isset($node['id']))
					$options['id']=$node['id'];

				$html.=CHtml::tag('li',$options,$node['text'],false);
				if(!empty($node['children']))
				{
					$html.="\n<ul>\n";
					$html.=self::saveDataAsHtml($node['children']);
					$html.="</ul>\n";
				}
				$html.=CHtml::closeTag('li')."\n";
			}
		}
		return $html;
	}

	/**
	 * Saves tree view data in JSON format.
	 * This method is typically used in dynamic tree view loading
	 * when the server code needs to send to the client the dynamic
	 * tree view data.
	 * @param array $data the data for the tree view (see {@link data} for possible data structure).
	 * @return string the JSON representation of the data
	 */
	public static function saveDataAsJson($data)
	{
		if(empty($data))
			return '[]';
		else
			return CJavaScript::jsonEncode($data);
	}
}
