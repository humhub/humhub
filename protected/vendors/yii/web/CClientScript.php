<?php
/**
 * CClientScript class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CClientScript manages JavaScript and CSS stylesheets for views.
 *
 * @property string $coreScriptUrl The base URL of all core javascript files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class CClientScript extends CApplicationComponent
{
	/**
	 * The script is rendered in the head section right before the title element.
	 */
	const POS_HEAD=0;
	/**
	 * The script is rendered at the beginning of the body section.
	 */
	const POS_BEGIN=1;
	/**
	 * The script is rendered at the end of the body section.
	 */
	const POS_END=2;
	/**
	 * The script is rendered inside window onload function.
	 */
	const POS_LOAD=3;
	/**
	 * The body script is rendered inside a jQuery ready function.
	 */
	const POS_READY=4;

	/**
	 * @var boolean whether JavaScript should be enabled. Defaults to true.
	 */
	public $enableJavaScript=true;
	/**
	 * @var array the mapping between script file names and the corresponding script URLs.
	 * The array keys are script file names (without directory part) and the array values are the corresponding URLs.
	 * If an array value is false, the corresponding script file will not be rendered.
	 * If an array key is '*.js' or '*.css', the corresponding URL will replace all
	 * JavaScript files or CSS files, respectively.
	 *
	 * This property is mainly used to optimize the generated HTML pages
	 * by merging different scripts files into fewer and optimized script files.
	 */
	public $scriptMap=array();
	/**
	 * @var array list of custom script packages (name=>package spec).
	 * This property keeps a list of named script packages, each of which can contain
	 * a set of CSS and/or JavaScript script files, and their dependent package names.
	 * By calling {@link registerPackage}, one can register a whole package of client
	 * scripts together with their dependent packages and render them in the HTML output.
	 *
	 * The array structure is as follows:
	 * <pre>
	 * array(
	 *   'package-name'=>array(
	 *     'basePath'=>'alias of the directory containing the script files',
	 *     'baseUrl'=>'base URL for the script files',
	 *     'js'=>array(list of js files relative to basePath/baseUrl),
	 *     'css'=>array(list of css files relative to basePath/baseUrl),
	 *     'depends'=>array(list of dependent packages),
	 *   ),
	 *   ......
	 * )
	 * </pre>
	 *
	 * The JS and CSS files listed are relative to 'basePath'.
	 * For example, if 'basePath' is 'application.assets', a script named 'comments.js'
	 * will refer to the file 'protected/assets/comments.js'.
	 *
	 * When a script is being rendered in HTML, it will be prefixed with 'baseUrl'.
	 * For example, if 'baseUrl' is '/assets', the 'comments.js' script will be rendered
	 * using URL '/assets/comments.js'.
	 *
	 * If 'baseUrl' does not start with '/', the relative URL of the application entry
	 * script will be inserted at the beginning. For example, if 'baseUrl' is 'assets'
	 * and the current application runs with the URL 'http://localhost/demo/index.php',
	 * then the 'comments.js' script will be rendered using URL '/demo/assets/comments.js'.
	 *
	 * If 'baseUrl' is not set, the script will be published by {@link CAssetManager}
	 * and the corresponding published URL will be used.
	 *
	 * When calling {@link registerPackage} to register a script package,
	 * this property will be checked first followed by {@link corePackages}.
	 * If a package is found, it will be registered for rendering later on.
	 *
	 * @since 1.1.7
	 */
	public $packages=array();
	/**
	 * @var array list of core script packages (name=>package spec).
	 * Please refer to {@link packages} for details about package spec.
	 *
	 * By default, the core script packages are specified in 'framework/web/js/packages.php'.
	 * You may configure this property to customize the core script packages.
	 *
	 * When calling {@link registerPackage} to register a script package,
	 * {@link packages} will be checked first followed by this property.
	 * If a package is found, it will be registered for rendering later on.
	 *
	 * @since 1.1.7
	 */
	public $corePackages;
	/**
	 * @var array the registered JavaScript code blocks (position, key => code)
	 */
	public $scripts=array();
	/**
	 * @var array the registered CSS files (CSS URL=>media type).
	 */
	protected $cssFiles=array();
	/**
	 * @var array the registered JavaScript files (position, key => URL)
	 */
	protected $scriptFiles=array();
	/**
	 * @var array the registered head meta tags. Each array element represents an option array
	 * that will be passed as the last parameter of {@link CHtml::metaTag}.
	 * @since 1.1.3
	 */
	protected $metaTags=array();
	/**
	 * @var array the registered head link tags. Each array element represents an option array
	 * that will be passed as the last parameter of {@link CHtml::linkTag}.
	 * @since 1.1.3
	 */
	protected $linkTags=array();
	/**
	 * @var array the registered css code blocks (key => array(CSS code, media type)).
	 * @since 1.1.3
	 */
	protected $css=array();
	/**
	 * @var boolean whether there are any javascript or css to be rendered.
	 * @since 1.1.7
	 */
	protected $hasScripts=false;
	/**
	 * @var array the registered script packages (name => package spec)
	 * @since 1.1.7
	 */
	protected $coreScripts=array();
	/**
	 * @var integer Where the scripts registered using {@link registerCoreScript} or {@link registerPackage}
	 * will be inserted in the page. This can be one of the CClientScript::POS_* constants.
	 * Defaults to CClientScript::POS_HEAD.
	 * @since 1.1.3
	 */
	public $coreScriptPosition=self::POS_HEAD;
	/**
	 * @var integer Where the scripts registered using {@link registerScriptFile} will be inserted in the page.
	 * This can be one of the CClientScript::POS_* constants.
	 * Defaults to CClientScript::POS_HEAD.
	 * @since 1.1.11
	 */
	public $defaultScriptFilePosition=self::POS_HEAD;
	/**
	 * @var integer Where the scripts registered using {@link registerScript} will be inserted in the page.
	 * This can be one of the CClientScript::POS_* constants.
	 * Defaults to CClientScript::POS_READY.
	 * @since 1.1.11
	 */
	public $defaultScriptPosition=self::POS_READY;

	private $_baseUrl;

	/**
	 * Cleans all registered scripts.
	 */
	public function reset()
	{
		$this->hasScripts=false;
		$this->coreScripts=array();
		$this->cssFiles=array();
		$this->css=array();
		$this->scriptFiles=array();
		$this->scripts=array();
		$this->metaTags=array();
		$this->linkTags=array();

		$this->recordCachingAction('clientScript','reset',array());
	}

	/**
	 * Renders the registered scripts.
	 * This method is called in {@link CController::render} when it finishes
	 * rendering content. CClientScript thus gets a chance to insert script tags
	 * at <code>head</code> and <code>body</code> sections in the HTML output.
	 * @param string $output the existing output that needs to be inserted with script tags
	 */
	public function render(&$output)
	{
		if(!$this->hasScripts)
			return;

		$this->renderCoreScripts();

		if(!empty($this->scriptMap))
			$this->remapScripts();

		$this->unifyScripts();

		$this->renderHead($output);
		if($this->enableJavaScript)
		{
			$this->renderBodyBegin($output);
			$this->renderBodyEnd($output);
		}
	}

	/**
	 * Removes duplicated scripts from {@link scriptFiles}.
	 * @since 1.1.5
	 */
	protected function unifyScripts()
	{
		if(!$this->enableJavaScript)
			return;
		$map=array();
		if(isset($this->scriptFiles[self::POS_HEAD]))
			$map=$this->scriptFiles[self::POS_HEAD];

		if(isset($this->scriptFiles[self::POS_BEGIN]))
		{
			foreach($this->scriptFiles[self::POS_BEGIN] as $scriptFile=>$scriptFileValue)
			{
				if(isset($map[$scriptFile]))
					unset($this->scriptFiles[self::POS_BEGIN][$scriptFile]);
				else
					$map[$scriptFile]=true;
			}
		}

		if(isset($this->scriptFiles[self::POS_END]))
		{
			foreach($this->scriptFiles[self::POS_END] as $key=>$scriptFile)
			{
				if(isset($map[$key]))
					unset($this->scriptFiles[self::POS_END][$key]);
			}
		}
	}

	/**
	 * Uses {@link scriptMap} to re-map the registered scripts.
	 */
	protected function remapScripts()
	{
		$cssFiles=array();
		foreach($this->cssFiles as $url=>$media)
		{
			$name=basename($url);
			if(isset($this->scriptMap[$name]))
			{
				if($this->scriptMap[$name]!==false)
					$cssFiles[$this->scriptMap[$name]]=$media;
			}
			elseif(isset($this->scriptMap['*.css']))
			{
				if($this->scriptMap['*.css']!==false)
					$cssFiles[$this->scriptMap['*.css']]=$media;
			}
			else
				$cssFiles[$url]=$media;
		}
		$this->cssFiles=$cssFiles;

		$jsFiles=array();
		foreach($this->scriptFiles as $position=>$scriptFiles)
		{
			$jsFiles[$position]=array();
			foreach($scriptFiles as $scriptFile=>$scriptFileValue)
			{
				$name=basename($scriptFile);
				if(isset($this->scriptMap[$name]))
				{
					if($this->scriptMap[$name]!==false)
						$jsFiles[$position][$this->scriptMap[$name]]=$this->scriptMap[$name];
				}
				elseif(isset($this->scriptMap['*.js']))
				{
					if($this->scriptMap['*.js']!==false)
						$jsFiles[$position][$this->scriptMap['*.js']]=$this->scriptMap['*.js'];
				}
				else
					$jsFiles[$position][$scriptFile]=$scriptFileValue;
			}
		}
		$this->scriptFiles=$jsFiles;
	}

	/**
	 * Composes script HTML block from the given script values,
	 * attempting to group scripts at single 'script' tag if possible.
	 * @param array $scripts script values to process.
	 * @return string HTML output
	 */
	protected function renderScriptBatch(array $scripts)
	{
		$html = '';
		$scriptBatches = array();
		foreach($scripts as $scriptValue)
		{
			if(is_array($scriptValue))
			{
				$scriptContent = $scriptValue['content'];
				unset($scriptValue['content']);
				$scriptHtmlOptions = $scriptValue;
			}
			else
			{
				$scriptContent = $scriptValue;
				$scriptHtmlOptions = array();
			}
			$key=serialize(ksort($scriptHtmlOptions));
			$scriptBatches[$key]['htmlOptions']=$scriptHtmlOptions;
			$scriptBatches[$key]['scripts'][]=$scriptContent;
		}
		foreach($scriptBatches as $scriptBatch)
			if(!empty($scriptBatch['scripts']))
				$html.=CHtml::script(implode("\n",$scriptBatch['scripts']),$scriptBatch['htmlOptions'])."\n";
		return $html;
	}

	/**
	 * Renders the specified core javascript library.
	 */
	public function renderCoreScripts()
	{
		if($this->coreScripts===null)
			return;
		$cssFiles=array();
		$jsFiles=array();
		foreach($this->coreScripts as $name=>$package)
		{
			$baseUrl=$this->getPackageBaseUrl($name);
			if(!empty($package['js']))
			{
				foreach($package['js'] as $js)
					$jsFiles[$baseUrl.'/'.$js]=$baseUrl.'/'.$js;
			}
			if(!empty($package['css']))
			{
				foreach($package['css'] as $css)
					$cssFiles[$baseUrl.'/'.$css]='';
			}
		}
		// merge in place
		if($cssFiles!==array())
		{
			foreach($this->cssFiles as $cssFile=>$media)
				$cssFiles[$cssFile]=$media;
			$this->cssFiles=$cssFiles;
		}
		if($jsFiles!==array())
		{
			if(isset($this->scriptFiles[$this->coreScriptPosition]))
			{
				foreach($this->scriptFiles[$this->coreScriptPosition] as $url => $value)
					$jsFiles[$url]=$value;
			}
			$this->scriptFiles[$this->coreScriptPosition]=$jsFiles;
		}
	}

	/**
	 * Inserts the scripts in the head section.
	 * @param string $output the output to be inserted with scripts.
	 */
	public function renderHead(&$output)
	{
		$html='';
		foreach($this->metaTags as $meta)
			$html.=CHtml::metaTag($meta['content'],null,null,$meta)."\n";
		foreach($this->linkTags as $link)
			$html.=CHtml::linkTag(null,null,null,null,$link)."\n";
		foreach($this->cssFiles as $url=>$media)
			$html.=CHtml::cssFile($url,$media)."\n";
		foreach($this->css as $css)
			$html.=CHtml::css($css[0],$css[1])."\n";
		if($this->enableJavaScript)
		{
			if(isset($this->scriptFiles[self::POS_HEAD]))
			{
				foreach($this->scriptFiles[self::POS_HEAD] as $scriptFileValueUrl=>$scriptFileValue)
				{
					if(is_array($scriptFileValue))
						$html.=CHtml::scriptFile($scriptFileValueUrl,$scriptFileValue)."\n";
					else
						$html.=CHtml::scriptFile($scriptFileValueUrl)."\n";
				}
			}

			if(isset($this->scripts[self::POS_HEAD]))
				$html.=$this->renderScriptBatch($this->scripts[self::POS_HEAD]);
		}

		if($html!=='')
		{
			$count=0;
			$output=preg_replace('/(<title\b[^>]*>|<\\/head\s*>)/is','<###head###>$1',$output,1,$count);
			if($count)
				$output=str_replace('<###head###>',$html,$output);
			else
				$output=$html.$output;
		}
	}

	/**
	 * Inserts the scripts at the beginning of the body section.
	 * @param string $output the output to be inserted with scripts.
	 */
	public function renderBodyBegin(&$output)
	{
		$html='';
		if(isset($this->scriptFiles[self::POS_BEGIN]))
		{
			foreach($this->scriptFiles[self::POS_BEGIN] as $scriptFileUrl=>$scriptFileValue)
			{
				if(is_array($scriptFileValue))
					$html.=CHtml::scriptFile($scriptFileUrl,$scriptFileValue)."\n";
				else
					$html.=CHtml::scriptFile($scriptFileUrl)."\n";
			}
		}
		if(isset($this->scripts[self::POS_BEGIN]))
			$html.=$this->renderScriptBatch($this->scripts[self::POS_BEGIN]);

		if($html!=='')
		{
			$count=0;
			$output=preg_replace('/(<body\b[^>]*>)/is','$1<###begin###>',$output,1,$count);
			if($count)
				$output=str_replace('<###begin###>',$html,$output);
			else
				$output=$html.$output;
		}
	}

	/**
	 * Inserts the scripts at the end of the body section.
	 * @param string $output the output to be inserted with scripts.
	 */
	public function renderBodyEnd(&$output)
	{
		if(!isset($this->scriptFiles[self::POS_END]) && !isset($this->scripts[self::POS_END])
			&& !isset($this->scripts[self::POS_READY]) && !isset($this->scripts[self::POS_LOAD]))
			return;

		$fullPage=0;
		$output=preg_replace('/(<\\/body\s*>)/is','<###end###>$1',$output,1,$fullPage);
		$html='';
		if(isset($this->scriptFiles[self::POS_END]))
		{
			foreach($this->scriptFiles[self::POS_END] as $scriptFileUrl=>$scriptFileValue)
			{
				if(is_array($scriptFileValue))
					$html.=CHtml::scriptFile($scriptFileUrl,$scriptFileValue)."\n";
				else
					$html.=CHtml::scriptFile($scriptFileUrl)."\n";
			}
		}
		$scripts=isset($this->scripts[self::POS_END]) ? $this->scripts[self::POS_END] : array();
		if(isset($this->scripts[self::POS_READY]))
		{
			if($fullPage)
				$scripts[]="jQuery(function($) {\n".implode("\n",$this->scripts[self::POS_READY])."\n});";
			else
				$scripts[]=implode("\n",$this->scripts[self::POS_READY]);
		}
		if(isset($this->scripts[self::POS_LOAD]))
		{
			if($fullPage)
				$scripts[]="jQuery(window).on('load',function() {\n".implode("\n",$this->scripts[self::POS_LOAD])."\n});";
			else
				$scripts[]=implode("\n",$this->scripts[self::POS_LOAD]);
		}
		if(!empty($scripts))
			$html.=$this->renderScriptBatch($scripts);

		if($fullPage)
			$output=str_replace('<###end###>',$html,$output);
		else
			$output=$output.$html;
	}

	/**
	 * Returns the base URL of all core javascript files.
	 * If the base URL is not explicitly set, this method will publish the whole directory
	 * 'framework/web/js/source' and return the corresponding URL.
	 * @return string the base URL of all core javascript files
	 */
	public function getCoreScriptUrl()
	{
		if($this->_baseUrl!==null)
			return $this->_baseUrl;
		else
			return $this->_baseUrl=Yii::app()->getAssetManager()->publish(YII_PATH.'/web/js/source');
	}

	/**
	 * Sets the base URL of all core javascript files.
	 * This setter is provided in case when core javascript files are manually published
	 * to a pre-specified location. This may save asset publishing time for large-scale applications.
	 * @param string $value the base URL of all core javascript files.
	 */
	public function setCoreScriptUrl($value)
	{
		$this->_baseUrl=$value;
	}

	/**
	 * Returns the base URL for a registered package with the specified name.
	 * If needed, this method may publish the assets of the package and returns the published base URL.
	 * @param string $name the package name
	 * @return string the base URL for the named package. False is returned if the package is not registered yet.
	 * @see registerPackage
	 * @since 1.1.8
	 */
	public function getPackageBaseUrl($name)
	{
		if(!isset($this->coreScripts[$name]))
			return false;
		$package=$this->coreScripts[$name];
		if(isset($package['baseUrl']))
		{
			$baseUrl=$package['baseUrl'];
			if($baseUrl==='' || $baseUrl[0]!=='/' && strpos($baseUrl,'://')===false)
				$baseUrl=Yii::app()->getRequest()->getBaseUrl().'/'.$baseUrl;
			$baseUrl=rtrim($baseUrl,'/');
		}
		elseif(isset($package['basePath']))
			$baseUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias($package['basePath']));
		else
			$baseUrl=$this->getCoreScriptUrl();

		return $this->coreScripts[$name]['baseUrl']=$baseUrl;
	}

	/**
	 * Registers a script package that is listed in {@link packages}.
	 * This method is the same as {@link registerCoreScript}.
	 * @param string $name the name of the script package.
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 * @since 1.1.7
	 * @see renderCoreScript
	 */
	public function registerPackage($name)
	{
		return $this->registerCoreScript($name);
	}

	/**
	 * Registers a script package that is listed in {@link packages}.
	 * @param string $name the name of the script package.
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 * @see renderCoreScript
	 */
	public function registerCoreScript($name)
	{
		if(isset($this->coreScripts[$name]))
			return $this;
		if(isset($this->packages[$name]))
			$package=$this->packages[$name];
		else
		{
			if($this->corePackages===null)
				$this->corePackages=require(YII_PATH.'/web/js/packages.php');
			if(isset($this->corePackages[$name]))
				$package=$this->corePackages[$name];
		}
		if(isset($package))
		{
			if(!empty($package['depends']))
			{
				foreach($package['depends'] as $p)
					$this->registerCoreScript($p);
			}
			$this->coreScripts[$name]=$package;
			$this->hasScripts=true;
			$params=func_get_args();
			$this->recordCachingAction('clientScript','registerCoreScript',$params);
		}
		return $this;
	}

	/**
	 * Registers a CSS file
	 * @param string $url URL of the CSS file
	 * @param string $media media that the CSS file should be applied to. If empty, it means all media types.
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 */
	public function registerCssFile($url,$media='')
	{
		$this->hasScripts=true;
		$this->cssFiles[$url]=$media;
		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerCssFile',$params);
		return $this;
	}

	/**
	 * Registers a piece of CSS code.
	 * @param string $id ID that uniquely identifies this piece of CSS code
	 * @param string $css the CSS code
	 * @param string $media media that the CSS code should be applied to. If empty, it means all media types.
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 */
	public function registerCss($id,$css,$media='')
	{
		$this->hasScripts=true;
		$this->css[$id]=array($css,$media);
		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerCss',$params);
		return $this;
	}

	/**
	 * Registers a javascript file.
	 * @param string $url URL of the javascript file
	 * @param integer $position the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * </ul>
	 * @param array $htmlOptions additional HTML attributes
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 */
	public function registerScriptFile($url,$position=null,array $htmlOptions=array())
	{
		if($position===null)
			$position=$this->defaultScriptFilePosition;
		$this->hasScripts=true;
		if(empty($htmlOptions))
			$value=$url;
		else
		{
			$value=$htmlOptions;
			$value['src']=$url;
		}
		$this->scriptFiles[$position][$url]=$value;
		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerScriptFile',$params);
		return $this;
	}

	/**
	 * Registers a piece of javascript code.
	 * @param string $id ID that uniquely identifies this piece of JavaScript code
	 * @param string $script the javascript code
	 * @param integer $position the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	 * <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	 * </ul>
	 * @param array $htmlOptions additional HTML attributes
	 * Note: HTML attributes are not allowed for script positions "CClientScript::POS_LOAD" and "CClientScript::POS_READY".
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 */
	public function registerScript($id,$script,$position=null,array $htmlOptions=array())
	{
		if($position===null)
			$position=$this->defaultScriptPosition;
		$this->hasScripts=true;
		if(empty($htmlOptions))
			$scriptValue=$script;
		else
		{
			if($position==self::POS_LOAD || $position==self::POS_READY)
				throw new CException(Yii::t('yii','Script HTML options are not allowed for "CClientScript::POS_LOAD" and "CClientScript::POS_READY".'));
			$scriptValue=$htmlOptions;
			$scriptValue['content']=$script;
		}
		$this->scripts[$position][$id]=$scriptValue;
		if($position===self::POS_READY || $position===self::POS_LOAD)
			$this->registerCoreScript('jquery');
		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerScript',$params);
		return $this;
	}

	/**
	 * Registers a meta tag that will be inserted in the head section (right before the title element) of the resulting page.
	 *
	 * <b>Note:</b>
	 * Each call of this method will cause a rendering of new meta tag, even if their attributes are equal.
	 *
	 * <b>Example:</b>
	 * <pre>
	 *    $cs->registerMetaTag('example', 'description', null, array('lang' => 'en'));
	 *    $cs->registerMetaTag('beispiel', 'description', null, array('lang' => 'de'));
	 * </pre>
	 * @param string $content content attribute of the meta tag
	 * @param string $name name attribute of the meta tag. If null, the attribute will not be generated
	 * @param string $httpEquiv http-equiv attribute of the meta tag. If null, the attribute will not be generated
	 * @param array $options other options in name-value pairs (e.g. 'scheme', 'lang')
	 * @param string $id Optional id of the meta tag to avoid duplicates
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 */
	public function registerMetaTag($content,$name=null,$httpEquiv=null,$options=array(),$id=null)
	{
		$this->hasScripts=true;
		if($name!==null)
			$options['name']=$name;
		if($httpEquiv!==null)
			$options['http-equiv']=$httpEquiv;
		$options['content']=$content;
		$this->metaTags[null===$id?count($this->metaTags):$id]=$options;
		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerMetaTag',$params);
		return $this;
	}

	/**
	 * Registers a link tag that will be inserted in the head section (right before the title element) of the resulting page.
	 * @param string $relation rel attribute of the link tag. If null, the attribute will not be generated.
	 * @param string $type type attribute of the link tag. If null, the attribute will not be generated.
	 * @param string $href href attribute of the link tag. If null, the attribute will not be generated.
	 * @param string $media media attribute of the link tag. If null, the attribute will not be generated.
	 * @param array $options other options in name-value pairs
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 */
	public function registerLinkTag($relation=null,$type=null,$href=null,$media=null,$options=array())
	{
		$this->hasScripts=true;
		if($relation!==null)
			$options['rel']=$relation;
		if($type!==null)
			$options['type']=$type;
		if($href!==null)
			$options['href']=$href;
		if($media!==null)
			$options['media']=$media;
		$this->linkTags[serialize($options)]=$options;
		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerLinkTag',$params);
		return $this;
	}

	/**
	 * Checks whether the CSS file has been registered.
	 * @param string $url URL of the CSS file
	 * @return boolean whether the CSS file is already registered
	 */
	public function isCssFileRegistered($url)
	{
		return isset($this->cssFiles[$url]);
	}

	/**
	 * Checks whether the CSS code has been registered.
	 * @param string $id ID that uniquely identifies the CSS code
	 * @return boolean whether the CSS code is already registered
	 */
	public function isCssRegistered($id)
	{
		return isset($this->css[$id]);
	}

	/**
	 * Checks whether the JavaScript file has been registered.
	 * @param string $url URL of the javascript file
	 * @param integer $position the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * </ul>
	 * @return boolean whether the javascript file is already registered
	 */
	public function isScriptFileRegistered($url,$position=self::POS_HEAD)
	{
		return isset($this->scriptFiles[$position][$url]);
	}

	/**
	 * Checks whether the JavaScript code has been registered.
	 * @param string $id ID that uniquely identifies the JavaScript code
	 * @param integer $position the position of the JavaScript code. Valid values include the following:
	 * <ul>
	 * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
	 * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
	 * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
	 * <li>CClientScript::POS_LOAD : the script is inserted in the window.onload() function.</li>
	 * <li>CClientScript::POS_READY : the script is inserted in the jQuery's ready function.</li>
	 * </ul>
	 * @return boolean whether the javascript code is already registered
	 */
	public function isScriptRegistered($id,$position=self::POS_READY)
	{
		return isset($this->scripts[$position][$id]);
	}

	/**
	 * Records a method call when an output cache is in effect.
	 * This is a shortcut to Yii::app()->controller->recordCachingAction.
	 * In case when controller is absent, nothing is recorded.
	 * @param string $context a property name of the controller. It refers to an object
	 * whose method is being called. If empty it means the controller itself.
	 * @param string $method the method name
	 * @param array $params parameters passed to the method
	 * @see COutputCache
	 */
	protected function recordCachingAction($context,$method,$params)
	{
		if(($controller=Yii::app()->getController())!==null)
			$controller->recordCachingAction($context,$method,$params);
	}

	/**
	 * Adds a package to packages list.
	 *
	 * @param string $name the name of the script package.
	 * @param array $definition the definition array of the script package,
	 * @see CClientScript::packages.
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.10).
	 *
	 * @since 1.1.9
	 */
	public function addPackage($name,$definition)
	{
		$this->packages[$name]=$definition;
		return $this;
	}
}
