<?php
/**
 * CJuiTabs class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiTabs displays a tabs widget.
 *
 * CJuiTabs encapsulates the {@link http://jqueryui.com/demos/tabs/ JUI tabs}
 * plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiTabs',array(
 *     'tabs'=>array(
 *         'StaticTab 1'=>'Content for tab 1',
 *         'StaticTab 2'=>array('content'=>'Content for tab 2', 'id'=>'tab2'),
 *         // panel 3 contains the content rendered by a partial view
 *         'AjaxTab'=>array('ajax'=>$ajaxUrl),
 *     ),
 *     // additional javascript options for the tabs plugin
 *     'options'=>array(
 *         'collapsible'=>true,
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI tabs plugin. Please refer to
 * the {@link http://jqueryui.com/demos/tabs/ JUI tabs} documentation
 * for possible options (name-value pairs).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiTabs extends CJuiWidget
{
	/**
	 * @var array list of tabs (tab title=>tab content).
	 * Note that the tab title will not be HTML-encoded.
	 * The tab content can be either a string or an array. When it is an array, it can
	 * be in one of the following two formats:
	 * <pre>
	 * array('id'=>'myTabID', 'content'=>'tab content')
	 * array('id'=>'myTabID', 'ajax'=>URL)
	 * </pre>
	 * where the 'id' element is optional. The second format allows the tab content
	 * to be dynamically fetched from the specified URL via AJAX. The URL can be either
	 * a string or an array. If an array, it will be normalized into a URL using {@link CHtml::normalizeUrl}.
	 */
	public $tabs=array();
	/**
	 * @var string the name of the container element that contains all panels. Defaults to 'div'.
	 */
	public $tagName='div';
	/**
	 * @var string the template that is used to generated every panel title.
	 * The token "{title}" in the template will be replaced with the panel title and
	 * the token "{url}" will be replaced with "#TabID" or with the url of the ajax request.
	 */
	public $headerTemplate='<li><a href="{url}" title="{id}">{title}</a></li>';
	/**
	 * @var string the template that is used to generated every tab content.
	 * The token "{content}" in the template will be replaced with the panel content
	 * and the token "{id}" with the tab ID.
	 */
	public $contentTemplate='<div id="{id}">{content}</div>';

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

		$tabsOut="";
		$contentOut="";
		$tabCount=0;

		foreach($this->tabs as $title=>$content)
		{
			$tabId=(is_array($content) && isset($content['id']))?$content['id']:$id.'_tab_'.$tabCount++;

			if(!is_array($content))
			{
				$tabsOut.=strtr($this->headerTemplate,array('{title}'=>$title,'{url}'=>'#'.$tabId,'{id}'=>'#'.$tabId))."\n";
				$contentOut.=strtr($this->contentTemplate,array('{content}'=>$content,'{id}'=>$tabId))."\n";
			}
			elseif(isset($content['ajax']))
			{
				$tabsOut.=strtr($this->headerTemplate,array('{title}'=>$title,'{url}'=>CHtml::normalizeUrl($content['ajax']),'{id}'=>'#'.$tabId))."\n";
			}
			else
			{
				$tabsOut.=strtr($this->headerTemplate,array('{title}'=>$title,'{url}'=>'#'.$tabId,'{id}'=>$tabId))."\n";
				if(isset($content['content']))
					$contentOut.=strtr($this->contentTemplate,array('{content}'=>$content['content'],'{id}'=>$tabId))."\n";
			}
		}
		echo "<ul>\n".$tabsOut."</ul>\n";
		echo $contentOut;
		echo CHtml::closeTag($this->tagName)."\n";

		$options=CJavaScript::encode($this->options);
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').tabs($options);");
	}

	/**
	 * Registers the core script files.
	 * This method overrides the parent implementation by registering the cookie plugin when cookie option is used.
	 */
	protected function registerCoreScripts()
	{
		parent::registerCoreScripts();
		if(isset($this->options['cookie']))
			Yii::app()->getClientScript()->registerCoreScript('cookie');
	}
}