<?php
/**
 * CTextHighlighter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter').'.php');
require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter.Renderer.Html').'.php');

/**
 * CTextHighlighter does syntax highlighting for its body content.
 *
 * The language of the syntax to be applied is specified via {@link language} property.
 * Currently, CTextHighlighter supports the following languages:
 * ABAP, CPP, CSS, DIFF, DTD, HTML, JAVA, JAVASCRIPT, MYSQL, PERL,
 * PHP, PYTHON, RUBY, SQL, XML. By setting {@link showLineNumbers}
 * to true, the highlighted result may be shown with line numbers.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CTextHighlighter extends COutputProcessor
{
	/**
	 * @var string the language whose syntax is to be used for highlighting.
	 * Valid values are those file names (without suffix) that are contained
	 * in 'vendors/TextHighlighter/Text/Highlighter'. Currently, the following
	 * languages are supported:
	 * ABAP, CPP, CSS, DIFF, DTD, HTML, JAVA, JAVASCRIPT,
	 * MYSQL, PERL, PHP, PYTHON, RUBY, SQL, XML
	 * If a language is not supported, it will be displayed as plain text.
	 * Language names are case-insensitive.
	 */
	public $language;
	/**
	 * @var boolean whether to show line numbers in the highlighted result. Defaults to false.
	 * @see lineNumberStyle
	 */
	public $showLineNumbers=false;
	/**
	 * @var string the style of line number display. It can be either 'list' or 'table'. Defaults to 'list'.
	 * @see showLineNumbers
	 */
	public $lineNumberStyle='list';
	/**
	 * @var integer tab size. Defaults to 4.
	 */
	public $tabSize=4;
	/**
	 * @var mixed the CSS file used for the widget. Defaults to null, meaning
	 * using the default CSS file included together with the widget.
	 * If false, no CSS file will be used. Otherwise, the specified CSS file
	 * will be included when using this widget.
	 */
	public $cssFile;
	/**
	 * @var array the HTML attributes to be applied to the container element.
	 * The highlighted content is contained in a DIV element.
	 */
	public $containerOptions=array();


	/**
	 * Processes the captured output.
     * This method highlights the output according to the syntax of the specified {@link language}.
	 * @param string $output the captured output to be processed
	 */
	public function processOutput($output)
	{
		$output=$this->highlight($output);
		parent::processOutput($output);
	}

	/**
	 * Highlights the content by the syntax of the specified language.
	 * @param string $content the content to be highlighted.
	 * @return string the highlighted content
	 */
	public function highlight($content)
	{
		$this->registerClientScript();

		$options['use_language']=true;
		$options['tabsize']=$this->tabSize;
		if($this->showLineNumbers)
			$options['numbers']=($this->lineNumberStyle==='list')?HL_NUMBERS_LI:HL_NUMBERS_TABLE;

		$highlighter=empty($this->language)?false:Text_Highlighter::factory($this->language);
		if($highlighter===false)
			$o='<pre>'.CHtml::encode($content).'</pre>';
		else
		{
			$highlighter->setRenderer(new Text_Highlighter_Renderer_Html($options));
			$o=preg_replace('/<span\s+[^>]*>(\s*)<\/span>/','\1',$highlighter->highlight($content));
		}

		return CHtml::tag('div',$this->containerOptions,$o);
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 */
	public function registerClientScript()
	{
		if($this->cssFile!==false)
			self::registerCssFile($this->cssFile);
	}

	/**
	 * Registers the needed CSS file.
	 * @param string $url the CSS URL. If null, a default CSS URL will be used.
	 */
	public static function registerCssFile($url=null)
	{
		if($url===null)
			$url=CHtml::asset(Yii::getPathOfAlias('system.vendors.TextHighlighter.highlight').'.css');
		Yii::app()->getClientScript()->registerCssFile($url);
	}
}
