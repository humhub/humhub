<?php
/**
 * CMarkdownParser class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once(Yii::getPathOfAlias('system.vendors.markdown.markdown').'.php');
if(!class_exists('HTMLPurifier_Bootstrap',false))
{
	require_once(Yii::getPathOfAlias('system.vendors.htmlpurifier').DIRECTORY_SEPARATOR.'HTMLPurifier.standalone.php');
	HTMLPurifier_Bootstrap::registerAutoload();
}

/**
 * CMarkdownParser is a wrapper of {@link http://michelf.com/projects/php-markdown/extra/ MarkdownExtra_Parser}.
 *
 * CMarkdownParser extends MarkdownExtra_Parser by using Text_Highlighter
 * to highlight code blocks with specific language syntax.
 * In particular, if a code block starts with the following:
 * <pre>
 * [language]
 * </pre>
 * The syntax for the specified language will be used to highlight
 * code block. The languages supported include (case-insensitive):
 * ABAP, CPP, CSS, DIFF, DTD, HTML, JAVA, JAVASCRIPT,
 * MYSQL, PERL, PHP, PYTHON, RUBY, SQL, XML
 *
 * You can also specify options to be passed to the syntax highlighter. For example:
 * <pre>
 * [php showLineNumbers=1]
 * </pre>
 * which will show line numbers in each line of the code block.
 *
 * For details about the standard markdown syntax, please check the following:
 * <ul>
 * <li>{@link http://daringfireball.net/projects/markdown/syntax official markdown syntax}</li>
 * <li>{@link http://michelf.com/projects/php-markdown/extra/ markdown extra syntax}</li>
 * </ul>
 *
 * @property string $defaultCssFile The default CSS file that is used to highlight code blocks.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.utils
 * @since 1.0
 */
class CMarkdownParser extends MarkdownExtra_Parser
{
	/**
	 * @var string the css class for the div element containing
	 * the code block that is highlighted. Defaults to 'hl-code'.
	 */
	public $highlightCssClass='hl-code';
	/**
	 * @var mixed the options to be passed to {@link http://htmlpurifier.org HTML Purifier}.
	 * This can be a HTMLPurifier_Config object,  an array of directives (Namespace.Directive => Value)
	 * or the filename of an ini file.
	 * This property is used only when {@link safeTransform} is invoked.
	 * @see http://htmlpurifier.org/live/configdoc/plain.html
	 * @since 1.1.4
	 */
	public $purifierOptions=null;

	/**
	 * Transforms the content and purifies the result.
	 * This method calls the transform() method to convert
	 * markdown content into HTML content. It then
	 * uses {@link CHtmlPurifier} to purify the HTML content
	 * to avoid XSS attacks.
	 * @param string $content the markdown content
	 * @return string the purified HTML content
	 */
	public function safeTransform($content)
	{
		$content=$this->transform($content);
		$purifier=new HTMLPurifier($this->purifierOptions);
		$purifier->config->set('Cache.SerializerPath',Yii::app()->getRuntimePath());
		return $purifier->purify($content);
	}

	/**
	 * @return string the default CSS file that is used to highlight code blocks.
	 */
	public function getDefaultCssFile()
	{
		return Yii::getPathOfAlias('system.vendors.TextHighlighter.highlight').'.css';
	}

	/**
	 * Callback function when a code block is matched.
	 * @param array $matches matches
	 * @return string the highlighted code block
	 */
	public function _doCodeBlocks_callback($matches)
	{
		$codeblock = $this->outdent($matches[1]);
		if(($codeblock = $this->highlightCodeBlock($codeblock)) !== null)
			return "\n\n".$this->hashBlock($codeblock)."\n\n";
		else
			return parent::_doCodeBlocks_callback($matches);
	}

	/**
	 * Callback function when a fenced code block is matched.
	 * @param array $matches matches
	 * @return string the highlighted code block
	 */
	public function _doFencedCodeBlocks_callback($matches)
	{
		return "\n\n".$this->hashBlock($this->highlightCodeBlock($matches[2]))."\n\n";
	}

	/**
	 * Highlights the code block.
	 * @param string $codeblock the code block
	 * @return string the highlighted code block. Null if the code block does not need to highlighted
	 */
	protected function highlightCodeBlock($codeblock)
	{
		if(($tag=$this->getHighlightTag($codeblock))!==null && ($highlighter=$this->createHighLighter($tag)))
		{
			$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);
			$tagLen = strpos($codeblock, $tag)+strlen($tag);
			$codeblock = ltrim(substr($codeblock, $tagLen));
			$output=preg_replace('/<span\s+[^>]*>(\s*)<\/span>/', '\1', $highlighter->highlight($codeblock));
			return "<div class=\"{$this->highlightCssClass}\">".$output."</div>";
		}
		else
			return "<pre>".CHtml::encode($codeblock)."</pre>";
	}

	/**
	 * Returns the user-entered highlighting options.
	 * @param string $codeblock code block with highlighting options.
	 * @return string the user-entered highlighting options. Null if no option is entered.
	 */
	protected function getHighlightTag($codeblock)
	{
		$str = trim(current(preg_split("/\r|\n/", $codeblock,2)));
		if(strlen($str) > 2 && $str[0] === '[' && $str[strlen($str)-1] === ']')
			return $str;
	}

	/**
	 * Creates a highlighter instance.
	 * @param string $options the user-entered options
	 * @return Text_Highlighter the highlighter instance
	 */
	protected function createHighLighter($options)
	{
		if(!class_exists('Text_Highlighter', false))
		{
			require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter').'.php');
			require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter.Renderer.Html').'.php');
		}
		$lang = current(preg_split('/\s+/', substr(substr($options,1), 0,-1),2));
		$highlighter = Text_Highlighter::factory($lang);
		if($highlighter)
			$highlighter->setRenderer(new Text_Highlighter_Renderer_Html($this->getHighlightConfig($options)));
		return $highlighter;
	}

	/**
	 * Generates the config for the highlighter.
	 * @param string $options user-entered options
	 * @return array the highlighter config
	 */
	public function getHighlightConfig($options)
	{
		$config = array('use_language'=>true);
		if( $this->getInlineOption('showLineNumbers', $options, false) )
			$config['numbers'] = HL_NUMBERS_LI;
		$config['tabsize'] = $this->getInlineOption('tabSize', $options, 4);
		return $config;
	}

	/**
	 * Generates the config for the highlighter.
	 *
	 * NOTE: This method is deprecated due to a mistake in the method name.
	 * Use {@link getHighlightConfig} instead of this.
	 *
	 * @param string $options user-entered options
	 * @return array the highlighter config
	 */
	public function getHiglightConfig($options)
	{
		return $this->getHighlightConfig($options);
	}

	/**
	 * Retrieves the specified configuration.
	 * @param string $name the configuration name
	 * @param string $str the user-entered options
	 * @param mixed $defaultValue default value if the configuration is not present
	 * @return mixed the configuration value
	 */
	protected function getInlineOption($name, $str, $defaultValue)
	{
		if(preg_match('/'.$name.'(\s*=\s*(\d+))?/i', $str, $v) && count($v) > 2)
			return $v[2];
		else
			return $defaultValue;
	}
}
