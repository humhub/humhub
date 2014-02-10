<?php
/**
 * CHtml class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CHtml is a static class that provides a collection of helper methods for creating HTML views.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.helpers
 * @since 1.0
 */
class CHtml
{
	const ID_PREFIX='yt';
	/**
	 * @var string the CSS class for displaying error summaries (see {@link errorSummary}).
	 */
	public static $errorSummaryCss='errorSummary';
	/**
	 * @var string the CSS class for displaying error messages (see {@link error}).
	 */
	public static $errorMessageCss='errorMessage';
	/**
	 * @var string the CSS class for highlighting error inputs. Form inputs will be appended
	 * with this CSS class if they have input errors.
	 */
	public static $errorCss='error';
	/**
	 * @var string the tag name for the error container tag. Defaults to 'div'.
	 * @since 1.1.13
	 */
	public static $errorContainerTag='div';
	/**
	 * @var string the CSS class for required labels. Defaults to 'required'.
	 * @see label
	 */
	public static $requiredCss='required';
	/**
	 * @var string the HTML code to be prepended to the required label.
	 * @see label
	 */
	public static $beforeRequiredLabel='';
	/**
	 * @var string the HTML code to be appended to the required label.
	 * @see label
	 */
	public static $afterRequiredLabel=' <span class="required">*</span>';
	/**
	 * @var integer the counter for generating automatic input field names.
	 */
	public static $count=0;
	/**
	 * Sets the default style for attaching jQuery event handlers.
	 *
	 * If set to true (default), event handlers are delegated.
	 * Event handlers are attached to the document body and can process events
	 * from descendant elements that are added to the document at a later time.
	 *
	 * If set to false, event handlers are directly bound.
	 * Event handlers are attached directly to the DOM element, that must already exist
	 * on the page. Elements injected into the page at a later time will not be processed.
	 *
	 * You can override this setting for a particular element by setting the htmlOptions delegate attribute
	 * (see {@link clientChange}).
	 *
	 * For more information about attaching jQuery event handler see {@link http://api.jquery.com/on/}
	 * @since 1.1.9
	 * @see clientChange
	 */
	public static $liveEvents=true;
	/**
	 * @var boolean whether to close single tags. Defaults to true. Can be set to false for HTML5.
	 * @since 1.1.13
	 */
	public static $closeSingleTags=true;
	/**
	 * @var boolean whether to render special attributes value. Defaults to true. Can be set to false for HTML5.
	 * @since 1.1.13
	 */
	public static $renderSpecialAttributesValue=true;

	/**
	 * Encodes special characters into HTML entities.
	 * The {@link CApplication::charset application charset} will be used for encoding.
	 * @param string $text data to be encoded
	 * @return string the encoded data
	 * @see http://www.php.net/manual/en/function.htmlspecialchars.php
	 */
	public static function encode($text)
	{
		return htmlspecialchars($text,ENT_QUOTES,Yii::app()->charset);
	}

	/**
	 * Decodes special HTML entities back to the corresponding characters.
	 * This is the opposite of {@link encode()}.
	 * @param string $text data to be decoded
	 * @return string the decoded data
	 * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
	 * @since 1.1.8
	 */
	public static function decode($text)
	{
		return htmlspecialchars_decode($text,ENT_QUOTES);
	}

	/**
	 * Encodes special characters in an array of strings into HTML entities.
	 * Both the array keys and values will be encoded if needed.
	 * If a value is an array, this method will also encode it recursively.
	 * The {@link CApplication::charset application charset} will be used for encoding.
	 * @param array $data data to be encoded
	 * @return array the encoded data
	 * @see http://www.php.net/manual/en/function.htmlspecialchars.php
	 */
	public static function encodeArray($data)
	{
		$d=array();
		foreach($data as $key=>$value)
		{
			if(is_string($key))
				$key=htmlspecialchars($key,ENT_QUOTES,Yii::app()->charset);
			if(is_string($value))
				$value=htmlspecialchars($value,ENT_QUOTES,Yii::app()->charset);
			elseif(is_array($value))
				$value=self::encodeArray($value);
			$d[$key]=$value;
		}
		return $d;
	}

	/**
	 * Generates an HTML element.
	 * @param string $tag the tag name
	 * @param array $htmlOptions the element attributes. The values will be HTML-encoded using {@link encode()}.
	 * If an 'encode' attribute is given and its value is false,
	 * the rest of the attribute values will NOT be HTML-encoded.
	 * Since version 1.1.5, attributes whose value is null will not be rendered.
	 * @param mixed $content the content to be enclosed between open and close element tags. It will not be HTML-encoded.
	 * If false, it means there is no body content.
	 * @param boolean $closeTag whether to generate the close tag.
	 * @return string the generated HTML element tag
	 */
	public static function tag($tag,$htmlOptions=array(),$content=false,$closeTag=true)
	{
		$html='<' . $tag . self::renderAttributes($htmlOptions);
		if($content===false)
			return $closeTag && self::$closeSingleTags ? $html.' />' : $html.'>';
		else
			return $closeTag ? $html.'>'.$content.'</'.$tag.'>' : $html.'>'.$content;
	}

	/**
	 * Generates an open HTML element.
	 * @param string $tag the tag name
	 * @param array $htmlOptions the element attributes. The values will be HTML-encoded using {@link encode()}.
	 * If an 'encode' attribute is given and its value is false,
	 * the rest of the attribute values will NOT be HTML-encoded.
	 * Since version 1.1.5, attributes whose value is null will not be rendered.
	 * @return string the generated HTML element tag
	 */
	public static function openTag($tag,$htmlOptions=array())
	{
		return '<' . $tag . self::renderAttributes($htmlOptions) . '>';
	}

	/**
	 * Generates a close HTML element.
	 * @param string $tag the tag name
	 * @return string the generated HTML element tag
	 */
	public static function closeTag($tag)
	{
		return '</'.$tag.'>';
	}

	/**
	 * Encloses the given string within a CDATA tag.
	 * @param string $text the string to be enclosed
	 * @return string the CDATA tag with the enclosed content.
	 */
	public static function cdata($text)
	{
		return '<![CDATA[' . $text . ']]>';
	}

	/**
	 * Generates a meta tag that can be inserted in the head section of HTML page.
	 * @param string $content content attribute of the meta tag
	 * @param string $name name attribute of the meta tag. If null, the attribute will not be generated
	 * @param string $httpEquiv http-equiv attribute of the meta tag. If null, the attribute will not be generated
	 * @param array $options other options in name-value pairs (e.g. 'scheme', 'lang')
	 * @return string the generated meta tag
	 */
	public static function metaTag($content,$name=null,$httpEquiv=null,$options=array())
	{
		if($name!==null)
			$options['name']=$name;
		if($httpEquiv!==null)
			$options['http-equiv']=$httpEquiv;
		$options['content']=$content;
		return self::tag('meta',$options);
	}

	/**
	 * Generates a link tag that can be inserted in the head section of HTML page.
	 * Do not confuse this method with {@link link()}. The latter generates a hyperlink.
	 * @param string $relation rel attribute of the link tag. If null, the attribute will not be generated.
	 * @param string $type type attribute of the link tag. If null, the attribute will not be generated.
	 * @param string $href href attribute of the link tag. If null, the attribute will not be generated.
	 * @param string $media media attribute of the link tag. If null, the attribute will not be generated.
	 * @param array $options other options in name-value pairs
	 * @return string the generated link tag
	 */
	public static function linkTag($relation=null,$type=null,$href=null,$media=null,$options=array())
	{
		if($relation!==null)
			$options['rel']=$relation;
		if($type!==null)
			$options['type']=$type;
		if($href!==null)
			$options['href']=$href;
		if($media!==null)
			$options['media']=$media;
		return self::tag('link',$options);
	}

	/**
	 * Encloses the given CSS content with a CSS tag.
	 * @param string $text the CSS content
	 * @param string $media the media that this CSS should apply to.
	 * @return string the CSS properly enclosed
	 */
	public static function css($text,$media='')
	{
		if($media!=='')
			$media=' media="'.$media.'"';
		return "<style type=\"text/css\"{$media}>\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</style>";
	}

	/**
	 * Registers a 'refresh' meta tag.
	 * This method can be invoked anywhere in a view. It will register a 'refresh'
	 * meta tag with {@link CClientScript} so that the page can be refreshed in
	 * the specified seconds.
	 * @param integer $seconds the number of seconds to wait before refreshing the page
	 * @param string $url the URL to which the page should be redirected to. If empty, it means the current page.
	 * @since 1.1.1
	 */
	public static function refresh($seconds, $url='')
	{
		$content="$seconds";
		if($url!=='')
			$content.=';'.self::normalizeUrl($url);
		Yii::app()->clientScript->registerMetaTag($content,null,'refresh');
	}

