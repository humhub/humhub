<?php
/**
 * CListPager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CListPager displays a dropdown list that contains options leading to different pages of target.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets.pagers
 * @since 1.0
 */
class CListPager extends CBasePager
{
	/**
	 * @var string the text shown before page buttons. Defaults to 'Go to page: '.
	 */
	public $header;
	/**
	 * @var string the text shown after page buttons.
	 */
	public $footer;
	/**
	 * @var string the text displayed as a prompt option in the dropdown list. Defaults to null, meaning no prompt.
	 */
	public $promptText;
	/**
	 * @var string the format string used to generate page selection text.
	 * The sprintf function will be used to perform the formatting.
	 */
	public $pageTextFormat;
	/**
	 * @var array HTML attributes for the enclosing 'div' tag.
	 */
	public $htmlOptions=array();

	/**
	 * Initializes the pager by setting some default property values.
	 */
	public function init()
	{
		if($this->header===null)
			$this->header=Yii::t('yii','Go to page: ');
		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id']=$this->getId();
		if($this->promptText!==null)
			$this->htmlOptions['prompt']=$this->promptText;
		if(!isset($this->htmlOptions['onchange']))
			$this->htmlOptions['onchange']="if(this.value!='') {window.location=this.value;};";
	}

	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run()
	{
		if(($pageCount=$this->getPageCount())<=1)
			return;
		$pages=array();
		for($i=0;$i<$pageCount;++$i)
			$pages[$this->createPageUrl($i)]=$this->generatePageText($i);
		$selection=$this->createPageUrl($this->getCurrentPage());
		echo $this->header;
		echo CHtml::dropDownList($this->getId(),$selection,$pages,$this->htmlOptions);
		echo $this->footer;
	}

	/**
	 * Generates the list option for the specified page number.
	 * You may override this method to customize the option display.
	 * @param integer $page zero-based page number
	 * @return string the list option for the page number
	 */
	protected function generatePageText($page)
	{
		if($this->pageTextFormat!==null)
			return sprintf($this->pageTextFormat,$page+1);
		else
			return $page+1;
	}
}