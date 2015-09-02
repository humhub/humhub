<?php
/**
 * CInputWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInputWidget is the base class for widgets that collect user inputs.
 *
 * CInputWidget declares properties common among input widgets. An input widget
 * can be associated with a data model and an attribute, or a name and a value.
 * If the former, the name and the value will be generated automatically.
 * Child classes may use {@link resolveNameID} and {@link hasModel}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
abstract class CInputWidget extends CWidget
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
	 * @var array additional HTML options to be rendered in the input tag
	 */
	public $htmlOptions=array();


	/**
	 * @return array the name and the ID of the input.
	 * @throws CException in case input name and ID cannot be resolved.
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
			throw new CException(Yii::t('yii','{class} must specify "model" and "attribute" or "name" property values.',array('{class}'=>get_class($this))));

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