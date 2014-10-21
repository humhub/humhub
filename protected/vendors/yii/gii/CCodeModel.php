<?php
/**
 * CCodeModel class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCodeModel is the base class for model classes that are used to generate code.
 *
 * Each code generator should have at least one code model class that extends from this class.
 * The purpose of a code model is to represent user-supplied parameters and use them to
 * generate customized code.
 *
 * Derived classes should implement the {@link prepare} method whose main task is to
 * fill up the {@link files} property based on the user parameters.
 *
 * The {@link files} property should be filled with a set of {@link CCodeFile} instances,
 * each representing a single code file to be generated.
 *
 * CCodeModel implements the feature of "sticky attributes". A sticky attribute is an attribute
 * that can remember its last valid value, even if the user closes his browser window
 * and reopen it. To declare an attribute is sticky, simply list it in a validation rule with
 * the validator name being "sticky".
 *
 * @property array $templates A list of available code templates (name=>directory).
 * @property string $templatePath The directory that contains the template files.
 * @property string $stickyFile The file path that stores the sticky attribute values.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.gii
 * @since 1.1.2
 */
abstract class CCodeModel extends CFormModel
{
	const STATUS_NEW=1;
	const STATUS_PREVIEW=2;
	const STATUS_SUCCESS=3;
	const STATUS_ERROR=4;

	static $keywords=array(
		'__class__',
		'__dir__',
		'__file__',
		'__function__',
		'__line__',
		'__method__',
		'__namespace__',
		'abstract',
		'and',
		'array',
		'as',
		'break',
		'case',
		'catch',
		'cfunction',
		'class',
		'clone',
		'const',
		'continue',
		'declare',
		'default',
		'die',
		'do',
		'echo',
		'else',
		'elseif',
		'empty',
		'enddeclare',
		'endfor',
		'endforeach',
		'endif',
		'endswitch',
		'endwhile',
		'eval',
		'exception',
		'exit',
		'extends',
		'final',
		'final',
		'for',
		'foreach',
		'function',
		'global',
		'goto',
		'if',
		'implements',
		'include',
		'include_once',
		'instanceof',
		'interface',
		'isset',
		'list',
		'namespace',
		'new',
		'old_function',
		'or',
		'parent',
		'php_user_filter',
		'print',
		'private',
		'protected',
		'public',
		'require',
		'require_once',
		'return',
		'static',
		'switch',
		'this',
		'throw',
		'try',
		'unset',
		'use',
		'var',
		'while',
		'xor',
	);

	/**
	 * @var array user confirmations on whether to overwrite existing code files with the newly generated ones.
	 * The value of this property is internally managed by this class and {@link CCodeGenerator}.
	 */
	public $answers;
	/**
	 * @var string the name of the code template that the user has selected.
	 * The value of this property is internally managed by this class and {@link CCodeGenerator}.
	 */
	public $template;
	/**
	 * @var array a list of {@link CCodeFile} objects that represent the code files to be generated.
	 * The {@link prepare()} method is responsible to populate this property.
	 */
	public $files=array();
	/**
	 * @var integer the status of this model. T
	 * The value of this property is internally managed by {@link CCodeGenerator}.
	 */
	public $status=self::STATUS_NEW;

	private $_stickyAttributes=array();

	/**
	 * Prepares the code files to be generated.
	 * This is the main method that child classes should implement. It should contain the logic
	 * that populates the {@link files} property with a list of code files to be generated.
	 */
	abstract public function prepare();

	/**
	 * Declares the model validation rules.
	 * Child classes must override this method in the following format:
	 * <pre>
	 * return array_merge(parent::rules(), array(
	 *     ...rules for the child class...
	 * ));
	 * </pre>
	 * @return array validation rules
	 */
	public function rules()
	{
		return array(
			array('template', 'required'),
			array('template', 'validateTemplate', 'skipOnError'=>true),
			array('template', 'sticky'),
		);
	}

	/**
	 * Validates the template selection.
	 * This method validates whether the user selects an existing template
	 * and the template contains all required template files as specified in {@link requiredTemplates}.
	 * @param string $attribute the attribute to be validated
	 * @param array $params validation parameters
	 */
	public function validateTemplate($attribute,$params)
	{
		$templates=$this->templates;
		if(!isset($templates[$this->template]))
			$this->addError('template', 'Invalid template selection.');
		else
		{
			$templatePath=$this->templatePath;
			foreach($this->requiredTemplates() as $template)
			{
				if(!is_file($templatePath.'/'.$template))
					$this->addError('template', "Unable to find the required code template file '$template'.");
			}
		}
	}

