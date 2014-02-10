<?php
/**
 * CAutoComplete class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CAutoComplete generates an auto-complete text field.
 *
 * CAutoComplete is based on the {@link http://plugins.jquery.com/project/autocompletex jQuery Autocomplete}.
 *
 * This class is deprecated since Yii 1.1.3. Consider using CJuiAutoComplete.
 * There is {@link http://www.learningjquery.com/2010/06/autocomplete-migration-guide a good migration guide from the author of both JavaScript solutions}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 * @deprecated in 1.1.3
 */
class CAutoComplete extends CInputWidget
{
	/**
	 * @var boolean whether to show the autocomplete using a text area. Defaults to false,
	 * meaning a text field is used.
	 */
	public $textArea=false;
	/**
	 * @var array data that would be saved as client-side data to provide candidate selections.
	 * Each array element can be string or an associative array.
	 * The {@link url} property will be ignored if this property is set.
	 * @see url
	 */
	public $data;
	/**
	 * @var string|array the URL that can return the candidate selections.
	 * A 'q' GET parameter will be sent with the URL which contains what the user has entered so far.
	 * If the URL is given as an array, it is considered as a route to a controller action and will
	 * be used to generate a URL using {@link CController::createUrl};
	 * If the URL is an empty string, the currently requested URL is used.
	 * This property will be ignored if {@link data} is set.
	 * @see data
 	 */
	public $url='';
	/**
	 * @var mixed the CSS file used for the widget. Defaults to null, meaning
	 * using the default CSS file included together with the widget.
	 * If false, no CSS file will be used. Otherwise, the specified CSS file
	 * will be included when using this widget.
	 */
	public $cssFile;
	/**
	 * @var integer the minimum number of characters a user has to type before
	 * the autocompleter activates. Defaults to 1.
	 */
	public $minChars;
	/**
	 * @var integer the delay in milliseconds the autocompleter waits after
	 * a keystroke to activate itself. Defaults to 400.
	 */
	public $delay;
	/**
	 * @var integer the number of backend query results to store in cache.
	 * If set to 1 (the current result), no caching will happen. Must be >= 1. Defaults to 10.
	 */
	public $cacheLength;
	/**
	 * @var boolean whether or not the autocompleter can use a cache for more
	 * specific queries. This means that all matches of "foot" are a subset
	 * of all matches for "foo". Usually this is true, and using this options
	 * decreases server load and increases performance. Only useful with
	 * cacheLength settings bigger than one, like 10. Defaults to true.
	 */
	public $matchSubset;
	/**
	 * @var boolean whether or not the comparison is case sensitive. Important
	 * only if you use caching. Defaults to false.
	 */
	public $matchCase;
	/**
	 * @var boolean whether or not the comparison looks inside
	 * (i.e. does "ba" match "foo bar") the search results. Important only if
	 * you use caching. Don't mix with autofill. Defaults to false.
	 */
	public $matchContains;
	/**
	 * @var boolean if set to true, the autocompleter will only allow results that
	 * are presented by the backend. Note that illegal values result in an empty
	 * input box. Defaults to false.
	 */
	public $mustMatch;
	/**
	 * @var boolean if this is set to true, the first autocomplete value will
	 * be automatically selected on tab/return, even if it has not been handpicked
	 * by keyboard or mouse action. If there is a handpicked (highlighted) result,
	 * that result will take precedence. Defaults to true.
	 */
	public $selectFirst;
	/**
	 * @var array extra parameters for the backend. If you were to specify
	 * array('bar'=>4), the autocompleter would call the backend with a GET
	 * parameter 'bar' 4. The param can be a function that is called to calculate
	 * the param before each request.
	 */
	public $extraParams;
	/**
	 * @var string a javascript function that provides advanced markup for an item.
	 * For each row of results, this function will be called. The returned value will
	 * be displayed inside an LI element in the results list. Autocompleter will
	 * provide 4 parameters: the results row, the position of the row in the list of
	 * results (starting at 1), the number of items in the list of results and the search term.
	 * The default behavior assumes that a single row contains a single value.
	 */
	public $formatItem;
	/**
	 * @var string a javascript function that can be used to limit the data that autocomplete
	 * searches for matches. For example, there may be items you want displayed to the user,
	 * but don't want included in the data that's searched. The function is called with the same arguments
	 * as {@link formatItem}. Defaults to formatItem.
	 */
	public $formatMatch;
	/**
	 * @var string a javascript function that provides the formatting for the value to be
	 * put into the input field. Again three arguments: Data, position (starting with one) and
	 * total number of data. The default behavior assumes either plain data to use as result
	 * or uses the same value as provided by formatItem.
	 */
	public $formatResult;
	/**
	 * @var boolean whether to allow more than one autocompleted-value to enter. Defaults to false.
	 */
	public $multiple;
	/**
	 * @var string seperator to put between values when using multiple option. Defaults to ", ".
	 */
	public $multipleSeparator;
	/**
	 * @var integer specify a custom width for the select box. Defaults to the width of the input element.
	 */
	public $width;
	/**
	 * @var boolean fill the textinput while still selecting a value, replacing the value
	 * if more is typed or something else is selected. Defaults to false.
	 */
	public $autoFill;
	/**
	 * @var integer limit the number of items in the select box. Is also sent as
	 * a "limit" parameter with a remote request. Defaults to 10.
	 */
	public $max;
	/**
	 * @var boolean|string Whether and how to highlight matches in the select box.
	 * Set to false to disable. Set to a javascript function to customize.
	 * The function gets the value as the first argument and the search term as the
	 * second and must return the formatted value. Defaults to Wraps the search term in a &lt;strong&gt; element.
	 */
	public $highlight;
	/**
	 * @var boolean whether to scroll when more results than configured via scrollHeight are available. Defaults to true.
	 */
	public $scroll;
	/**
	 * @var integer height of scrolled autocomplete control in pixels. Defaults to 180.
	 */
	public $scrollHeight;
	/**
	 * @var string the CSS class for the input element. Defaults to "ac_input".
	 */
	public $inputClass;
	/**
	 * @var string the CSS class for the dropdown list. Defaults to "ac_results".
	 */
	public $resultsClass;
	/**
	 * @var string the CSS class used when the data is being loaded from backend. Defaults to "ac_loading".
	 */
	public $loadingClass;
	/**
	 * @var array additional options that can be passed to the constructor of the autocomplete js object.
	 * This allows you to override existing functions of the autocomplete js class (e.g. the parse() function)
	 *
	 * If you want to provide JavaScript native code, you have to wrap the string with {@link CJavaScriptExpression} otherwise it will
	 * be enclosed by quotes.
	 */
	public $options=array();
	/**
	 * @var string the chain of method calls that would be appended at the end of the autocomplete constructor.
	 * For example, ".result(function(...){})" would cause the specified js function to execute
	 * when the user selects an option.
	 */
	public $methodChain;

