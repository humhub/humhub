<?php
/**
 * CFormElementCollection class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormElementCollection implements the collection for storing form elements.
 *
 * Because CFormElementCollection extends from {@link CMap}, it can be used like an associative array.
 * For example,
 * <pre>
 * $element=$collection['username'];
 * $collection['username']=array('type'=>'text', 'maxlength'=>128);
 * $collection['password']=new CFormInputElement(array('type'=>'password'),$form);
 * $collection[]='some string';
 * </pre>
 *
 * CFormElementCollection can store three types of value: a configuration array, a {@link CFormElement}
 * object, or a string, as shown in the above example. Internally, these values will be converted
 * to {@link CFormElement} objects.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.form
 * @since 1.1
 */
class CFormElementCollection extends CMap
{
	private $_form;
	private $_forButtons;

	/**
	 * Constructor.
	 * @param CForm $form the form object that owns this collection
	 * @param boolean $forButtons whether this collection is used to store buttons.
	 */
	public function __construct($form,$forButtons=false)
	{
		parent::__construct();
		$this->_form=$form;
		$this->_forButtons=$forButtons;
	}

	/**
	 * Adds an item to the collection.
	 * This method overrides the parent implementation to ensure
	 * only configuration arrays, strings, or {@link CFormElement} objects
	 * can be stored in this collection.
	 * @param mixed $key key
	 * @param mixed $value value
	 * @throws CException if the value is invalid.
	 */
	public function add($key,$value)
	{
		if(is_array($value))
		{
			if(is_string($key))
				$value['name']=$key;

			if($this->_forButtons)
			{
				$class=$this->_form->buttonElementClass;
				$element=new $class($value,$this->_form);
			}
			else
			{
				if(!isset($value['type']))
					$value['type']='text';
				if($value['type']==='string')
				{
					unset($value['type'],$value['name']);
					$element=new CFormStringElement($value,$this->_form);
				}
				elseif(!strcasecmp(substr($value['type'],-4),'form'))	// a form
				{
					$class=$value['type']==='form' ? get_class($this->_form) : Yii::import($value['type']);
					$element=new $class($value,null,$this->_form);
				}
				else
				{
					$class=$this->_form->inputElementClass;
					$element=new $class($value,$this->_form);
				}
			}
		}
		elseif($value instanceof CFormElement)
		{
			if(property_exists($value,'name') && is_string($key))
				$value->name=$key;
			$element=$value;
		}
		else
			$element=new CFormStringElement(array('content'=>$value),$this->_form);
		parent::add($key,$element);
		$this->_form->addedElement($key,$element,$this->_forButtons);
	}

	/**
	 * Removes the specified element by key.
	 * @param string $key the name of the element to be removed from the collection
	 */
	public function remove($key)
	{
		if(($item=parent::remove($key))!==null)
			$this->_form->removedElement($key,$item,$this->_forButtons);
	}
}