	/**
	 * Links to the specified CSS file.
	 * @param string $url the CSS URL
	 * @param string $media the media that this CSS should apply to.
	 * @return string the CSS link.
	 */
	public static function cssFile($url,$media='')
	{
		return CHtml::linkTag('stylesheet','text/css',$url,$media!=='' ? $media : null);
	}

	/**
	 * Encloses the given JavaScript within a script tag.
	 * @param string $text the JavaScript to be enclosed
	 * @return string the enclosed JavaScript
	 */
	public static function script($text)
	{
		return "<script type=\"text/javascript\">\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</script>";
	}

	/**
	 * Includes a JavaScript file.
	 * @param string $url URL for the JavaScript file
	 * @return string the JavaScript file tag
	 */
	public static function scriptFile($url)
	{
		return '<script type="text/javascript" src="'.self::encode($url).'"></script>';
	}

	/**
	 * Generates an opening form tag.
	 * This is a shortcut to {@link beginForm}.
	 * @param mixed $action the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * @param string $method form method (e.g. post, get)
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated form tag.
	 */
	public static function form($action='',$method='post',$htmlOptions=array())
	{
		return self::beginForm($action,$method,$htmlOptions);
	}

	/**
	 * Generates an opening form tag.
	 * Note, only the open tag is generated. A close tag should be placed manually
	 * at the end of the form.
	 * @param mixed $action the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * @param string $method form method (e.g. post, get)
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated form tag.
	 * @see endForm
	 */
	public static function beginForm($action='',$method='post',$htmlOptions=array())
	{
		$htmlOptions['action']=$url=self::normalizeUrl($action);
		$htmlOptions['method']=$method;
		$form=self::tag('form',$htmlOptions,false,false);
		$hiddens=array();
		if(!strcasecmp($method,'get') && ($pos=strpos($url,'?'))!==false)
		{
			foreach(explode('&',substr($url,$pos+1)) as $pair)
			{
				if(($pos=strpos($pair,'='))!==false)
					$hiddens[]=self::hiddenField(urldecode(substr($pair,0,$pos)),urldecode(substr($pair,$pos+1)),array('id'=>false));
				else
					$hiddens[]=self::hiddenField(urldecode($pair),'',array('id'=>false));
			}
		}
		$request=Yii::app()->request;
		if($request->enableCsrfValidation && !strcasecmp($method,'post'))
			$hiddens[]=self::hiddenField($request->csrfTokenName,$request->getCsrfToken(),array('id'=>false));
		if($hiddens!==array())
			$form.="\n".self::tag('div',array('style'=>'display:none'),implode("\n",$hiddens));
		return $form;
	}

	/**
	 * Generates a closing form tag.
	 * @return string the generated tag
	 * @see beginForm
	 */
	public static function endForm()
	{
		return '</form>';
	}

	/**
	 * Generates a stateful form tag.
	 * A stateful form tag is similar to {@link form} except that it renders an additional
	 * hidden field for storing persistent page states. You should use this method to generate
	 * a form tag if you want to access persistent page states when the form is submitted.
	 * @param mixed $action the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * @param string $method form method (e.g. post, get)
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated form tag.
	 */
	public static function statefulForm($action='',$method='post',$htmlOptions=array())
	{
		return self::form($action,$method,$htmlOptions)."\n".
			self::tag('div',array('style'=>'display:none'),self::pageStateField(''));
	}

	/**
	 * Generates a hidden field for storing persistent page states.
	 * This method is internally used by {@link statefulForm}.
	 * @param string $value the persistent page states in serialized format
	 * @return string the generated hidden field
	 */
	public static function pageStateField($value)
	{
		return '<input type="hidden" name="'.CController::STATE_INPUT_NAME.'" value="'.$value.'" />';
	}

	/**
	 * Generates a hyperlink tag.
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
	 * @param mixed $url a URL or an action route that can be used to create a URL.
	 * See {@link normalizeUrl} for more details about how to specify this parameter.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated hyperlink
	 * @see normalizeUrl
	 * @see clientChange
	 */
	public static function link($text,$url='#',$htmlOptions=array())
	{
		if($url!=='')
			$htmlOptions['href']=self::normalizeUrl($url);
		self::clientChange('click',$htmlOptions);
		return self::tag('a',$htmlOptions,$text);
	}

	/**
	 * Generates a mailto link.
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
	 * @param string $email email address. If this is empty, the first parameter (link body) will be treated as the email address.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated mailto link
	 * @see clientChange
	 */
	public static function mailto($text,$email='',$htmlOptions=array())
	{
		if($email==='')
			$email=$text;
		return self::link($text,'mailto:'.$email,$htmlOptions);
	}

	/**
	 * Generates an image tag.
	 * @param string $src the image URL
	 * @param string $alt the alternative text display
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated image tag
	 */
	public static function image($src,$alt='',$htmlOptions=array())
	{
		$htmlOptions['src']=$src;
		$htmlOptions['alt']=$alt;
		return self::tag('img',$htmlOptions);
	}

	/**
	 * Generates a button.
	 * @param string $label the button label
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function button($label='button',$htmlOptions=array())
	{
		if(!isset($htmlOptions['name']))
		{
			if(!array_key_exists('name',$htmlOptions))
				$htmlOptions['name']=self::ID_PREFIX.self::$count++;
		}
		if(!isset($htmlOptions['type']))
			$htmlOptions['type']='button';
		if(!isset($htmlOptions['value']))
			$htmlOptions['value']=$label;
		self::clientChange('click',$htmlOptions);
		return self::tag('input',$htmlOptions);
	}

	/**
	 * Generates a button using HTML button tag.
	 * This method is similar to {@link button} except that it generates a 'button'
	 * tag instead of 'input' tag.
	 * @param string $label the button label. Note that this value will be directly inserted in the button element
	 * without being HTML-encoded.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function htmlButton($label='button',$htmlOptions=array())
	{
		if(!isset($htmlOptions['name']))
			$htmlOptions['name']=self::ID_PREFIX.self::$count++;
		if(!isset($htmlOptions['type']))
			$htmlOptions['type']='button';
		self::clientChange('click',$htmlOptions);
		return self::tag('button',$htmlOptions,$label);
	}

	/**
	 * Generates a submit button.
	 * @param string $label the button label
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function submitButton($label='submit',$htmlOptions=array())
	{
		$htmlOptions['type']='submit';
		return self::button($label,$htmlOptions);
	}

	/**
	 * Generates a reset button.
	 * @param string $label the button label
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function resetButton($label='reset',$htmlOptions=array())
	{
		$htmlOptions['type']='reset';
		return self::button($label,$htmlOptions);
	}

	/**
	 * Generates an image submit button.
	 * @param string $src the image URL
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function imageButton($src,$htmlOptions=array())
	{
		$htmlOptions['src']=$src;
		$htmlOptions['type']='image';
		return self::button('submit',$htmlOptions);
	}

	/**
	 * Generates a link submit button.
	 * @param string $label the button label
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button tag
	 * @see clientChange
	 */
	public static function linkButton($label='submit',$htmlOptions=array())
	{
		if(!isset($htmlOptions['submit']))
			$htmlOptions['submit']=isset($htmlOptions['href']) ? $htmlOptions['href'] : '';
		return self::link($label,'#',$htmlOptions);
	}

	/**
	 * Generates a label tag.
	 * @param string $label label text. Note, you should HTML-encode the text if needed.
	 * @param string $for the ID of the HTML element that this label is associated with.
	 * If this is false, the 'for' attribute for the label tag will not be rendered.
	 * @param array $htmlOptions additional HTML attributes.
	 * The following HTML option is recognized:
	 * <ul>
	 * <li>required: if this is set and is true, the label will be styled
	 * with CSS class 'required' (customizable with CHtml::$requiredCss),
	 * and be decorated with {@link CHtml::beforeRequiredLabel} and
	 * {@link CHtml::afterRequiredLabel}.</li>
	 * </ul>
	 * @return string the generated label tag
	 */
	public static function label($label,$for,$htmlOptions=array())
	{
		if($for===false)
			unset($htmlOptions['for']);
		else
			$htmlOptions['for']=$for;
		if(isset($htmlOptions['required']))
		{
			if($htmlOptions['required'])
			{
				if(isset($htmlOptions['class']))
					$htmlOptions['class'].=' '.self::$requiredCss;
				else
					$htmlOptions['class']=self::$requiredCss;
				$label=self::$beforeRequiredLabel.$label.self::$afterRequiredLabel;
			}
			unset($htmlOptions['required']);
		}
		return self::tag('label',$htmlOptions,$label);
	}

	/**
	 * Generates a text field input.
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see inputField
	 */
	public static function textField($name,$value='',$htmlOptions=array())
	{
		self::clientChange('change',$htmlOptions);
		return self::inputField('text',$name,$value,$htmlOptions);
	}

