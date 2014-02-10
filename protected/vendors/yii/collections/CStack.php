<?php
/**
 * This file contains classes implementing the stack feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CStack implements a stack.
 *
 * The typical stack operations are implemented, which include
 * {@link push()}, {@link pop()} and {@link peek()}. In addition,
 * {@link contains()} can be used to check if an item is contained
 * in the stack. To obtain the number of the items in the stack,
 * check the {@link getCount Count} property.
 *
 * Items in the stack may be traversed using foreach as follows,
 * <pre>
 * foreach($stack as $item) ...
 * </pre>
 *
 * @property Iterator $iterator An iterator for traversing the items in the stack.
 * @property integer $count The number of items in the stack.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.collections
 * @since 1.0
 */
class CStack extends CComponent implements IteratorAggregate,Countable
{
	/**
	 * internal data storage
	 * @var array
	 */
	private $_d=array();
	/**
	 * number of items
	 * @var integer
	 */
	private $_c=0;

	/**
	 * Constructor.
	 * Initializes the stack with an array or an iterable object.
	 * @param array $data the initial data. Default is null, meaning no initialization.
	 * @throws CException If data is not null and neither an array nor an iterator.
	 */
	public function __construct($data=null)
	{
		if($data!==null)
			$this->copyFrom($data);
	}

	/**
	 * @return array the list of items in stack
	 */
	public function toArray()
	{
		return $this->_d;
	}

	/**
	 * Copies iterable data into the stack.
	 * Note, existing data in the list will be cleared first.
	 * @param mixed $data the data to be copied from, must be an array or object implementing Traversable
	 * @throws CException If data is neither an array nor a Traversable.
	 */
	public function copyFrom($data)
	{
		if(is_array($data) || ($data instanceof Traversable))
		{
			$this->clear();
			foreach($data as $item)
			{
				$this->_d[]=$item;
				++$this->_c;
			}
		}
		elseif($data!==null)
			throw new CException(Yii::t('yii','Stack data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Removes all items in the stack.
	 */
	public function clear()
	{
		$this->_c=0;
		$this->_d=array();
	}

	/**
	 * @param mixed $item the item
	 * @return boolean whether the stack contains the item
	 */
	public function contains($item)
	{
		return array_search($item,$this->_d,true)!==false;
	}

	/**
	 * Returns the item at the top of the stack.
	 * Unlike {@link pop()}, this method does not remove the item from the stack.
	 * @return mixed item at the top of the stack
	 * @throws CException if the stack is empty
	 */
	public function peek()
	{
		if($this->_c)
			return $this->_d[$this->_c-1];
		else
			throw new CException(Yii::t('yii','The stack is empty.'));
	}

	/**
	 * Pops up the item at the top of the stack.
	 * @return mixed the item at the top of the stack
	 * @throws CException if the stack is empty
	 */
	public function pop()
	{
		if($this->_c)
		{
			--$this->_c;
			return array_pop($this->_d);
		}
		else
			throw new CException(Yii::t('yii','The stack is empty.'));
	}

	/**
	 * Pushes an item into the stack.
	 * @param mixed $item the item to be pushed into the stack
	 */
	public function push($item)
	{
		++$this->_c;
		$this->_d[]=$item;
	}

	/**
	 * Returns an iterator for traversing the items in the stack.
	 * This method is required by the interface IteratorAggregate.
	 * @return Iterator an iterator for traversing the items in the stack.
	 */
	public function getIterator()
	{
		return new CStackIterator($this->_d);
	}

	/**
	 * Returns the number of items in the stack.
	 * @return integer the number of items in the stack
	 */
	public function getCount()
	{
		return $this->_c;
	}

	/**
	 * Returns the number of items in the stack.
	 * This method is required by Countable interface.
	 * @return integer number of items in the stack.
	 */
	public function count()
	{
		return $this->getCount();
	}
}