	/**
	 * Checks if the named class exists (in a case sensitive manner).
	 * @param string $name class name to be checked
	 * @return boolean whether the class exists
	 */
	public function classExists($name)
	{
		return class_exists($name,false) && in_array($name, get_declared_classes());
	}

	/**
	 * Declares the model attribute labels.
	 * Child classes must override this method in the following format:
	 * <pre>
	 * return array_merge(parent::attributeLabels(), array(
	 *     ...labels for the child class attributes...
	 * ));
	 * </pre>
	 * @return array the attribute labels
	 */
	public function attributeLabels()
	{
		return array(
			'template'=>'Code Template',
		);
	}

	/**
	 * Returns a list of code templates that are required.
	 * Derived classes usually should override this method.
	 * @return array list of code templates that are required. They should be file paths
	 * relative to {@link templatePath}.
	 */
	public function requiredTemplates()
	{
		return array();
	}

	/**
	 * Saves the generated code into files.
	 */
	public function save()
	{
		$result=true;
		foreach($this->files as $file)
		{
			if($this->confirmed($file))
				$result=$file->save() && $result;
		}
		return $result;
	}

	/**
	 * Returns the message to be displayed when the newly generated code is saved successfully.
	 * Child classes should override this method if the message needs to be customized.
	 * @return string the message to be displayed when the newly generated code is saved successfully.
	 */
	public function successMessage()
	{
		return 'The code has been generated successfully.';
	}

	/**
	 * Returns the message to be displayed when some error occurred during code file saving.
	 * Child classes should override this method if the message needs to be customized.
	 * @return string the message to be displayed when some error occurred during code file saving.
	 */
	public function errorMessage()
	{
		return 'There was some error when generating the code. Please check the following messages.';
	}

	/**
	 * Returns a list of available code templates (name=>directory).
	 * This method simply returns the {@link CCodeGenerator::templates} property value.
	 * @return array a list of available code templates (name=>directory).
	 */
	public function getTemplates()
	{
		return Yii::app()->controller->templates;
	}

	/**
	 * @return string the directory that contains the template files.
	 * @throws CHttpException if {@link templates} is empty or template selection is invalid
	 */
	public function getTemplatePath()
	{
		$templates=$this->getTemplates();
		if(isset($templates[$this->template]))
			return $templates[$this->template];
		elseif(empty($templates))
			throw new CHttpException(500,'No templates are available.');
		else
			throw new CHttpException(500,'Invalid template selection.');

	}

	/**
	 * @param CCodeFile $file whether the code file should be saved
	 * @return bool whether the confirmation is found in {@link answers} with appropriate {@link operation}
	 */
	public function confirmed($file)
	{
		return $this->answers===null && $file->operation===CCodeFile::OP_NEW
			|| is_array($this->answers) && isset($this->answers[md5($file->path)]);
	}

	/**
	 * Generates the code using the specified code template file.
	 * This method is manly used in {@link generate} to generate code.
	 * @param string $templateFile the code template file path
	 * @param array $_params_ a set of parameters to be extracted and made available in the code template
	 * @throws CException is template file does not exist
	 * @return string the generated code
	 */
	public function render($templateFile,$_params_=null)
	{
		if(!is_file($templateFile))
			throw new CException("The template file '$templateFile' does not exist.");

		if(is_array($_params_))
			extract($_params_,EXTR_PREFIX_SAME,'params');
		else
			$params=$_params_;
		ob_start();
		ob_implicit_flush(false);
		require($templateFile);
		return ob_get_clean();
	}

	/**
	 * @return string the code generation result log.
	 */
	public function renderResults()
	{
		$output='Generating code using template "'.$this->templatePath."\"...\n";
		foreach($this->files as $file)
		{
			if($file->error!==null)
				$output.="<span class=\"error\">generating {$file->relativePath}<br/>           {$file->error}</span>\n";
			elseif($file->operation===CCodeFile::OP_NEW && $this->confirmed($file))
				$output.=' generated '.$file->relativePath."\n";
			elseif($file->operation===CCodeFile::OP_OVERWRITE && $this->confirmed($file))
				$output.=' overwrote '.$file->relativePath."\n";
			else
				$output.='   skipped '.$file->relativePath."\n";
		}
		$output.="done!\n";
		return $output;
	}

