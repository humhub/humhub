<?php
/**
 * CFormElement class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormElement is the base class for presenting all kinds of form element.
 *
 * CFormElement implements the way to get and set arbitrary attributes.
 *
 * @property boolean $visible Whether this element is visible and should be rendered.
 * @property mixed $parent The direct parent of this element. This could be either a {@link CForm} object or a {@link CBaseController} object
 * (a controller or a widget).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.form
 * @since 1.1
 */
abstract class CFormElement extends CComponent
{
	/**
	 * @var array list of attributes (name=>value) for the HTML element represented by this object.
	 */
	public $attributes=array();

	private $_parent;
	private $_visible;

	/**
	 * Renders this element.
	 * @return string the rendering result
	 */
	abstract function render();

	/**
	 * Constructor.
	 * @param mixed $config the configuration for this element.
	 * @param mixed $parent the direct parent of this element.
	 * @see configure
	 */
	public function __construct($config,$parent)
	{
		$this->configure($config);
		$this->_parent=$parent;
	}

	/**
	 * Converts the object to a string.
	 * This is a PHP magic method.
	 * The default implementation simply calls {@link render} and return
	 * the rendering result.
	 * @return string the string representation of this object.
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Returns a property value or an attribute value.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to read a property or attribute:
	 * <pre>
	 * $value=$element->propertyName;
	 * $value=$element->attributeName;
	 * </pre>
	 * @param string $name the property or attribute name
	 * @return mixed the property or attribute value
	 * @throws CException if the property or attribute is not defined
	 * @see __set
	 */
	public function __get($name)
	{
		$getter='get'.$name;
		if(method_exists($this,$getter))
			return $this->$getter();
		elseif(isset($this->attributes[$name]))
			return $this->attributes[$name];
		else
			throw new CException(Yii::t('yii','Property "{class}.{property}" is not defined.',
				array('{class}'=>get_class($this), '{property}'=>$name)));
	}

	/**
	 * Sets value of a property or attribute.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to set a property or attribute.
	 * <pre>
	 * $this->propertyName=$value;
	 * $this->attributeName=$value;
	 * </pre>
	 * @param string $name the property or attribute name
	 * @param mixed $value the property or attribute value
	 * @see __get
	 */
	public function __set($name,$value)
	{
		$setter='set'.$name;
		if(method_exists($this,$setter))
			$this->$setter($value);
		else
			$this->attributes[$name]=$value;
	}

	/**
	 * Configures this object with property initial values.
	 * @param mixed $config the configuration for this object. This can be an array
	 * representing the property names and their initial values.
	 * It can also be a string representing the file name of the PHP script
	 * that returns a configuration array.
	 */
	public function configure($config)
	{
		if(is_string($config))
			$config=require(Yii::getPathOfAlias($config).'.php');
		if(is_array($config))
		{
			foreach($config as $name=>$value)
				$this->$name=$value;
		}
	}

	/**
	 * Returns a value indicating whether this element is visible and should be rendered.
	 * This method will call {@link evaluateVisible} to determine the visibility of this element.
	 * @return boolean whether this element is visible and should be rendered.
	 */
	public function getVisible()
	{
		if($this->_visible===null)
			$this->_visible=$this->evaluateVisible();
		return $this->_visible;
	}

	/**
	 * @param boolean $value whether this element is visible and should be rendered.
	 */
	public function setVisible($value)
	{
		$this->_visible=$value;
	}

	/**
	 * @return mixed the direct parent of this element. This could be either a {@link CForm} object or a {@link CBaseController} object
	 * (a controller or a widget).
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Evaluates the visibility of this element.
	 * Child classes should override this method to implement the actual algorithm
	 * for determining the visibility.
	 * @return boolean whether this element is visible. Defaults to true.
	 */
	protected function evaluateVisible()
	{
		return true;
	}
}