	/**
	 * Generates a hidden input.
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated input field
	 * @see inputField
	 */
	public static function hiddenField($name,$value='',$htmlOptions=array())
	{
		return self::inputField('hidden',$name,$value,$htmlOptions);
	}

	/**
	 * Generates a password field input.
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see inputField
	 */
	public static function passwordField($name,$value='',$htmlOptions=array())
	{
		self::clientChange('change',$htmlOptions);
		return self::inputField('password',$name,$value,$htmlOptions);
	}

	/**
	 * Generates a file input.
	 * Note, you have to set the enclosing form's 'enctype' attribute to be 'multipart/form-data'.
	 * After the form is submitted, the uploaded file information can be obtained via $_FILES[$name] (see
	 * PHP documentation).
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated input field
	 * @see inputField
	 */
	public static function fileField($name,$value='',$htmlOptions=array())
	{
		return self::inputField('file',$name,$value,$htmlOptions);
	}

	/**
	 * Generates a text area input.
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated text area
	 * @see clientChange
	 * @see inputField
	 */
	public static function textArea($name,$value='',$htmlOptions=array())
	{
		$htmlOptions['name']=$name;
		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=self::getIdByName($name);
		elseif($htmlOptions['id']===false)
			unset($htmlOptions['id']);
		self::clientChange('change',$htmlOptions);
		return self::tag('textarea',$htmlOptions,isset($htmlOptions['encode']) && !$htmlOptions['encode'] ? $value : self::encode($value));
	}

	/**
	 * Generates a radio button.
	 * @param string $name the input name
	 * @param boolean $checked whether the radio button is checked
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * Since version 1.1.2, a special option named 'uncheckValue' is available that can be used to specify
	 * the value returned when the radio button is not checked. When set, a hidden field is rendered so that
	 * when the radio button is not checked, we can still obtain the posted uncheck value.
	 * If 'uncheckValue' is not set or set to NULL, the hidden field will not be rendered.
	 * @return string the generated radio button
	 * @see clientChange
	 * @see inputField
	 */
	public static function radioButton($name,$checked=false,$htmlOptions=array())
	{
		if($checked)
			$htmlOptions['checked']='checked';
		else
			unset($htmlOptions['checked']);
		$value=isset($htmlOptions['value']) ? $htmlOptions['value'] : 1;
		self::clientChange('click',$htmlOptions);

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck=null;

		if($uncheck!==null)
		{
			// add a hidden field so that if the radio button is not selected, it still submits a value
			if(isset($htmlOptions['id']) && $htmlOptions['id']!==false)
				$uncheckOptions=array('id'=>self::ID_PREFIX.$htmlOptions['id']);
			else
				$uncheckOptions=array('id'=>false);
			$hidden=self::hiddenField($name,$uncheck,$uncheckOptions);
		}
		else
			$hidden='';

		// add a hidden field so that if the radio button is not selected, it still submits a value
		return $hidden . self::inputField('radio',$name,$value,$htmlOptions);
	}

	/**
	 * Generates a check box.
	 * @param string $name the input name
	 * @param boolean $checked whether the check box is checked
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * Since version 1.1.2, a special option named 'uncheckValue' is available that can be used to specify
	 * the value returned when the checkbox is not checked. When set, a hidden field is rendered so that
	 * when the checkbox is not checked, we can still obtain the posted uncheck value.
	 * If 'uncheckValue' is not set or set to NULL, the hidden field will not be rendered.
	 * @return string the generated check box
	 * @see clientChange
	 * @see inputField
	 */
	public static function checkBox($name,$checked=false,$htmlOptions=array())
	{
		if($checked)
			$htmlOptions['checked']='checked';
		else
			unset($htmlOptions['checked']);
		$value=isset($htmlOptions['value']) ? $htmlOptions['value'] : 1;
		self::clientChange('click',$htmlOptions);

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck=null;

		if($uncheck!==null)
		{
			// add a hidden field so that if the check box is not checked, it still submits a value
			if(isset($htmlOptions['id']) && $htmlOptions['id']!==false)
				$uncheckOptions=array('id'=>self::ID_PREFIX.$htmlOptions['id']);
			else
				$uncheckOptions=array('id'=>false);
			$hidden=self::hiddenField($name,$uncheck,$uncheckOptions);
		}
		else
			$hidden='';

		// add a hidden field so that if the check box is not checked, it still submits a value
		return $hidden . self::inputField('checkbox',$name,$value,$htmlOptions);
	}

	/**
	 * Generates a drop down list.
	 * @param string $name the input name
	 * @param string $select the selected value
	 * @param array $data data for generating the list options (value=>display).
	 * You may use {@link listData} to generate this data.
	 * Please refer to {@link listOptions} on how this data is used to generate the list options.
	 * Note, the values and labels will be automatically HTML-encoded by this method.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are recognized. See {@link clientChange} and {@link tag} for more details.
	 * In addition, the following options are also supported specifically for dropdown list:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * </ul>
	 * Since 1.1.13, a special option named 'unselectValue' is available. It can be used to set the value
	 * that will be returned when no option is selected in multiple mode. When set, a hidden field is
	 * rendered so that if no option is selected in multiple mode, we can still obtain the posted
	 * unselect value. If 'unselectValue' is not set or set to NULL, the hidden field will not be rendered.
	 * @return string the generated drop down list
	 * @see clientChange
	 * @see inputField
	 * @see listData
	 */
	public static function dropDownList($name,$select,$data,$htmlOptions=array())
	{
		$htmlOptions['name']=$name;

		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=self::getIdByName($name);
		elseif($htmlOptions['id']===false)
			unset($htmlOptions['id']);

		self::clientChange('change',$htmlOptions);
		$options="\n".self::listOptions($select,$data,$htmlOptions);
		$hidden='';

		if(isset($htmlOptions['multiple']))
		{
			if(substr($htmlOptions['name'],-2)!=='[]')
				$htmlOptions['name'].='[]';

			if(isset($htmlOptions['unselectValue']))
			{
				$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
				$hidden=self::hiddenField(substr($htmlOptions['name'],0,-2),$htmlOptions['unselectValue'],$hiddenOptions);
				unset($htmlOptions['unselectValue']);
			}
		}
		// add a hidden field so that if the option is not selected, it still submits a value
		return $hidden . self::tag('select',$htmlOptions,$options);
	}

	/**
	 * Generates a list box.
	 * @param string $name the input name
	 * @param mixed $select the selected value(s). This can be either a string for single selection or an array for multiple selections.
	 * @param array $data data for generating the list options (value=>display)
	 * You may use {@link listData} to generate this data.
	 * Please refer to {@link listOptions} on how this data is used to generate the list options.
	 * Note, the values and labels will be automatically HTML-encoded by this method.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized. See {@link clientChange} and {@link tag} for more details.
	 * In addition, the following options are also supported specifically for list box:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * </ul>
	 * @return string the generated list box
	 * @see clientChange
	 * @see inputField
	 * @see listData
	 */
	public static function listBox($name,$select,$data,$htmlOptions=array())
	{
		if(!isset($htmlOptions['size']))
			$htmlOptions['size']=4;
		if(isset($htmlOptions['multiple']))
		{
			if(substr($name,-2)!=='[]')
				$name.='[]';
		}
		return self::dropDownList($name,$select,$data,$htmlOptions);
	}

