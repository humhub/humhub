<?php
/**
 * CHtmlPurifier class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

if(!class_exists('HTMLPurifier_Bootstrap',false))
{
	require_once(Yii::getPathOfAlias('system.vendors.htmlpurifier').DIRECTORY_SEPARATOR.'HTMLPurifier.standalone.php');
	HTMLPurifier_Bootstrap::registerAutoload();
}

/**
 * CHtmlPurifier is wrapper of {@link http://htmlpurifier.org HTML Purifier}.
 *
 * CHtmlPurifier removes all malicious code (better known as XSS) with a thoroughly audited,
 * secure yet permissive whitelist. It will also make sure the resulting code
 * is standard-compliant.
 *
 * CHtmlPurifier can be used as either a widget or a controller filter.
 *
 * Note: since HTML Purifier is a big package, its performance is not very good.
 * You should consider either caching the purification result or purifying the user input
 * before saving to database.
 *
 * Usage as a class:
 * <pre>
 * $p = new CHtmlPurifier();
 * $p->options = array('URI.AllowedSchemes'=>array(
 *   'http' => true,
 *   'https' => true,
 * ));
 * $text = $p->purify($text);
 * </pre>
 *
 * Usage as validation rule:
 * <pre>
 * array('text','filter','filter'=>array($obj=new CHtmlPurifier(),'purify')),
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CHtmlPurifier extends COutputProcessor
{
	/**
	 * @var object the HTML Purifier instance.
	 */
	private $_purifier;
	/**
	 * @var mixed the options to be passed to HTML Purifier instance.
	 * This can be a HTMLPurifier_Config object,  an array of directives (Namespace.Directive => Value)
	 * or the filename of an ini file.
	 * @see http://htmlpurifier.org/live/configdoc/plain.html
	 */
	private $_options=null;
	
	/**
	 * Processes the captured output.
	 * This method purifies the output using {@link http://htmlpurifier.org HTML Purifier}.
	 * @param string $output the captured output to be processed
	 */
	public function processOutput($output)
	{
		$output=$this->purify($output);
		parent::processOutput($output);
	}
	
	/**
	 * Purifies the HTML content by removing malicious code.
	 * @param mixed $content the content to be purified.
	 * @return mixed the purified content 
	 */
	public function purify($content)
	{
		if(is_array($content))
			$content=array_map(array($this,'purify'),$content);
		else
			$content=$this->getPurifier()->purify($content);
		return $content;
	}
	
	/**
	 * Set the options for HTML Purifier and create a new HTML Purifier instance based on these options.
	 * @param mixed $options the options for HTML Purifier
	 * @return CHtmlPurifier
	 */
	public function setOptions($options)
	{
		$this->_options=$options;
		$this->createNewHtmlPurifierInstance();
		return $this;
	}
	
	/**
	 * Get the options for the HTML Purifier instance.
	 * @return mixed the HTML Purifier instance options
	 */
	public function getOptions()
	{
		return $this->_options;
	}
	
	/**
	 * Get the HTML Purifier instance or create a new one if it doesn't exist.
	 * @return HTMLPurifier
	 */
	protected function getPurifier()
	{
		if($this->_purifier!==null)
			return $this->_purifier;
		return $this->createNewHtmlPurifierInstance();
	}
	
	/**
	 * Create a new HTML Purifier instance.
	 * @return HTMLPurifier
	 */
	protected function createNewHtmlPurifierInstance()
	{
		$this->_purifier=new HTMLPurifier($this->getOptions());
		$this->_purifier->config->set('Cache.SerializerPath',Yii::app()->getRuntimePath());
		return $this->_purifier;
	}
}
