<?php
/**
 * CFormStringElement class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormStringElement represents a string in a form.
 *
 * @property string $on Scenario names separated by commas. Defaults to null.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.form
 * @since 1.1
 */
class CFormStringElement extends CFormElement
{
	/**
	 * @var string the string content
	 */
	public $content;

	private $_on;

	/**
	 * Returns a value indicating under which scenarios this string is visible.
	 * If the value is empty, it means the string is visible under all scenarios.
	 * Otherwise, only when the model is in the scenario whose name can be found in
	 * this value, will the string be visible. See {@link CModel::scenario} for more
	 * information about model scenarios.
	 * @return string scenario names separated by commas. Defaults to null.
	 */
	public function getOn()
	{
		return $this->_on;
	}

	/**
	 * @param string $value scenario names separated by commas.
	 */
	public function setOn($value)
	{
		$this->_on=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Renders this element.
	 * The default implementation simply returns {@link content}.
	 * @return string the string content
	 */
	public function render()
	{
		return $this->content;
	}

	/**
	 * Evaluates the visibility of this element.
	 * This method will check the {@link on} property to see if
	 * the model is in a scenario that should have this string displayed.
	 * @return boolean whether this element is visible.
	 */
	protected function evaluateVisible()
	{
		return empty($this->_on) || in_array($this->getParent()->getModel()->getScenario(),$this->_on);
	}
}