	/**
	 * Generates a check box list.
	 * A check box list allows multiple selection, like {@link listBox}.
	 * As a result, the corresponding POST value is an array.
	 * @param string $name name of the check box list. You can use this name to retrieve
	 * the selected value(s) once the form is submitted.
	 * @param mixed $select selection of the check boxes. This can be either a string
	 * for single selection or an array for multiple selections.
	 * @param array $data value-label pairs used to generate the check box list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions addtional HTML options. The options will be applied to
	 * each checkbox input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each checkbox is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * check box input tag while "{label}" be replaced by the corresponding check box label.</li>
	 * <li>separator: string, specifies the string that separates the generated check boxes.</li>
	 * <li>checkAll: string, specifies the label for the "check all" checkbox.
	 * If this option is specified, a 'check all' checkbox will be displayed. Clicking on
	 * this checkbox will cause all checkboxes checked or unchecked.</li>
	 * <li>checkAllLast: boolean, specifies whether the 'check all' checkbox should be
	 * displayed at the end of the checkbox list. If this option is not set (default)
	 * or is false, the 'check all' checkbox will be displayed at the beginning of
	 * the checkbox list.</li>
	 * <li>labelOptions: array, specifies the additional HTML attributes to be rendered
	 * for every label tag in the list.</li>
	 * <li>container: string, specifies the checkboxes enclosing tag. Defaults to 'span'.
	 * If the value is an empty string, no enclosing tag will be generated</li>
	 * <li>baseID: string, specifies the base ID prefix to be used for checkboxes in the list.
	 * This option is available since version 1.1.13.</li>
	 * </ul>
	 * @return string the generated check box list
	 */
	public static function checkBoxList($name,$select,$data,$htmlOptions=array())
	{
		$template=isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
		$separator=isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
		$container=isset($htmlOptions['container'])?$htmlOptions['container']:'span';
		unset($htmlOptions['template'],$htmlOptions['separator'],$htmlOptions['container']);

		if(substr($name,-2)!=='[]')
			$name.='[]';

		if(isset($htmlOptions['checkAll']))
		{
			$checkAllLabel=$htmlOptions['checkAll'];
			$checkAllLast=isset($htmlOptions['checkAllLast']) && $htmlOptions['checkAllLast'];
		}
		unset($htmlOptions['checkAll'],$htmlOptions['checkAllLast']);

		$labelOptions=isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
		unset($htmlOptions['labelOptions']);

		$items=array();
		$baseID=isset($htmlOptions['baseID']) ? $htmlOptions['baseID'] : self::getIdByName($name);
		unset($htmlOptions['baseID']);
		$id=0;
		$checkAll=true;

		foreach($data as $value=>$label)
		{
			$checked=!is_array($select) && !strcmp($value,$select) || is_array($select) && in_array($value,$select);
			$checkAll=$checkAll && $checked;
			$htmlOptions['value']=$value;
			$htmlOptions['id']=$baseID.'_'.$id++;
			$option=self::checkBox($name,$checked,$htmlOptions);
			$label=self::label($label,$htmlOptions['id'],$labelOptions);
			$items[]=strtr($template,array('{input}'=>$option,'{label}'=>$label));
		}

		if(isset($checkAllLabel))
		{
			$htmlOptions['value']=1;
			$htmlOptions['id']=$id=$baseID.'_all';
			$option=self::checkBox($id,$checkAll,$htmlOptions);
			$label=self::label($checkAllLabel,$id,$labelOptions);
			$item=strtr($template,array('{input}'=>$option,'{label}'=>$label));
			if($checkAllLast)
				$items[]=$item;
			else
				array_unshift($items,$item);
			$name=strtr($name,array('['=>'\\[',']'=>'\\]'));
			$js=<<<EOD
jQuery('#$id').click(function() {
	jQuery("input[name='$name']").prop('checked', this.checked);
});
jQuery("input[name='$name']").click(function() {
	jQuery('#$id').prop('checked', !jQuery("input[name='$name']:not(:checked)").length);
});
jQuery('#$id').prop('checked', !jQuery("input[name='$name']:not(:checked)").length);
EOD;
			$cs=Yii::app()->getClientScript();
			$cs->registerCoreScript('jquery');
			$cs->registerScript($id,$js);
		}

		if(empty($container))
			return implode($separator,$items);
		else
			return self::tag($container,array('id'=>$baseID),implode($separator,$items));
	}

	/**
	 * Generates a radio button list.
	 * A radio button list is like a {@link checkBoxList check box list}, except that
	 * it only allows single selection.
	 * @param string $name name of the radio button list. You can use this name to retrieve
	 * the selected value(s) once the form is submitted.
	 * @param string $select selection of the radio buttons.
	 * @param array $data value-label pairs used to generate the radio button list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions addtional HTML options. The options will be applied to
	 * each radio button input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each radio button is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * radio button input tag while "{label}" will be replaced by the corresponding radio button label.</li>
	 * <li>separator: string, specifies the string that separates the generated radio buttons. Defaults to new line (<br/>).</li>
	 * <li>labelOptions: array, specifies the additional HTML attributes to be rendered
	 * for every label tag in the list.</li>
	 * <li>container: string, specifies the radio buttons enclosing tag. Defaults to 'span'.
	 * If the value is an empty string, no enclosing tag will be generated</li>
	 * <li>baseID: string, specifies the base ID prefix to be used for radio buttons in the list.
	 * This option is available since version 1.1.13.</li>
	 * </ul>
	 * @return string the generated radio button list
	 */
	public static function radioButtonList($name,$select,$data,$htmlOptions=array())
	{
		$template=isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
		$separator=isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
		$container=isset($htmlOptions['container'])?$htmlOptions['container']:'span';
		unset($htmlOptions['template'],$htmlOptions['separator'],$htmlOptions['container']);

		$labelOptions=isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
		unset($htmlOptions['labelOptions']);

		$items=array();
		$baseID=isset($htmlOptions['baseID']) ? $htmlOptions['baseID'] : self::getIdByName($name);
		unset($htmlOptions['baseID']);
		$id=0;
		foreach($data as $value=>$label)
		{
			$checked=!strcmp($value,$select);
			$htmlOptions['value']=$value;
			$htmlOptions['id']=$baseID.'_'.$id++;
			$option=self::radioButton($name,$checked,$htmlOptions);
			$label=self::label($label,$htmlOptions['id'],$labelOptions);
			$items[]=strtr($template,array('{input}'=>$option,'{label}'=>$label));
		}
		if(empty($container))
			return implode($separator,$items);
		else
			return self::tag($container,array('id'=>$baseID),implode($separator,$items));
	}

	/**
	 * Generates a link that can initiate AJAX requests.
	 * @param string $text the link body (it will NOT be HTML-encoded.)
	 * @param mixed $url the URL for the AJAX request. If empty, it is assumed to be the current URL. See {@link normalizeUrl} for more details.
	 * @param array $ajaxOptions AJAX options (see {@link ajax})
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated link
	 * @see normalizeUrl
	 * @see ajax
	 */
	public static function ajaxLink($text,$url,$ajaxOptions=array(),$htmlOptions=array())
	{
		if(!isset($htmlOptions['href']))
			$htmlOptions['href']='#';
		$ajaxOptions['url']=$url;
		$htmlOptions['ajax']=$ajaxOptions;
		self::clientChange('click',$htmlOptions);
		return self::tag('a',$htmlOptions,$text);
	}

	/**
	 * Generates a push button that can initiate AJAX requests.
	 * @param string $label the button label
	 * @param mixed $url the URL for the AJAX request. If empty, it is assumed to be the current URL. See {@link normalizeUrl} for more details.
	 * @param array $ajaxOptions AJAX options (see {@link ajax})
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button
	 */
	public static function ajaxButton($label,$url,$ajaxOptions=array(),$htmlOptions=array())
	{
		$ajaxOptions['url']=$url;
		$htmlOptions['ajax']=$ajaxOptions;
		return self::button($label,$htmlOptions);
	}

	/**
	 * Generates a push button that can submit the current form in POST method.
	 * @param string $label the button label
	 * @param mixed $url the URL for the AJAX request. If empty, it is assumed to be the current URL. See {@link normalizeUrl} for more details.
	 * @param array $ajaxOptions AJAX options (see {@link ajax})
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated button
	 */
	public static function ajaxSubmitButton($label,$url,$ajaxOptions=array(),$htmlOptions=array())
	{
		$ajaxOptions['type']='POST';
		$htmlOptions['type']='submit';
		return self::ajaxButton($label,$url,$ajaxOptions,$htmlOptions);
	}

	/**
	 * Generates the JavaScript that initiates an AJAX request.
	 * @param array $options AJAX options. The valid options are specified in the jQuery ajax documentation.
	 * The following special options are added for convenience:
	 * <ul>
	 * <li>update: string, specifies the selector whose HTML content should be replaced
	 *   by the AJAX request result.</li>
	 * <li>replace: string, specifies the selector whose target should be replaced
	 *   by the AJAX request result.</li>
	 * </ul>
	 * Note, if you specify the 'success' option, the above options will be ignored.
	 * @return string the generated JavaScript
	 * @see http://docs.jquery.com/Ajax/jQuery.ajax#options
	 */
	public static function ajax($options)
	{
		Yii::app()->getClientScript()->registerCoreScript('jquery');
		if(!isset($options['url']))
			$options['url']=new CJavaScriptExpression('location.href');
		else
			$options['url']=self::normalizeUrl($options['url']);
		if(!isset($options['cache']))
			$options['cache']=false;
		if(!isset($options['data']) && isset($options['type']))
			$options['data']=new CJavaScriptExpression('jQuery(this).parents("form").serialize()');
		foreach(array('beforeSend','complete','error','success') as $name)
		{
			if(isset($options[$name]) && !($options[$name] instanceof CJavaScriptExpression))
				$options[$name]=new CJavaScriptExpression($options[$name]);
		}
		if(isset($options['update']))
		{
			if(!isset($options['success']))
				$options['success']=new CJavaScriptExpression('function(html){jQuery("'.$options['update'].'").html(html)}');
			unset($options['update']);
		}
		if(isset($options['replace']))
		{
			if(!isset($options['success']))
				$options['success']=new CJavaScriptExpression('function(html){jQuery("'.$options['replace'].'").replaceWith(html)}');
			unset($options['replace']);
		}
		return 'jQuery.ajax('.CJavaScript::encode($options).');';
	}

