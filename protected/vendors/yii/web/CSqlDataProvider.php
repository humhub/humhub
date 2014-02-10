<?php
/**
 * CSqlDataProvider implements a data provider based on a plain SQL statement.
 *
 * CSqlDataProvider provides data in terms of arrays, each representing a row of query result.
 *
 * Like other data providers, CSqlDataProvider also supports sorting and pagination.
 * It does so by modifying the given {@link sql} statement with "ORDER BY" and "LIMIT"
 * clauses. You may configure the {@link sort} and {@link pagination} properties to
 * customize sorting and pagination behaviors.
 *
 * CSqlDataProvider may be used in the following way:
 * <pre>
 * $count=Yii::app()->db->createCommand('SELECT COUNT(*) FROM tbl_user')->queryScalar();
 * $sql='SELECT * FROM tbl_user';
 * $dataProvider=new CSqlDataProvider($sql, array(
 *     'totalItemCount'=>$count,
 *     'sort'=>array(
 *         'attributes'=>array(
 *              'id', 'username', 'email',
 *         ),
 *     ),
 *     'pagination'=>array(
 *         'pageSize'=>10,
 *     ),
 * ));
 * // $dataProvider->getData() will return a list of arrays.
 * </pre>
 *
 * Note: if you want to use the pagination feature, you must configure the {@link totalItemCount} property
 * to be the total number of rows (without pagination). And if you want to use the sorting feature,
 * you must configure {@link sort} property so that the provider knows which columns can be sorted.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.1.4
 */
class CSqlDataProvider extends CDataProvider
{
	/**
	 * @var CDbConnection the database connection to be used in the queries.
	 * Defaults to null, meaning using Yii::app()->db.
	 */
	public $db;
	/**
	 * @var string|CDbCommand the SQL statement to be used for fetching data rows.
	 * Since version 1.1.13 this can also be an instance of {@link CDbCommand}.
	 */
	public $sql;
	/**
	 * @var array parameters (name=>value) to be bound to the SQL statement.
	 */
	public $params=array();
	/**
	 * @var string the name of key field. Defaults to 'id'.
	 */
	public $keyField='id';

	/**
	 * Constructor.
	 * @param string|CDbCommand $sql the SQL statement to be used for fetching data rows. Since version 1.1.13 this can also be an instance of {@link CDbCommand}.
	 * @param array $config configuration (name=>value) to be applied as the initial property values of this class.
	 */
	public function __construct($sql,$config=array())
	{
		$this->sql=$sql;
		foreach($config as $key=>$value)
			$this->$key=$value;
	}

	/**
	 * Fetches the data from the persistent data storage.
	 * @return array list of data items
	 */
	protected function fetchData()
	{
		if(!($this->sql instanceof CDbCommand))
		{
			$db=$this->db===null ? Yii::app()->db : $this->db;
			$command=$db->createCommand($this->sql);
		}
		else
			$command=clone $this->sql;

		if(($sort=$this->getSort())!==false)
		{
			$order=$sort->getOrderBy();
			if(!empty($order))
			{
				if(preg_match('/\s+order\s+by\s+[\w\s,]+$/i',$command->text))
					$command->text.=', '.$order;
				else
					$command->text.=' ORDER BY '.$order;
			}
		}

		if(($pagination=$this->getPagination())!==false)
		{
			$pagination->setItemCount($this->getTotalItemCount());
			$limit=$pagination->getLimit();
			$offset=$pagination->getOffset();
			$command->text=$command->getConnection()->getCommandBuilder()->applyLimit($command->text,$limit,$offset);
		}

		foreach($this->params as $name=>$value)
			$command->bindValue($name,$value);

		return $command->queryAll();
	}

	/**
	 * Fetches the data item keys from the persistent data storage.
	 * @return array list of data item keys.
	 */
	protected function fetchKeys()
	{
		$keys=array();
		foreach($this->getData() as $i=>$data)
			$keys[$i]=$data[$this->keyField];
		return $keys;
	}

	/**
	 * Calculates the total number of data items.
	 * This method is invoked when {@link getTotalItemCount()} is invoked
	 * and {@link totalItemCount} is not set previously.
	 * The default implementation simply returns 0.
	 * You may override this method to return accurate total number of data items.
	 * @return integer the total number of data items.
	 */
	protected function calculateTotalItemCount()
	{
		return 0;
	}
}
