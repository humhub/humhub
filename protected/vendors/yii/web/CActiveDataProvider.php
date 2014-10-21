<?php
/**
 * CActiveDataProvider class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CActiveDataProvider implements a data provider based on ActiveRecord.
 *
 * CActiveDataProvider provides data in terms of ActiveRecord objects which are
 * of class {@link modelClass}. It uses the AR {@link CActiveRecord::findAll} method
 * to retrieve the data from database. The {@link criteria} property can be used to
 * specify various query options.
 *
 * CActiveDataProvider may be used in the following way:
 * <pre>
 * $dataProvider=new CActiveDataProvider('Post', array(
 *     'criteria'=>array(
 *         'condition'=>'status=1',
 *         'order'=>'create_time DESC',
 *         'with'=>array('author'),
 *     ),
 *     'countCriteria'=>array(
 *         'condition'=>'status=1',
 *         // 'order' and 'with' clauses have no meaning for the count query
 *     ),
 *     'pagination'=>array(
 *         'pageSize'=>20,
 *     ),
 * ));
 * // $dataProvider->getData() will return a list of Post objects
 * </pre>
 *
 * @property CDbCriteria $criteria The query criteria.
 * @property CDbCriteria $countCriteria The count query criteria. This property is available
 * since 1.1.14
 * @property CSort $sort The sorting object. If this is false, it means the sorting is disabled.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.1
 */
class CActiveDataProvider extends CDataProvider
{
	/**
	 * @var string the primary ActiveRecord class name. The {@link getData()} method
	 * will return a list of objects of this class.
	 */
	public $modelClass;
	/**
	 * @var CActiveRecord the AR finder instance (eg <code>Post::model()</code>).
	 * This property can be set by passing the finder instance as the first parameter
	 * to the constructor. For example, <code>Post::model()->published()</code>.
	 * @since 1.1.3
	 */
	public $model;
	/**
	 * @var string the name of key attribute for {@link modelClass}. If not set,
	 * it means the primary key of the corresponding database table will be used.
	 */
	public $keyAttribute;

	/**
	 * @var CDbCriteria
	 */
	private $_criteria;
	/**
	 * @var CDbCriteria
	 */
	private $_countCriteria;

	/**
	 * Constructor.
	 * @param mixed $modelClass the model class (e.g. 'Post') or the model finder instance
	 * (e.g. <code>Post::model()</code>, <code>Post::model()->published()</code>).
	 * @param array $config configuration (name=>value) to be applied as the initial property values of this class.
	 */
	public function __construct($modelClass,$config=array())
	{
		if(is_string($modelClass))
		{
			$this->modelClass=$modelClass;
			$this->model=$this->getModel($this->modelClass);
		}
		elseif($modelClass instanceof CActiveRecord)
		{
			$this->modelClass=get_class($modelClass);
			$this->model=$modelClass;
		}
		$this->setId(CHtml::modelName($this->model));
		foreach($config as $key=>$value)
			$this->$key=$value;
	}

	/**
	 * Returns the query criteria.
	 * @return CDbCriteria the query criteria
	 */
	public function getCriteria()
	{
		if($this->_criteria===null)
			$this->_criteria=new CDbCriteria;
		return $this->_criteria;
	}

	/**
	 * Sets the query criteria.
	 * @param CDbCriteria|array $value the query criteria. This can be either a CDbCriteria object or an array
	 * representing the query criteria.
	 */
	public function setCriteria($value)
	{
		$this->_criteria=$value instanceof CDbCriteria ? $value : new CDbCriteria($value);
	}

	/**
	 * Returns the count query criteria.
	 * @return CDbCriteria the count query criteria.
	 * @since 1.1.14
	 */
	public function getCountCriteria()
	{
		if($this->_countCriteria===null)
			return $this->getCriteria();
		return $this->_countCriteria;
	}

	/**
	 * Sets the count query criteria.
	 * @param CDbCriteria|array $value the count query criteria. This can be either a CDbCriteria object
	 * or an array representing the query criteria.
	 * @since 1.1.14
	 */
	public function setCountCriteria($value)
	{
		$this->_countCriteria=$value instanceof CDbCriteria ? $value : new CDbCriteria($value);
	}

	/**
	 * Returns the sorting object.
	 * @param string $className the sorting object class name. Parameter is available since version 1.1.13.
	 * @return CSort the sorting object. If this is false, it means the sorting is disabled.
	 */
	public function getSort($className='CSort')
	{
		if(($sort=parent::getSort($className))!==false)
			$sort->modelClass=$this->modelClass;
		return $sort;
	}

	/**
	 * Given active record class name returns new model instance.
	 *
	 * @param string $className active record class name.
	 * @return CActiveRecord active record model instance.
	 *
	 * @since 1.1.14
	 */
	protected function getModel($className)
	{
		return CActiveRecord::model($className);
	}

	/**
	 * Fetches the data from the persistent data storage.
	 * @return array list of data items
	 */
	protected function fetchData()
	{
		$criteria=clone $this->getCriteria();

		if(($pagination=$this->getPagination())!==false)
		{
			$pagination->setItemCount($this->getTotalItemCount());
			$pagination->applyLimit($criteria);
		}

		$baseCriteria=$this->model->getDbCriteria(false);

		if(($sort=$this->getSort())!==false)
		{
			// set model criteria so that CSort can use its table alias setting
			if($baseCriteria!==null)
			{
				$c=clone $baseCriteria;
				$c->mergeWith($criteria);
				$this->model->setDbCriteria($c);
			}
			else
				$this->model->setDbCriteria($criteria);
			$sort->applyOrder($criteria);
		}

		$this->model->setDbCriteria($baseCriteria!==null ? clone $baseCriteria : null);
		$data=$this->model->findAll($criteria);
		$this->model->setDbCriteria($baseCriteria);  // restore original criteria
		return $data;
	}

	/**
	 * Fetches the data item keys from the persistent data storage.
	 * @return array list of data item keys.
	 */
	protected function fetchKeys()
	{
		$keys=array();
		foreach($this->getData() as $i=>$data)
		{
			$key=$this->keyAttribute===null ? $data->getPrimaryKey() : $data->{$this->keyAttribute};
			$keys[$i]=is_array($key) ? implode(',',$key) : $key;
		}
		return $keys;
	}

	/**
	 * Calculates the total number of data items.
	 * @return integer the total number of data items.
	 */
	protected function calculateTotalItemCount()
	{
		$baseCriteria=$this->model->getDbCriteria(false);
		if($baseCriteria!==null)
			$baseCriteria=clone $baseCriteria;
		$count=$this->model->count($this->getCountCriteria());
		$this->model->setDbCriteria($baseCriteria);
		return $count;
	}
}