	/**
	 * Generates the URL for the published assets.
	 * @param string $path the path of the asset to be published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hashed dirname of the path being published.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @return string the asset URL
	 */
	public static function asset($path,$hashByName=false)
	{
		return Yii::app()->getAssetManager()->publish($path,$hashByName);
	}

	/**
	 * Normalizes the input parameter to be a valid URL.
	 *
	 * If the input parameter is an empty string, the currently requested URL will be returned.
	 *
	 * If the input parameter is a non-empty string, it is treated as a valid URL and will
	 * be returned without any change.
	 *
	 * If the input parameter is an array, it is treated as a controller route and a list of
	 * GET parameters, and the {@link CController::createUrl} method will be invoked to
	 * create a URL. In this case, the first array element refers to the controller route,
	 * and the rest key-value pairs refer to the additional GET parameters for the URL.
	 * For example, <code>array('post/list', 'page'=>3)</code> may be used to generate the URL
	 * <code>/index.php?r=post/list&page=3</code>.
	 *
	 * @param mixed $url the parameter to be used to generate a valid URL
	 * @return string the normalized URL
	 */
	public static function normalizeUrl($url)
	{
		if(is_array($url))
		{
			if(isset($url[0]))
			{
				if(($c=Yii::app()->getController())!==null)
					$url=$c->createUrl($url[0],array_splice($url,1));
				else
					$url=Yii::app()->createUrl($url[0],array_splice($url,1));
			}
			else
				$url='';
		}
		return $url==='' ? Yii::app()->getRequest()->getUrl() : $url;
	}

	/**
	 * Generates an input HTML tag.
	 * This method generates an input HTML tag based on the given input name and value.
	 * @param string $type the input type (e.g. 'text', 'radio')
	 * @param string $name the input name
	 * @param string $value the input value
	 * @param array $htmlOptions additional HTML attributes for the HTML tag (see {@link tag}).
	 * @return string the generated input tag
	 */
	protected static function inputField($type,$name,$value,$htmlOptions)
	{
		$htmlOptions['type']=$type;
		$htmlOptions['value']=$value;
		$htmlOptions['name']=$name;
		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=self::getIdByName($name);
		elseif($htmlOptions['id']===false)
			unset($htmlOptions['id']);
		return self::tag('input',$htmlOptions);
	}