	/**
	 * The "sticky" validator.
	 * This validator does not really validate the attributes.
	 * It actually saves the attribute value in a file to make it sticky.
	 * @param string $attribute the attribute to be validated
	 * @param array $params the validation parameters
	 */
	public function sticky($attribute,$params)
	{
		if(!$this->hasErrors())
			$this->_stickyAttributes[$attribute]=$this->$attribute;
	}

	/**
	 * Loads sticky attributes from a file and populates them into the model.
	 */
	public function loadStickyAttributes()
	{
		$this->_stickyAttributes=array();
		$path=$this->getStickyFile();
		if(is_file($path))
		{
			$result=@include($path);
			if(is_array($result))
			{
				$this->_stickyAttributes=$result;
				foreach($this->_stickyAttributes as $name=>$value)
				{
					if(property_exists($this,$name) || $this->canSetProperty($name))
						$this->$name=$value;
				}
			}
		}
	}

	/**
	 * Saves sticky attributes into a file.
	 */
	public function saveStickyAttributes()
	{
		$path=$this->getStickyFile();
		@mkdir(dirname($path),0755,true);
		file_put_contents($path,"<?php\nreturn ".var_export($this->_stickyAttributes,true).";\n");
	}

	/**
	 * @return string the file path that stores the sticky attribute values.
	 */
	public function getStickyFile()
	{
		return Yii::app()->runtimePath.'/gii-'.Yii::getVersion().'/'.get_class($this).'.php';
	}

	/**
	 * Converts a word to its plural form.
	 * Note that this is for English only!
	 * For example, 'apple' will become 'apples', and 'child' will become 'children'.
	 * @param string $name the word to be pluralized
	 * @return string the pluralized word
	 */
	public function pluralize($name)
	{
		$rules=array(
			'/(m)ove$/i' => '\1oves',
			'/(f)oot$/i' => '\1eet',
			'/(c)hild$/i' => '\1hildren',
			'/(h)uman$/i' => '\1umans',
			'/(m)an$/i' => '\1en',
			'/(s)taff$/i' => '\1taff',
			'/(t)ooth$/i' => '\1eeth',
			'/(p)erson$/i' => '\1eople',
			'/([m|l])ouse$/i' => '\1ice',
			'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(shea|lea|loa|thie)f$/i' => '\1ves',
			'/([ti])um$/i' => '\1a',
			'/(tomat|potat|ech|her|vet)o$/i' => '\1oes',
			'/(bu)s$/i' => '\1ses',
			'/(ax|test)is$/i' => '\1es',
			'/s$/' => 's',
		);
		foreach($rules as $rule=>$replacement)
		{
			if(preg_match($rule,$name))
				return preg_replace($rule,$replacement,$name);
		}
		return $name.'s';
	}

	/**
	 * Converts a class name into a HTML ID.
	 * For example, 'PostTag' will be converted as 'post-tag'.
	 * @param string $name the string to be converted
	 * @return string the resulting ID
	 */
	public function class2id($name)
	{
		return trim(strtolower(str_replace('_','-',preg_replace('/(?<![A-Z])[A-Z]/', '-\0', $name))),'-');
	}

	/**
	 * Converts a class name into space-separated words.
	 * For example, 'PostTag' will be converted as 'Post Tag'.
	 * @param string $name the string to be converted
	 * @param boolean $ucwords whether to capitalize the first letter in each word
	 * @return string the resulting words
	 */
	public function class2name($name,$ucwords=true)
	{
		$result=trim(strtolower(str_replace('_',' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name))));
		return $ucwords ? ucwords($result) : $result;
	}

	/**
	 * Converts a class name into a variable name with the first letter in lower case.
	 * This method is provided because lcfirst() PHP function is only available for PHP 5.3+.
	 * @param string $name the class name
	 * @return string the variable name converted from the class name
	 * @since 1.1.4
	 */
	public function class2var($name)
	{
		$name[0]=strtolower($name[0]);
		return $name;
	}

	/**
	 * Validates an attribute to make sure it is not taking a PHP reserved keyword.
	 * @param string $attribute the attribute to be validated
	 * @param array $params validation parameters
	 */
	public function validateReservedWord($attribute,$params)
	{
		$value=$this->$attribute;
		if(in_array(strtolower($value),self::$keywords))
			$this->addError($attribute, $this->getAttributeLabel($attribute).' cannot take a reserved PHP keyword.');
	}
}