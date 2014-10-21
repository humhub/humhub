<?php
/**
 * CMarkdown class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMarkdown converts the captured content from markdown syntax to HTML code.
 *
 * CMarkdown can be used as either a widget or a filter. It is a wrapper of {@link CMarkdownParser}.
 * CMarkdown adds an additional option {@link purifyOutput} which can be set true
 * so that the converted HTML code is purified before being displayed.
 *
 * For details about the markdown syntax, please check the following:
 * <ul>
 * <li>{@link http://daringfireball.net/projects/markdown/syntax official markdown syntax}</li>
 * <li>{@link http://michelf.com/projects/php-markdown/extra/ markdown extra syntax}</li>
 * <li>{@link CMarkdownParser markdown with syntax highlighting}</li>
 * </ul>
 *
 * @property CMarkdownParser $markdownParser The parser instance.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CMarkdown extends COutputProcessor
{
	/**
	 * @var mixed the CSS file used for the widget. Defaults to null, meaning
	 * using the default CSS file included together with the widget.
	 * If false, no CSS file will be used. Otherwise, the specified CSS file
	 * will be included when using this widget.
	 */
	public $cssFile;
	/**
	 * @var boolean whether to use {@link CHtmlPurifier} to purify the generated HTML code. Defaults to false.
	 */
	public $purifyOutput=false;

	private $_parser;

	/**
	 * Processes the captured output.
     * This method converts the content in markdown syntax to HTML code.
     * If {@link purifyOutput} is true, the HTML code will also be purified.
	 * @param string $output the captured output to be processed
	 * @see convert
	 */
	public function processOutput($output)
	{
		$output=$this->transform($output);
		if($this->purifyOutput)
		{
			$purifier=new CHtmlPurifier;
			$output=$purifier->purify($output);
		}
		parent::processOutput($output);
	}

	/**
	 * Converts the content in markdown syntax to HTML code.
	 * This method uses {@link CMarkdownParser} to do the conversion.
	 * @param string $output the content to be converted
	 * @return string the converted content
	 */
	public function transform($output)
	{
		$this->registerClientScript();
		return $this->getMarkdownParser()->transform($output);
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
		CTextHighlighter::registerCssFile($url);
	}

	/**
	 * Returns the markdown parser instance.
	 * This method calls {@link createMarkdownParser} to create the parser instance.
	 * Call this method multipe times will only return the same instance.
	 * @return CMarkdownParser the parser instance
	 */
	public function getMarkdownParser()
	{
		if($this->_parser===null)
			$this->_parser=$this->createMarkdownParser();
		return $this->_parser;
	}

	/**
	 * Creates a markdown parser.
	 * By default, this method creates a {@link CMarkdownParser} instance.
	 * @return CMarkdownParser the markdown parser.
	 */
	protected function createMarkdownParser()
	{
		return new CMarkdownParser;
	}
}