	/**
	 * Generates a label tag for a model attribute.
	 * The label text is the attribute label and the label is associated with
	 * the input for the attribute (see {@link CModel::getAttributeLabel}.
	 * If the attribute has input error, the label's CSS class will be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. The following special options are recognized:
	 * <ul>
	 * <li>required: if this is set and is true, the label will be styled
	 * with CSS class 'required' (customizable with CHtml::$requiredCss),
	 * and be decorated with {@link CHtml::beforeRequiredLabel} and
	 * {@link CHtml::afterRequiredLabel}.</li>
	 * <li>label: this specifies the label to be displayed. If this is not set,
	 * {@link CModel::getAttributeLabel} will be called to get the label for display.
	 * If the label is specified as false, no label will be rendered.</li>
	 * </ul>
	 * @return string the generated label tag
	 */
	public static function activeLabel($model,$attribute,$htmlOptions=array())
	{
		if(isset($htmlOptions['for']))
		{
			$for=$htmlOptions['for'];
			unset($htmlOptions['for']);
		}
		else
			$for=self::getIdByName(self::resolveName($model,$attribute));
		if(isset($htmlOptions['label']))
		{
			if(($label=$htmlOptions['label'])===false)
				return '';
			unset($htmlOptions['label']);
		}
		else
			$label=$model->getAttributeLabel($attribute);
		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);
		return self::label($label,$for,$htmlOptions);
	}

	/**
	 * Generates a label tag for a model attribute.
	 * This is an enhanced version of {@link activeLabel}. It will render additional
	 * CSS class and mark when the attribute is required.
	 * In particular, it calls {@link CModel::isAttributeRequired} to determine
	 * if the attribute is required.
	 * If so, it will add a CSS class {@link CHtml::requiredCss} to the label,
	 * and decorate the label with {@link CHtml::beforeRequiredLabel} and
	 * {@link CHtml::afterRequiredLabel}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated label tag
	 */
	public static function activeLabelEx($model,$attribute,$htmlOptions=array())
	{
		$realAttribute=$attribute;
		self::resolveName($model,$attribute); // strip off square brackets if any
		$htmlOptions['required']=$model->isAttributeRequired($attribute);
		return self::activeLabel($model,$realAttribute,$htmlOptions);
	}

	/**
	 * Generates a text field input for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see activeInputField
	 */
	public static function activeTextField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		self::clientChange('change',$htmlOptions);
		return self::activeInputField('text',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a url field input for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see activeInputField
	 * @since 1.1.11
	 */
	public static function activeUrlField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		self::clientChange('change',$htmlOptions);
		return self::activeInputField('url',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates an email field input for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see activeInputField
	 * @since 1.1.11
	 */
	public static function activeEmailField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		self::clientChange('change',$htmlOptions);
		return self::activeInputField('email',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a number field input for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see activeInputField
	 * @since 1.1.11
	 */
	public static function activeNumberField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		self::clientChange('change',$htmlOptions);
		return self::activeInputField('number',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a range field input for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see activeInputField
	 * @since 1.1.11
	 */
	public static function activeRangeField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		self::clientChange('change',$htmlOptions);
		return self::activeInputField('range',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a date field input for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see activeInputField
	 * @since 1.1.11
	 */
	public static function activeDateField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		self::clientChange('change',$htmlOptions);
		return self::activeInputField('date',$model,$attribute,$htmlOptions);
	}


	/**
	 * Generates a hidden input for a model attribute.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated input field
	 * @see activeInputField
	 */
	public static function activeHiddenField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		return self::activeInputField('hidden',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a password field input for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated input field
	 * @see clientChange
	 * @see activeInputField
	 */
	public static function activePasswordField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		self::clientChange('change',$htmlOptions);
		return self::activeInputField('password',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a text area input for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return string the generated text area
	 * @see clientChange
	 */
	public static function activeTextArea($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		self::clientChange('change',$htmlOptions);
		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);
		if(isset($htmlOptions['value']))
		{
			$text=$htmlOptions['value'];
			unset($htmlOptions['value']);
		}
		else
			$text=self::resolveValue($model,$attribute);
		return self::tag('textarea',$htmlOptions,isset($htmlOptions['encode']) && !$htmlOptions['encode'] ? $text : self::encode($text));
	}

	/**
	 * Generates a file input for a model attribute.
	 * Note, you have to set the enclosing form's 'enctype' attribute to be 'multipart/form-data'.
	 * After the form is submitted, the uploaded file information can be obtained via $_FILES (see
	 * PHP documentation).
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes (see {@link tag}).
	 * @return string the generated input field
	 * @see activeInputField
	 */
	public static function activeFileField($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		// add a hidden field so that if a model only has a file field, we can
		// still use isset($_POST[$modelClass]) to detect if the input is submitted
		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
		return self::hiddenField($htmlOptions['name'],'',$hiddenOptions)
			. self::activeInputField('file',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a radio button for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * A special option named 'uncheckValue' is available that can be used to specify
	 * the value returned when the radio button is not checked. By default, this value is '0'.
	 * Internally, a hidden field is rendered so that when the radio button is not checked,
	 * we can still obtain the posted uncheck value.
	 * If 'uncheckValue' is set as NULL, the hidden field will not be rendered.
	 * @return string the generated radio button
	 * @see clientChange
	 * @see activeInputField
	 */
	public static function activeRadioButton($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		if(!isset($htmlOptions['value']))
			$htmlOptions['value']=1;
		if(!isset($htmlOptions['checked']) && self::resolveValue($model,$attribute)==$htmlOptions['value'])
			$htmlOptions['checked']='checked';
		self::clientChange('click',$htmlOptions);

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck='0';

		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
		$hidden=$uncheck!==null ? self::hiddenField($htmlOptions['name'],$uncheck,$hiddenOptions) : '';

		// add a hidden field so that if the radio button is not selected, it still submits a value
		return $hidden . self::activeInputField('radio',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a check box for a model attribute.
	 * The attribute is assumed to take either true or false value.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * A special option named 'uncheckValue' is available that can be used to specify
	 * the value returned when the checkbox is not checked. By default, this value is '0'.
	 * Internally, a hidden field is rendered so that when the checkbox is not checked,
	 * we can still obtain the posted uncheck value.
	 * If 'uncheckValue' is set as NULL, the hidden field will not be rendered.
	 * @return string the generated check box
	 * @see clientChange
	 * @see activeInputField
	 */
	public static function activeCheckBox($model,$attribute,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		if(!isset($htmlOptions['value']))
			$htmlOptions['value']=1;
		if(!isset($htmlOptions['checked']) && self::resolveValue($model,$attribute)==$htmlOptions['value'])
			$htmlOptions['checked']='checked';
		self::clientChange('click',$htmlOptions);

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck='0';

		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
		$hidden=$uncheck!==null ? self::hiddenField($htmlOptions['name'],$uncheck,$hiddenOptions) : '';

		return $hidden . self::activeInputField('checkbox',$model,$attribute,$htmlOptions);
	}

	/**
	 * Generates a drop down list for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data data for generating the list options (value=>display)
	 * You may use {@link listData} to generate this data.
	 * Please refer to {@link listOptions} on how this data is used to generate the list options.
	 * Note, the values and labels will be automatically HTML-encoded by this method.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are recognized. See {@link clientChange} and {@link tag} for more details.
	 * In addition, the following options are also supported:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty.  Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * </ul>
	 * Since 1.1.13, a special option named 'unselectValue' is available. It can be used to set the value
	 * that will be returned when no option is selected in multiple mode. When set, a hidden field is
	 * rendered so that if no option is selected in multiple mode, we can still obtain the posted
	 * unselect value. If 'unselectValue' is not set or set to NULL, the hidden field will not be rendered.
	 * @return string the generated drop down list
	 * @see clientChange
	 * @see listData
	 */
	public static function activeDropDownList($model,$attribute,$data,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		$selection=self::resolveValue($model,$attribute);
		$options="\n".self::listOptions($selection,$data,$htmlOptions);
		self::clientChange('change',$htmlOptions);

		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);

		$hidden='';
		if(isset($htmlOptions['multiple']))
		{
			if(substr($htmlOptions['name'],-2)!=='[]')
				$htmlOptions['name'].='[]';

			if(isset($htmlOptions['unselectValue']))
			{
				$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
				$hidden=self::hiddenField(substr($htmlOptions['name'],0,-2),$htmlOptions['unselectValue'],$hiddenOptions);
				unset($htmlOptions['unselectValue']);
			}
		}
		return $hidden . self::tag('select',$htmlOptions,$options);
	}

	/**
	 * Generates a list box for a model attribute.
	 * The model attribute value is used as the selection.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data data for generating the list options (value=>display)
	 * You may use {@link listData} to generate this data.
	 * Please refer to {@link listOptions} on how this data is used to generate the list options.
	 * Note, the values and labels will be automatically HTML-encoded by this method.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are recognized. See {@link clientChange} and {@link tag} for more details.
	 * In addition, the following options are also supported:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * </ul>
	 * @return string the generated list box
	 * @see clientChange
	 * @see listData
	 */
	public static function activeListBox($model,$attribute,$data,$htmlOptions=array())
	{
		if(!isset($htmlOptions['size']))
			$htmlOptions['size']=4;
		return self::activeDropDownList($model,$attribute,$data,$htmlOptions);
	}

	/**
	 * Generates a check box list for a model attribute.
	 * The model attribute value is used as the selection.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * Note that a check box list allows multiple selection, like {@link listBox}.
	 * As a result, the corresponding POST value is an array. In case no selection
	 * is made, the corresponding POST value is an empty string.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the check box list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions addtional HTML options. The options will be applied to
	 * each checkbox input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each checkbox is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * check box input tag while "{label}" will be replaced by the corresponding check box label.</li>
	 * <li>separator: string, specifies the string that separates the generated check boxes.</li>
	 * <li>checkAll: string, specifies the label for the "check all" checkbox.
	 * If this option is specified, a 'check all' checkbox will be displayed. Clicking on
	 * this checkbox will cause all checkboxes checked or unchecked.</li>
	 * <li>checkAllLast: boolean, specifies whether the 'check all' checkbox should be
	 * displayed at the end of the checkbox list. If this option is not set (default)
	 * or is false, the 'check all' checkbox will be displayed at the beginning of
	 * the checkbox list.</li>
	 * <li>encode: boolean, specifies whether to encode HTML-encode tag attributes and values. Defaults to true.</li>
	 * </ul>
	 * Since 1.1.7, a special option named 'uncheckValue' is available. It can be used to set the value
	 * that will be returned when the checkbox is not checked. By default, this value is ''.
	 * Internally, a hidden field is rendered so when the checkbox is not checked, we can still
	 * obtain the value. If 'uncheckValue' is set to NULL, there will be no hidden field rendered.
	 * @return string the generated check box list
	 * @see checkBoxList
	 */
	public static function activeCheckBoxList($model,$attribute,$data,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		$selection=self::resolveValue($model,$attribute);
		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);
		$name=$htmlOptions['name'];
		unset($htmlOptions['name']);

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck='';

		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
		$hidden=$uncheck!==null ? self::hiddenField($name,$uncheck,$hiddenOptions) : '';

		return $hidden . self::checkBoxList($name,$selection,$data,$htmlOptions);
	}

	/**
	 * Generates a radio button list for a model attribute.
	 * The model attribute value is used as the selection.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the radio button list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions addtional HTML options. The options will be applied to
	 * each radio button input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each radio button is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * radio button input tag while "{label}" will be replaced by the corresponding radio button label.</li>
	 * <li>separator: string, specifies the string that separates the generated radio buttons. Defaults to new line (<br/>).</li>
	 * <li>encode: boolean, specifies whether to encode HTML-encode tag attributes and values. Defaults to true.</li>
	 * </ul>
	 * Since version 1.1.7, a special option named 'uncheckValue' is available that can be used to specify the value
	 * returned when the radio button is not checked. By default, this value is ''. Internally, a hidden field is
	 * rendered so that when the radio button is not checked, we can still obtain the posted uncheck value.
	 * If 'uncheckValue' is set as NULL, the hidden field will not be rendered.
	 * @return string the generated radio button list
	 * @see radioButtonList
	 */
	public static function activeRadioButtonList($model,$attribute,$data,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		$selection=self::resolveValue($model,$attribute);
		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);
		$name=$htmlOptions['name'];
		unset($htmlOptions['name']);

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck='';

		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
		$hidden=$uncheck!==null ? self::hiddenField($name,$uncheck,$hiddenOptions) : '';

		return $hidden . self::radioButtonList($name,$selection,$data,$htmlOptions);
	}

	/**
	 * Displays a summary of validation errors for one or several models.
	 * @param mixed $model the models whose input errors are to be displayed. This can be either
	 * a single model or an array of models.
	 * @param string $header a piece of HTML code that appears in front of the errors
	 * @param string $footer a piece of HTML code that appears at the end of the errors
	 * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
	 * A special option named 'firstError' is recognized, which when set true, will
	 * make the error summary to show only the first error message of each attribute.
	 * If this is not set or is false, all error messages will be displayed.
	 * This option has been available since version 1.1.3.
	 * @return string the error summary. Empty if no errors are found.
	 * @see CModel::getErrors
	 * @see errorSummaryCss
	 */
	public static function errorSummary($model,$header=null,$footer=null,$htmlOptions=array())
	{
		$content='';
		if(!is_array($model))
			$model=array($model);
		if(isset($htmlOptions['firstError']))
		{
			$firstError=$htmlOptions['firstError'];
			unset($htmlOptions['firstError']);
		}
		else
			$firstError=false;
		foreach($model as $m)
		{
			foreach($m->getErrors() as $errors)
			{
				foreach($errors as $error)
				{
					if($error!='')
						$content.="<li>$error</li>\n";
					if($firstError)
						break;
				}
			}
		}
		if($content!=='')
		{
			if($header===null)
				$header='<p>'.Yii::t('yii','Please fix the following input errors:').'</p>';
			if(!isset($htmlOptions['class']))
				$htmlOptions['class']=self::$errorSummaryCss;
			return self::tag('div',$htmlOptions,$header."\n<ul>\n$content</ul>".$footer);
		}
		else
			return '';
	}

	/**
	 * Displays the first validation error for a model attribute.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute name
	 * @param array $htmlOptions additional HTML attributes to be rendered in the container tag.
	 * @return string the error display. Empty if no errors are found.
	 * @see CModel::getErrors
	 * @see errorMessageCss
	 * @see $errorContainerTag
	 */
	public static function error($model,$attribute,$htmlOptions=array())
	{
		self::resolveName($model,$attribute); // turn [a][b]attr into attr
		$error=$model->getError($attribute);
		if($error!='')
		{
			if(!isset($htmlOptions['class']))
				$htmlOptions['class']=self::$errorMessageCss;
			return self::tag(self::$errorContainerTag,$htmlOptions,$error);
		}
		else
			return '';
	}

	/**
	 * Generates the data suitable for list-based HTML elements.
	 * The generated data can be used in {@link dropDownList}, {@link listBox}, {@link checkBoxList},
	 * {@link radioButtonList}, and their active-versions (such as {@link activeDropDownList}).
	 * Note, this method does not HTML-encode the generated data. You may call {@link encodeArray} to
	 * encode it if needed.
	 * Please refer to the {@link value} method on how to specify value field, text field and group field.
	 * You can also pass anonymous functions as second, third and fourth arguments which calculates
	 * text field value (PHP 5.3+ only) since 1.1.13. Your anonymous function should receive one argument,
	 * which is the model, the current &lt;option&gt; tag is generated from.
	 *
	 * <pre>
	 * CHtml::listData($posts,'id',function($post) {
	 * 	return CHtml::encode($post->title);
	 * });
	 * </pre>
	 *
	 * @param array $models a list of model objects. This parameter
	 * can also be an array of associative arrays (e.g. results of {@link CDbCommand::queryAll}).
	 * @param mixed $valueField the attribute name or anonymous function (PHP 5.3+) for list option values
	 * @param mixed $textField the attribute name or anonymous function (PHP 5.3+) for list option texts
	 * @param mixed $groupField the attribute name or anonymous function (PHP 5.3+) for list option group names. If empty, no group will be generated.
	 * @return array the list data that can be used in {@link dropDownList}, {@link listBox}, etc.
	 */
	public static function listData($models,$valueField,$textField,$groupField='')
	{
		$listData=array();
		if($groupField==='')
		{
			foreach($models as $model)
			{
				$value=self::value($model,$valueField);
				$text=self::value($model,$textField);
				$listData[$value]=$text;
			}
		}
		else
		{
			foreach($models as $model)
			{
				$group=self::value($model,$groupField);
				$value=self::value($model,$valueField);
				$text=self::value($model,$textField);
				if($group===null)
					$listData[$value]=$text;
				else
					$listData[$group][$value]=$text;
			}
		}
		return $listData;
	}

	/**
	 * Evaluates the value of the specified attribute for the given model.
	 * The attribute name can be given in a dot syntax. For example, if the attribute
	 * is "author.firstName", this method will return the value of "$model->author->firstName".
	 * A default value (passed as the last parameter) will be returned if the attribute does
	 * not exist or is broken in the middle (e.g. $model->author is null).
	 * The model can be either an object or an array. If the latter, the attribute is treated
	 * as a key of the array. For the example of "author.firstName", if would mean the array value
	 * "$model['author']['firstName']".
	 *
	 * Anonymous function could also be used for attribute calculation since 1.1.13
	 * ($attribute parameter; PHP 5.3+ only) as follows:
	 * <pre>
	 * $taskClosedSecondsAgo=CHtml::value($closedTask,function($model) {
	 * 	return time()-$model->closed_at;
	 * });
	 * </pre>
	 * Your anonymous function should receive one argument, which is the model, the current
	 * value is calculated from. This feature could be used together with the {@link listData}.
	 * Please refer to its documentation for more details.
	 *
	 * @param mixed $model the model. This can be either an object or an array.
	 * @param mixed $attribute the attribute name (use dot to concatenate multiple attributes)
	 * or anonymous function (PHP 5.3+). Remember that functions created by "create_function"
	 * are not supported by this method. Also note that numeric value is meaningless when
	 * first parameter is object typed.
	 * @param mixed $defaultValue the default value to return when the attribute does not exist.
	 * @return mixed the attribute value.
	 */
	public static function value($model,$attribute,$defaultValue=null)
	{
		if(is_scalar($attribute) || $attribute===null)
			foreach(explode('.',$attribute) as $name)
			{
				if(is_object($model) && isset($model->$name))
					$model=$model->$name;
				elseif(is_array($model) && isset($model[$name]))
					$model=$model[$name];
				else
					return $defaultValue;
			}
		else
			return call_user_func($attribute,$model);

		return $model;
	}

	/**
	 * Generates a valid HTML ID based on name.
	 * @param string $name name from which to generate HTML ID
	 * @return string the ID generated based on name.
	 */
	public static function getIdByName($name)
	{
		return str_replace(array('[]', '][', '[', ']', ' '), array('', '_', '_', '', '_'), $name);
	}

	/**
	 * Generates input field ID for a model attribute.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @return string the generated input field ID
	 */
	public static function activeId($model,$attribute)
	{
		return self::getIdByName(self::activeName($model,$attribute));
	}

	/**
	 * Generates input field name for a model attribute.
	 * Unlike {@link resolveName}, this method does NOT modify the attribute name.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @return string the generated input field name
	 */
	public static function activeName($model,$attribute)
	{
		$a=$attribute; // because the attribute name may be changed by resolveName
		return self::resolveName($model,$a);
	}

	/**
	 * Generates an input HTML tag for a model attribute.
	 * This method generates an input HTML tag based on the given data model and attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * This enables highlighting the incorrect input.
	 * @param string $type the input type (e.g. 'text', 'radio')
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes for the HTML tag
	 * @return string the generated input tag
	 */
	protected static function activeInputField($type,$model,$attribute,$htmlOptions)
	{
		$htmlOptions['type']=$type;
		if($type==='text' || $type==='password')
		{
			if(!isset($htmlOptions['maxlength']))
			{
				foreach($model->getValidators($attribute) as $validator)
				{
					if($validator instanceof CStringValidator && $validator->max!==null)
					{
						$htmlOptions['maxlength']=$validator->max;
						break;
					}
				}
			}
			elseif($htmlOptions['maxlength']===false)
				unset($htmlOptions['maxlength']);
		}

		if($type==='file')
			unset($htmlOptions['value']);
		elseif(!isset($htmlOptions['value']))
			$htmlOptions['value']=self::resolveValue($model,$attribute);
		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);
		return self::tag('input',$htmlOptions);
	}

	/**
	 * Generates the list options.
	 * @param mixed $selection the selected value(s). This can be either a string for single selection or an array for multiple selections.
	 * @param array $listData the option data (see {@link listData})
	 * @param array $htmlOptions additional HTML attributes. The following two special attributes are recognized:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * <li>key: string, specifies the name of key attribute of the selection object(s).
	 * This is used when the selection is represented in terms of objects. In this case,
	 * the property named by the key option of the objects will be treated as the actual selection value.
	 * This option defaults to 'primaryKey', meaning using the 'primaryKey' property value of the objects in the selection.
	 * This option has been available since version 1.1.3.</li>
	 * </ul>
	 * @return string the generated list options
	 */
	public static function listOptions($selection,$listData,&$htmlOptions)
	{
		$raw=isset($htmlOptions['encode']) && !$htmlOptions['encode'];
		$content='';
		if(isset($htmlOptions['prompt']))
		{
			$content.='<option value="">'.strtr($htmlOptions['prompt'],array('<'=>'&lt;', '>'=>'&gt;'))."</option>\n";
			unset($htmlOptions['prompt']);
		}
		if(isset($htmlOptions['empty']))
		{
			if(!is_array($htmlOptions['empty']))
				$htmlOptions['empty']=array(''=>$htmlOptions['empty']);
			foreach($htmlOptions['empty'] as $value=>$label)
				$content.='<option value="'.self::encode($value).'">'.strtr($label,array('<'=>'&lt;', '>'=>'&gt;'))."</option>\n";
			unset($htmlOptions['empty']);
		}

		if(isset($htmlOptions['options']))
		{
			$options=$htmlOptions['options'];
			unset($htmlOptions['options']);
		}
		else
			$options=array();

		$key=isset($htmlOptions['key']) ? $htmlOptions['key'] : 'primaryKey';
		if(is_array($selection))
		{
			foreach($selection as $i=>$item)
			{
				if(is_object($item))
					$selection[$i]=$item->$key;
			}
		}
		elseif(is_object($selection))
			$selection=$selection->$key;

		foreach($listData as $key=>$value)
		{
			if(is_array($value))
			{
				$content.='<optgroup label="'.($raw?$key : self::encode($key))."\">\n";
				$dummy=array('options'=>$options);
				if(isset($htmlOptions['encode']))
					$dummy['encode']=$htmlOptions['encode'];
				$content.=self::listOptions($selection,$value,$dummy);
				$content.='</optgroup>'."\n";
			}
			else
			{
				$attributes=array('value'=>(string)$key, 'encode'=>!$raw);
				if(!is_array($selection) && !strcmp($key,$selection) || is_array($selection) && in_array($key,$selection))
					$attributes['selected']='selected';
				if(isset($options[$key]))
					$attributes=array_merge($attributes,$options[$key]);
				$content.=self::tag('option',$attributes,$raw?(string)$value : self::encode((string)$value))."\n";
			}
		}

		unset($htmlOptions['key']);

		return $content;
	}

	/**
	 * Generates the JavaScript with the specified client changes.
	 * @param string $event event name (without 'on')
	 * @param array $htmlOptions HTML attributes which may contain the following special attributes
	 * specifying the client change behaviors:
	 * <ul>
	 * <li>submit: string, specifies the URL to submit to. If the current element has a parent form, that form will be
	 * submitted, and if 'submit' is non-empty its value will replace the form's URL. If there is no parent form the
	 * data listed in 'params' will be submitted instead (via POST method), to the URL in 'submit' or the currently
	 * requested URL if 'submit' is empty. Please note that if the 'csrf' setting is true, the CSRF token will be
	 * included in the params too.</li>
	 * <li>params: array, name-value pairs that should be submitted together with the form. This is only used when 'submit' option is specified.</li>
	 * <li>csrf: boolean, whether a CSRF token should be automatically included in 'params' when {@link CHttpRequest::enableCsrfValidation} is true. Defaults to false.
	 * You may want to set this to be true if there is no enclosing form around this element.
	 * This option is meaningful only when 'submit' option is set.</li>
	 * <li>return: boolean, the return value of the javascript. Defaults to false, meaning that the execution of
	 * javascript would not cause the default behavior of the event.</li>
	 * <li>confirm: string, specifies the message that should show in a pop-up confirmation dialog.</li>
	 * <li>ajax: array, specifies the AJAX options (see {@link ajax}).</li>
	 * <li>live: boolean, whether the event handler should be delegated or directly bound.
	 * If not set, {@link liveEvents} will be used. This option has been available since version 1.1.11.</li>
	 * </ul>
	 * This parameter has been available since version 1.1.1.
	 */
	protected static function clientChange($event,&$htmlOptions)
	{
		if(!isset($htmlOptions['submit']) && !isset($htmlOptions['confirm']) && !isset($htmlOptions['ajax']))
			return;

		if(isset($htmlOptions['live']))
		{
			$live=$htmlOptions['live'];
			unset($htmlOptions['live']);
		}
		else
			$live = self::$liveEvents;

		if(isset($htmlOptions['return']) && $htmlOptions['return'])
			$return='return true';
		else
			$return='return false';

		if(isset($htmlOptions['on'.$event]))
		{
			$handler=trim($htmlOptions['on'.$event],';').';';
			unset($htmlOptions['on'.$event]);
		}
		else
			$handler='';

		if(isset($htmlOptions['id']))
			$id=$htmlOptions['id'];
		else
			$id=$htmlOptions['id']=isset($htmlOptions['name'])?$htmlOptions['name']:self::ID_PREFIX.self::$count++;

		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('jquery');

		if(isset($htmlOptions['submit']))
		{
			$cs->registerCoreScript('yii');
			$request=Yii::app()->getRequest();
			if($request->enableCsrfValidation && isset($htmlOptions['csrf']) && $htmlOptions['csrf'])
				$htmlOptions['params'][$request->csrfTokenName]=$request->getCsrfToken();
			if(isset($htmlOptions['params']))
				$params=CJavaScript::encode($htmlOptions['params']);
			else
				$params='{}';
			if($htmlOptions['submit']!=='')
				$url=CJavaScript::quote(self::normalizeUrl($htmlOptions['submit']));
			else
				$url='';
			$handler.="jQuery.yii.submitForm(this,'$url',$params);{$return};";
		}

		if(isset($htmlOptions['ajax']))
			$handler.=self::ajax($htmlOptions['ajax'])."{$return};";

		if(isset($htmlOptions['confirm']))
		{
			$confirm='confirm(\''.CJavaScript::quote($htmlOptions['confirm']).'\')';
			if($handler!=='')
				$handler="if($confirm) {".$handler."} else return false;";
			else
				$handler="return $confirm;";
		}

		if($live)
			$cs->registerScript('Yii.CHtml.#' . $id, "jQuery('body').on('$event','#$id',function(){{$handler}});");
		else
			$cs->registerScript('Yii.CHtml.#' . $id, "jQuery('#$id').on('$event', function(){{$handler}});");
		unset($htmlOptions['params'],$htmlOptions['submit'],$htmlOptions['ajax'],$htmlOptions['confirm'],$htmlOptions['return'],$htmlOptions['csrf']);
	}

	/**
	 * Generates input name and ID for a model attribute.
	 * This method will update the HTML options by setting appropriate 'name' and 'id' attributes.
	 * This method may also modify the attribute name if the name
	 * contains square brackets (mainly used in tabular input).
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions the HTML options
	 */
	public static function resolveNameID($model,&$attribute,&$htmlOptions)
	{
		if(!isset($htmlOptions['name']))
			$htmlOptions['name']=self::resolveName($model,$attribute);
		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=self::getIdByName($htmlOptions['name']);
		elseif($htmlOptions['id']===false)
			unset($htmlOptions['id']);
	}

	/**
	 * Generates input name for a model attribute.
	 * Note, the attribute name may be modified after calling this method if the name
	 * contains square brackets (mainly used in tabular input) before the real attribute name.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @return string the input name
	 */
	public static function resolveName($model,&$attribute)
	{
		if(($pos=strpos($attribute,'['))!==false)
		{
			if($pos!==0)  // e.g. name[a][b]
				return get_class($model).'['.substr($attribute,0,$pos).']'.substr($attribute,$pos);
			if(($pos=strrpos($attribute,']'))!==false && $pos!==strlen($attribute)-1)  // e.g. [a][b]name
			{
				$sub=substr($attribute,0,$pos+1);
				$attribute=substr($attribute,$pos+1);
				return get_class($model).$sub.'['.$attribute.']';
			}
			if(preg_match('/\](\w+\[.*)$/',$attribute,$matches))
			{
				$name=get_class($model).'['.str_replace(']','][',trim(strtr($attribute,array(']['=>']','['=>']')),']')).']';
				$attribute=$matches[1];
				return $name;
			}
		}
		return get_class($model).'['.$attribute.']';
	}

	/**
	 * Evaluates the attribute value of the model.
	 * This method can recognize the attribute name written in array format.
	 * For example, if the attribute name is 'name[a][b]', the value "$model->name['a']['b']" will be returned.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute name
	 * @return mixed the attribute value
	 * @since 1.1.3
	 */
	public static function resolveValue($model,$attribute)
	{
		if(($pos=strpos($attribute,'['))!==false)
		{
			if($pos===0) // [a]name[b][c], should ignore [a]
			{
				if(preg_match('/\](\w+(\[.+)?)/',$attribute,$matches))
					$attribute=$matches[1]; // we get: name[b][c]
				if(($pos=strpos($attribute,'['))===false)
					return $model->$attribute;
			}
			$name=substr($attribute,0,$pos);
			$value=$model->$name;
			foreach(explode('][',rtrim(substr($attribute,$pos+1),']')) as $id)
			{
				if((is_array($value) || $value instanceof ArrayAccess) && isset($value[$id]))
					$value=$value[$id];
				else
					return null;
			}
			return $value;
		}
		else
			return $model->$attribute;
	}

	/**
	 * Appends {@link errorCss} to the 'class' attribute.
	 * @param array $htmlOptions HTML options to be modified
	 */
	protected static function addErrorCss(&$htmlOptions)
	{
		if(empty(self::$errorCss))
			return;

		if(isset($htmlOptions['class']))
			$htmlOptions['class'].=' '.self::$errorCss;
		else
			$htmlOptions['class']=self::$errorCss;
	}

	/**
	 * Renders the HTML tag attributes.
	 * Since version 1.1.5, attributes whose value is null will not be rendered.
	 * Special attributes, such as 'checked', 'disabled', 'readonly', will be rendered
	 * properly based on their corresponding boolean value.
	 * @param array $htmlOptions attributes to be rendered
	 * @return string the rendering result
	 */
	public static function renderAttributes($htmlOptions)
	{
		static $specialAttributes=array(
			'async'=>1,
			'autofocus'=>1,
			'autoplay'=>1,
			'checked'=>1,
			'controls'=>1,
			'declare'=>1,
			'default'=>1,
			'defer'=>1,
			'disabled'=>1,
			'formnovalidate'=>1,
			'hidden'=>1,
			'ismap'=>1,
			'loop'=>1,
			'multiple'=>1,
			'muted'=>1,
			'nohref'=>1,
			'noresize'=>1,
			'novalidate'=>1,
			'open'=>1,
			'readonly'=>1,
			'required'=>1,
			'reversed'=>1,
			'scoped'=>1,
			'seamless'=>1,
			'selected'=>1,
			'typemustmatch'=>1,
		);

		if($htmlOptions===array())
			return '';

		$html='';
		if(isset($htmlOptions['encode']))
		{
			$raw=!$htmlOptions['encode'];
			unset($htmlOptions['encode']);
		}
		else
			$raw=false;

		foreach($htmlOptions as $name=>$value)
		{
			if(isset($specialAttributes[$name]))
			{
				if($value)
				{
					$html .= ' ' . $name;
					if(self::$renderSpecialAttributesValue)
						$html .= '="' . $name . '"';
				}
			}
			elseif($value!==null)
				$html .= ' ' . $name . '="' . ($raw ? $value : self::encode($value)) . '"';
		}

		return $html;
	}
}
