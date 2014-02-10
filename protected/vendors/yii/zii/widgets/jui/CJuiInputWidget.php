<?php
/**
 * CJuiInputWidget class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiInputWidget is the base class for JUI widgets that can collect user input.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
abstract class CJuiInputWidget extends CJuiWidget
{
	/**
	 * @var CModel the data model associated with this widget.
	 */
	public $model;
	/**
	 * @var string the attribute associated with this widget.
	 * The name can contain square brackets (e.g. 'name[1]') which is used to collect tabular data input.
	 */
	public $attribute;
	/**
	 * @var string the input name. This must be set if {@link model} is not set.
	 */
	public $name;
	/**
	 * @var string the input value
	 */
	public $value;

	/**
	 * @return array the name and the ID of the input.
	 */
	protected function resolveNameID()
	{
		if($this->name!==null)
			$name=$this->name;
		elseif(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];
		elseif($this->hasModel())
			$name=CHtml::activeName($this->model,$this->attribute);
		else
			throw new CException(Yii::t('zii','{class} must specify "model" and "attribute" or "name" property values.',array('{class}'=>get_class($this))));

		if(($id=$this->getId(false))===null)
		{
			if(isset($this->htmlOptions['id']))
				$id=$this->htmlOptions['id'];
			else
				$id=CHtml::getIdByName($name);
		}

		return array($name,$id);
	}

	/**
	 * @return boolean whether this widget is associated with a data model.
	 */
	protected function hasModel()
	{
		return $this->model instanceof CModel && $this->attribute!==null;
	}
}