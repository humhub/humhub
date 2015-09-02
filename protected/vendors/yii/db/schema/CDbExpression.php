<?php
/**
 * CDbExpression class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbExpression represents a DB expression that does not need escaping.
 * CDbExpression is mainly used in {@link CActiveRecord} as attribute values.
 * When inserting or updating a {@link CActiveRecord}, attribute values of
 * type CDbExpression will be directly put into the corresponding SQL statement
 * without escaping. A typical usage is that an attribute is set with 'NOW()'
 * expression so that saving the record would fill the corresponding column
 * with the current DB server timestamp.
 *
 * Starting from version 1.1.1, one can also specify parameters to be bound
 * for the expression. For example, if the expression is 'LOWER(:value)', then
 * one can set {@link params} to be <code>array(':value'=>$value)</code>.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.schema
 */
class CDbExpression extends CComponent
{
	/**
	 * @var string the DB expression
	 */
	public $expression;
	/**
	 * @var array list of parameters that should be bound for this expression.
	 * The keys are placeholders appearing in {@link expression}, while the values
	 * are the corresponding parameter values.
	 * @since 1.1.1
	 */
	public $params=array();

	/**
	 * Constructor.
	 * @param string $expression the DB expression
	 * @param array $params parameters
	 */
	public function __construct($expression,$params=array())
	{
		$this->expression=$expression;
		$this->params=$params;
	}

	/**
	 * String magic method
	 * @return string the DB expression
	 */
	public function __toString()
	{
		return $this->expression;
	}
}