	/**
	 * Initializes the widget.
	 * This method registers all needed client scripts and renders
	 * the autocomplete input.
	 */
	public function init()
	{
		list($name,$id)=$this->resolveNameID();
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];

		$this->registerClientScript();

		if($this->hasModel())
		{
			$field=$this->textArea ? 'activeTextArea' : 'activeTextField';
			echo CHtml::$field($this->model,$this->attribute,$this->htmlOptions);
		}
		else
		{
			$field=$this->textArea ? 'textArea' : 'textField';
			echo CHtml::$field($name,$this->value,$this->htmlOptions);
		}
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 */
	public function registerClientScript()
	{
		$id=$this->htmlOptions['id'];

		$acOptions=$this->getClientOptions();
		$options=$acOptions===array()?'{}' : CJavaScript::encode($acOptions);

		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('autocomplete');
		if($this->data!==null)
			$data=CJavaScript::encode($this->data);
		else
		{
			$url=CHtml::normalizeUrl($this->url);
			$data='"'.$url.'"';
		}
		$cs->registerScript('Yii.CAutoComplete#'.$id,"jQuery(\"#{$id}\").legacyautocomplete($data,{$options}){$this->methodChain};");

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
			$url=$cs->getCoreScriptUrl().'/autocomplete/jquery.autocomplete.css';
		$cs->registerCssFile($url);
	}

	/**
	 * @return array the javascript options
	 */
	protected function getClientOptions()
	{
		static $properties=array(
			'minChars', 'delay', 'cacheLength', 'matchSubset',
			'matchCase', 'matchContains', 'mustMatch', 'selectFirst',
			'extraParams', 'multiple', 'multipleSeparator', 'width',
			'autoFill', 'max', 'scroll', 'scrollHeight', 'inputClass',
			'resultsClass', 'loadingClass');
		static $functions=array('formatItem', 'formatMatch', 'formatResult', 'highlight');

		$options=$this->options;
		foreach($properties as $property)
		{
			if($this->$property!==null)
				$options[$property]=$this->$property;
		}
		foreach($functions as $func)
		{
			if($this->$func!==null)
			{
				if($this->$func instanceof CJavaScriptExpression)
					$options[$func]=$this->$func;
				else
					$options[$func]=new CJavaScriptExpression($this->$func);
			}
		}

		return $options;
	}
}
