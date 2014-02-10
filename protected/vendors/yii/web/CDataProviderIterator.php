<?php
/**
 * CDataProviderIterator class file.
 *
 * @author Charles Pick <charles.pick@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDataProviderIterator allows iteration over large data sets without holding the entire set in memory.
 *
 * CDataProviderIterator iterates over the results of a data provider, starting at the first page
 * of results and ending at the last page. It is usually only suited for use with {@link CActiveDataProvider}.
 *
 * For example, the following code will iterate over all registered users (active record class User) without
 * running out of memory, even if there are millions of users in the database.
 * <pre>
 * $dataProvider = new CActiveDataProvider("User");
 * $iterator = new CDataProviderIterator($dataProvider);
 * foreach($iterator as $user) {
 *	 echo $user->name."\n";
 * }
 * </pre>
 *
 * @property CDataProvider $dataProvider the data provider to iterate over
 * @property integer $totalItemCount the total number of items in the iterator
 *
 * @author Charles Pick <charles.pick@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @package system.web
 * @since 1.1.13
 */
class CDataProviderIterator extends CComponent implements Iterator, Countable
{
	private $_dataProvider;
	private $_currentIndex=-1;
	private $_currentPage=0;
	private $_totalItemCount=-1;
	private $_items;

	/**
	 * Constructor.
	 * @param CDataProvider $dataProvider the data provider to iterate over
	 * @param integer $pageSize pageSize to use for iteration. This is the number of objects loaded into memory at the same time.
	 */
	public function __construct(CDataProvider $dataProvider, $pageSize=null)
	{
		$this->_dataProvider=$dataProvider;
		$this->_totalItemCount=$dataProvider->getTotalItemCount();

		if(($pagination=$this->_dataProvider->getPagination())===false)
			$this->_dataProvider->setPagination(new CPagination());

		if($pageSize!==null)
			$pagination->setPageSize($pageSize);
	}

	/**
	 * Returns the data provider to iterate over
	 * @return CDataProvider the data provider to iterate over
	 */
	public function getDataProvider()
	{
		return $this->_dataProvider;
	}

	/**
	 * Gets the total number of items to iterate over
	 * @return integer the total number of items to iterate over
	 */
	public function getTotalItemCount()
	{
		return $this->_totalItemCount;
	}

	/**
	 * Loads a page of items
	 * @return array the items from the next page of results
	 */
	protected function loadPage()
	{
		$this->_dataProvider->getPagination()->setCurrentPage($this->_currentPage);
		return $this->_items=$this->dataProvider->getData(true);
	}

	/**
	 * Gets the current item in the list.
	 * This method is required by the Iterator interface.
	 * @return mixed the current item in the list
	 */
	public function current()
	{
		return $this->_items[$this->_currentIndex];
	}

	/**
	 * Gets the key of the current item.
	 * This method is required by the Iterator interface.
	 * @return integer the key of the current item
	 */
	public function key()
	{
		$pageSize=$this->_dataProvider->getPagination()->getPageSize();
		return $this->_currentPage*$pageSize+$this->_currentIndex;
	}

	/**
	 * Moves the pointer to the next item in the list.
	 * This method is required by the Iterator interface.
	 */
	public function next()
	{
		$pageSize=$this->_dataProvider->getPagination()->getPageSize();
		$this->_currentIndex++;
		if($this->_currentIndex >= $pageSize)
		{
			$this->_currentPage++;
			$this->_currentIndex=0;
			$this->loadPage();
		}
	}

	/**
	 * Rewinds the iterator to the start of the list.
	 * This method is required by the Iterator interface.
	 */
	public function rewind()
	{
		$this->_currentIndex=0;
		$this->_currentPage=0;
		$this->loadPage();
	}

	/**
	 * Checks if the current position is valid or not.
	 * This method is required by the Iterator interface.
	 * @return boolean true if this index is valid
	 */
	public function valid()
	{
		return $this->key() < $this->_totalItemCount;
	}

	/**
	 * Gets the total number of items in the dataProvider.
	 * This method is required by the Countable interface.
	 * @return integer the total number of items
	 */
	public function count()
	{
		return $this->_totalItemCount;
	}
}