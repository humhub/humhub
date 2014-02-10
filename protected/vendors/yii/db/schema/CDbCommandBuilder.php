<?php
/**
 * CDbCommandBuilder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbCommandBuilder provides basic methods to create query commands for tables.
 *
 * @property CDbConnection $dbConnection Database connection.
 * @property CDbSchema $schema The schema for this command builder.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.schema
 * @since 1.0
 */
class CDbCommandBuilder extends CComponent
{
	const PARAM_PREFIX=':yp';

	private $_schema;
	private $_connection;

	/**
	 * @param CDbSchema $schema the schema for this command builder
	 */
	public function __construct($schema)
	{
		$this->_schema=$schema;
		$this->_connection=$schema->getDbConnection();
	}

	/**
	 * @return CDbConnection database connection.
	 */
	public function getDbConnection()
	{
		return $this->_connection;
	}

	/**
	 * @return CDbSchema the schema for this command builder.
	 */
	public function getSchema()
	{
		return $this->_schema;
	}

	/**
	 * Returns the last insertion ID for the specified table.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @return mixed last insertion id. Null is returned if no sequence name.
	 */
	public function getLastInsertID($table)
	{
		$this->ensureTable($table);
		if($table->sequenceName!==null)
			return $this->_connection->getLastInsertID($table->sequenceName);
		else
			return null;
	}

	/**
	 * Creates a SELECT command for a single table.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param CDbCriteria $criteria the query criteria
	 * @param string $alias the alias name of the primary table. Defaults to 't'.
	 * @return CDbCommand query command.
	 */
	public function createFindCommand($table,$criteria,$alias='t')
	{
		$this->ensureTable($table);
		$select=is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select;
		if($criteria->alias!='')
			$alias=$criteria->alias;
		$alias=$this->_schema->quoteTableName($alias);

		// issue 1432: need to expand * when SQL has JOIN
		if($select==='*' && !empty($criteria->join))
		{
			$prefix=$alias.'.';
			$select=array();
			foreach($table->getColumnNames() as $name)
				$select[]=$prefix.$this->_schema->quoteColumnName($name);
			$select=implode(', ',$select);
		}

		$sql=($criteria->distinct ? 'SELECT DISTINCT':'SELECT')." {$select} FROM {$table->rawName} $alias";
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyGroup($sql,$criteria->group);
		$sql=$this->applyHaving($sql,$criteria->having);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);
		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}

	/**
	 * Creates a COUNT(*) command for a single table.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param CDbCriteria $criteria the query criteria
	 * @param string $alias the alias name of the primary table. Defaults to 't'.
	 * @return CDbCommand query command.
	 */
	public function createCountCommand($table,$criteria,$alias='t')
	{
		$this->ensureTable($table);
		if($criteria->alias!='')
			$alias=$criteria->alias;
		$alias=$this->_schema->quoteTableName($alias);

		if(!empty($criteria->group) || !empty($criteria->having))
		{
			$select=is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select;
			if($criteria->alias!='')
				$alias=$criteria->alias;
			$sql=($criteria->distinct ? 'SELECT DISTINCT':'SELECT')." {$select} FROM {$table->rawName} $alias";
			$sql=$this->applyJoin($sql,$criteria->join);
			$sql=$this->applyCondition($sql,$criteria->condition);
			$sql=$this->applyGroup($sql,$criteria->group);
			$sql=$this->applyHaving($sql,$criteria->having);
			$sql="SELECT COUNT(*) FROM ($sql) sq";
		}
		else
		{
			if(is_string($criteria->select) && stripos($criteria->select,'count')===0)
				$sql="SELECT ".$criteria->select;
			elseif($criteria->distinct)
			{
				if(is_array($table->primaryKey))
				{
					$pk=array();
					foreach($table->primaryKey as $key)
						$pk[]=$alias.'.'.$key;
					$pk=implode(', ',$pk);
				}
				else
					$pk=$alias.'.'.$table->primaryKey;
				$sql="SELECT COUNT(DISTINCT $pk)";
			}
			else
				$sql="SELECT COUNT(*)";
			$sql.=" FROM {$table->rawName} $alias";
			$sql=$this->applyJoin($sql,$criteria->join);
			$sql=$this->applyCondition($sql,$criteria->condition);
		}

		// Suppress binding of parameters belonging to the ORDER clause. Issue #1407.
		if($criteria->order && $criteria->params)
		{
			$params1=array();
			preg_match_all('/(:\w+)/',$sql,$params1);
			$params2=array();
			preg_match_all('/(:\w+)/',$this->applyOrder($sql,$criteria->order),$params2);
			foreach(array_diff($params2[0],$params1[0]) as $param)
				unset($criteria->params[$param]);
		}

		// Do the same for SELECT part.
		if($criteria->select && $criteria->params)
		{
			$params1=array();
			preg_match_all('/(:\w+)/',$sql,$params1);
			$params2=array();
			preg_match_all('/(:\w+)/',$sql.' '.(is_array($criteria->select) ? implode(', ',$criteria->select) : $criteria->select),$params2);
			foreach(array_diff($params2[0],$params1[0]) as $param)
				unset($criteria->params[$param]);
		}

		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}

	/**
	 * Creates a DELETE command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand delete command.
	 */
	public function createDeleteCommand($table,$criteria)
	{
		$this->ensureTable($table);
		$sql="DELETE FROM {$table->rawName}";
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyGroup($sql,$criteria->group);
		$sql=$this->applyHaving($sql,$criteria->having);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);
		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}

	/**
	 * Creates an INSERT command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $data data to be inserted (column name=>column value). If a key is not a valid column name, the corresponding value will be ignored.
	 * @return CDbCommand insert command
	 */
	public function createInsertCommand($table,$data)
	{
		$this->ensureTable($table);
		$fields=array();
		$values=array();
		$placeholders=array();
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null && ($value!==null || $column->allowNull))
			{
				$fields[]=$column->rawName;
				if($value instanceof CDbExpression)
				{
					$placeholders[]=$value->expression;
					foreach($value->params as $n=>$v)
						$values[$n]=$v;
				}
				else
				{
					$placeholders[]=self::PARAM_PREFIX.$i;
					$values[self::PARAM_PREFIX.$i]=$column->typecast($value);
					$i++;
				}
			}
		}
		if($fields===array())
		{
			$pks=is_array($table->primaryKey) ? $table->primaryKey : array($table->primaryKey);
			foreach($pks as $pk)
			{
				$fields[]=$table->getColumn($pk)->rawName;
				$placeholders[]='NULL';
			}
		}
		$sql="INSERT INTO {$table->rawName} (".implode(', ',$fields).') VALUES ('.implode(', ',$placeholders).')';
		$command=$this->_connection->createCommand($sql);

		foreach($values as $name=>$value)
			$command->bindValue($name,$value);

		return $command;
	}

	/**
	 * Creates an UPDATE command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $data list of columns to be updated (name=>value)
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand update command.
	 */
	public function createUpdateCommand($table,$data,$criteria)
	{
		$this->ensureTable($table);
		$fields=array();
		$values=array();
		$bindByPosition=isset($criteria->params[0]);
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null)
			{
				if($value instanceof CDbExpression)
				{
					$fields[]=$column->rawName.'='.$value->expression;
					foreach($value->params as $n=>$v)
						$values[$n]=$v;
				}
				elseif($bindByPosition)
				{
					$fields[]=$column->rawName.'=?';
					$values[]=$column->typecast($value);
				}
				else
				{
					$fields[]=$column->rawName.'='.self::PARAM_PREFIX.$i;
					$values[self::PARAM_PREFIX.$i]=$column->typecast($value);
					$i++;
				}
			}
		}
		if($fields===array())
			throw new CDbException(Yii::t('yii','No columns are being updated for table "{table}".',
				array('{table}'=>$table->name)));
		$sql="UPDATE {$table->rawName} SET ".implode(', ',$fields);
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);

		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,array_merge($values,$criteria->params));

		return $command;
	}

	/**
	 * Creates an UPDATE command that increments/decrements certain columns.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $counters counters to be updated (counter increments/decrements indexed by column names.)
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand the created command
	 * @throws CException if no counter is specified
	 */
	public function createUpdateCounterCommand($table,$counters,$criteria)
	{
		$this->ensureTable($table);
		$fields=array();
		foreach($counters as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null)
			{
				$value=(float)$value;
				if($value<0)
					$fields[]="{$column->rawName}={$column->rawName}-".(-$value);
				else
					$fields[]="{$column->rawName}={$column->rawName}+".$value;
			}
		}
		if($fields!==array())
		{
			$sql="UPDATE {$table->rawName} SET ".implode(', ',$fields);
			$sql=$this->applyJoin($sql,$criteria->join);
			$sql=$this->applyCondition($sql,$criteria->condition);
			$sql=$this->applyOrder($sql,$criteria->order);
			$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);
			$command=$this->_connection->createCommand($sql);
			$this->bindValues($command,$criteria->params);
			return $command;
		}
		else
			throw new CDbException(Yii::t('yii','No counter columns are being updated for table "{table}".',
				array('{table}'=>$table->name)));
	}

	/**
	 * Creates a command based on a given SQL statement.
	 * @param string $sql the explicitly specified SQL statement
	 * @param array $params parameters that will be bound to the SQL statement
	 * @return CDbCommand the created command
	 */
	public function createSqlCommand($sql,$params=array())
	{
		$command=$this->_connection->createCommand($sql);
		$this->bindValues($command,$params);
		return $command;
	}

	/**
	 * Alters the SQL to apply JOIN clause.
	 * @param string $sql the SQL statement to be altered
	 * @param string $join the JOIN clause (starting with join type, such as INNER JOIN)
	 * @return string the altered SQL statement
	 */
	public function applyJoin($sql,$join)
	{
		if($join!='')
			return $sql.' '.$join;
		else
			return $sql;
	}

	/**
	 * Alters the SQL to apply WHERE clause.
	 * @param string $sql the SQL statement without WHERE clause
	 * @param string $condition the WHERE clause (without WHERE keyword)
	 * @return string the altered SQL statement
	 */
	public function applyCondition($sql,$condition)
	{
		if($condition!='')
			return $sql.' WHERE '.$condition;
		else
			return $sql;
	}

	/**
	 * Alters the SQL to apply ORDER BY.
	 * @param string $sql SQL statement without ORDER BY.
	 * @param string $orderBy column ordering
	 * @return string modified SQL applied with ORDER BY.
	 */
	public function applyOrder($sql,$orderBy)
	{
		if($orderBy!='')
			return $sql.' ORDER BY '.$orderBy;
		else
			return $sql;
	}

	/**
	 * Alters the SQL to apply LIMIT and OFFSET.
	 * Default implementation is applicable for PostgreSQL, MySQL and SQLite.
	 * @param string $sql SQL query string without LIMIT and OFFSET.
	 * @param integer $limit maximum number of rows, -1 to ignore limit.
	 * @param integer $offset row offset, -1 to ignore offset.
	 * @return string SQL with LIMIT and OFFSET
	 */
	public function applyLimit($sql,$limit,$offset)
	{
		if($limit>=0)
			$sql.=' LIMIT '.(int)$limit;
		if($offset>0)
			$sql.=' OFFSET '.(int)$offset;
		return $sql;
	}

	/**
	 * Alters the SQL to apply GROUP BY.
	 * @param string $sql SQL query string without GROUP BY.
	 * @param string $group GROUP BY
	 * @return string SQL with GROUP BY.
	 */
	public function applyGroup($sql,$group)
	{
		if($group!='')
			return $sql.' GROUP BY '.$group;
		else
			return $sql;
	}

	/**
	 * Alters the SQL to apply HAVING.
	 * @param string $sql SQL query string without HAVING
	 * @param string $having HAVING
	 * @return string SQL with HAVING
	 */
	public function applyHaving($sql,$having)
	{
		if($having!='')
			return $sql.' HAVING '.$having;
		else
			return $sql;
	}

	/**
	 * Binds parameter values for an SQL command.
	 * @param CDbCommand $command database command
	 * @param array $values values for binding (integer-indexed array for question mark placeholders, string-indexed array for named placeholders)
	 */
	public function bindValues($command, $values)
	{
		if(($n=count($values))===0)
			return;
		if(isset($values[0])) // question mark placeholders
		{
			for($i=0;$i<$n;++$i)
				$command->bindValue($i+1,$values[$i]);
		}
		else // named placeholders
		{
			foreach($values as $name=>$value)
			{
				if($name[0]!==':')
					$name=':'.$name;
				$command->bindValue($name,$value);
			}
		}
	}

	/**
	 * Creates a query criteria.
	 * @param mixed $condition query condition or criteria.
	 * If a string, it is treated as query condition (the WHERE clause);
	 * If an array, it is treated as the initial values for constructing a {@link CDbCriteria} object;
	 * Otherwise, it should be an instance of {@link CDbCriteria}.
	 * @param array $params parameters to be bound to an SQL statement.
	 * This is only used when the first parameter is a string (query condition).
	 * In other cases, please use {@link CDbCriteria::params} to set parameters.
	 * @return CDbCriteria the created query criteria
	 * @throws CException if the condition is not string, array and CDbCriteria
	 */
	public function createCriteria($condition='',$params=array())
	{
		if(is_array($condition))
			$criteria=new CDbCriteria($condition);
		elseif($condition instanceof CDbCriteria)
			$criteria=clone $condition;
		else
		{
			$criteria=new CDbCriteria;
			$criteria->condition=$condition;
			$criteria->params=$params;
		}
		return $criteria;
	}

	/**
	 * Creates a query criteria with the specified primary key.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param mixed $pk primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
	 * @param mixed $condition query condition or criteria.
	 * If a string, it is treated as query condition;
	 * If an array, it is treated as the initial values for constructing a {@link CDbCriteria};
	 * Otherwise, it should be an instance of {@link CDbCriteria}.
	 * @param array $params parameters to be bound to an SQL statement.
	 * This is only used when the second parameter is a string (query condition).
	 * In other cases, please use {@link CDbCriteria::params} to set parameters.
	 * @param string $prefix column prefix (ended with dot). If null, it will be the table name
	 * @return CDbCriteria the created query criteria
	 */
	public function createPkCriteria($table,$pk,$condition='',$params=array(),$prefix=null)
	{
		$this->ensureTable($table);
		$criteria=$this->createCriteria($condition,$params);
		if($criteria->alias!='')
			$prefix=$this->_schema->quoteTableName($criteria->alias).'.';
		if(!is_array($pk)) // single key
			$pk=array($pk);
		if(is_array($table->primaryKey) && !isset($pk[0]) && $pk!==array()) // single composite key
			$pk=array($pk);
		$condition=$this->createInCondition($table,$table->primaryKey,$pk,$prefix);
		if($criteria->condition!='')
			$criteria->condition=$condition.' AND ('.$criteria->condition.')';
		else
			$criteria->condition=$condition;

		return $criteria;
	}

	/**
	 * Generates the expression for selecting rows of specified primary key values.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $values list of primary key values to be selected within
	 * @param string $prefix column prefix (ended with dot). If null, it will be the table name
	 * @return string the expression for selection
	 */
	public function createPkCondition($table,$values,$prefix=null)
	{
		$this->ensureTable($table);
		return $this->createInCondition($table,$table->primaryKey,$values,$prefix);
	}

	/**
	 * Creates a query criteria with the specified column values.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $columns column values that should be matched in the query (name=>value)
	 * @param mixed $condition query condition or criteria.
	 * If a string, it is treated as query condition;
	 * If an array, it is treated as the initial values for constructing a {@link CDbCriteria};
	 * Otherwise, it should be an instance of {@link CDbCriteria}.
	 * @param array $params parameters to be bound to an SQL statement.
	 * This is only used when the third parameter is a string (query condition).
	 * In other cases, please use {@link CDbCriteria::params} to set parameters.
	 * @param string $prefix column prefix (ended with dot). If null, it will be the table name
	 * @return CDbCriteria the created query criteria
	 */
	public function createColumnCriteria($table,$columns,$condition='',$params=array(),$prefix=null)
	{
		$this->ensureTable($table);
		$criteria=$this->createCriteria($condition,$params);
		if($criteria->alias!='')
			$prefix=$this->_schema->quoteTableName($criteria->alias).'.';
		$bindByPosition=isset($criteria->params[0]);
		$conditions=array();
		$values=array();
		$i=0;
		if($prefix===null)
			$prefix=$table->rawName.'.';
		foreach($columns as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null)
			{
				if(is_array($value))
					$conditions[]=$this->createInCondition($table,$name,$value,$prefix);
				elseif($value!==null)
				{
					if($bindByPosition)
					{
						$conditions[]=$prefix.$column->rawName.'=?';
						$values[]=$value;
					}
					else
					{
						$conditions[]=$prefix.$column->rawName.'='.self::PARAM_PREFIX.$i;
						$values[self::PARAM_PREFIX.$i]=$value;
						$i++;
					}
				}
				else
					$conditions[]=$prefix.$column->rawName.' IS NULL';
			}
			else
				throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
					array('{table}'=>$table->name,'{column}'=>$name)));
		}
		$criteria->params=array_merge($values,$criteria->params);
		if(isset($conditions[0]))
		{
			if($criteria->condition!='')
				$criteria->condition=implode(' AND ',$conditions).' AND ('.$criteria->condition.')';
			else
				$criteria->condition=implode(' AND ',$conditions);
		}
		return $criteria;
	}

	/**
	 * Generates the expression for searching the specified keywords within a list of columns.
	 * The search expression is generated using the 'LIKE' SQL syntax.
	 * Every word in the keywords must be present and appear in at least one of the columns.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array $columns list of column names for potential search condition.
	 * @param mixed $keywords search keywords. This can be either a string with space-separated keywords or an array of keywords.
	 * @param string $prefix optional column prefix (with dot at the end). If null, the table name will be used as the prefix.
	 * @param boolean $caseSensitive whether the search is case-sensitive. Defaults to true.
	 * @return string SQL search condition matching on a set of columns. An empty string is returned
	 * if either the column array or the keywords are empty.
	 */
	public function createSearchCondition($table,$columns,$keywords,$prefix=null,$caseSensitive=true)
	{
		$this->ensureTable($table);
		if(!is_array($keywords))
			$keywords=preg_split('/\s+/u',$keywords,-1,PREG_SPLIT_NO_EMPTY);
		if(empty($keywords))
			return '';
		if($prefix===null)
			$prefix=$table->rawName.'.';
		$conditions=array();
		foreach($columns as $name)
		{
			if(($column=$table->getColumn($name))===null)
				throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
					array('{table}'=>$table->name,'{column}'=>$name)));
			$condition=array();
			foreach($keywords as $keyword)
			{
				$keyword='%'.strtr($keyword,array('%'=>'\%', '_'=>'\_')).'%';
				if($caseSensitive)
					$condition[]=$prefix.$column->rawName.' LIKE '.$this->_connection->quoteValue('%'.$keyword.'%');
				else
					$condition[]='LOWER('.$prefix.$column->rawName.') LIKE LOWER('.$this->_connection->quoteValue('%'.$keyword.'%').')';
			}
			$conditions[]=implode(' AND ',$condition);
		}
		return '('.implode(' OR ',$conditions).')';
	}

	/**
	 * Generates the expression for selecting rows of specified primary key values.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param mixed $columnName the column name(s). It can be either a string indicating a single column
	 * or an array of column names. If the latter, it stands for a composite key.
	 * @param array $values list of key values to be selected within
	 * @param string $prefix column prefix (ended with dot). If null, it will be the table name
	 * @return string the expression for selection
	 */
	public function createInCondition($table,$columnName,$values,$prefix=null)
	{
		if(($n=count($values))<1)
			return '0=1';

		$this->ensureTable($table);

		if($prefix===null)
			$prefix=$table->rawName.'.';

		$db=$this->_connection;

		if(is_array($columnName) && count($columnName)===1)
			$columnName=reset($columnName);

		if(is_string($columnName)) // simple key
		{
			if(!isset($table->columns[$columnName]))
				throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
				array('{table}'=>$table->name, '{column}'=>$columnName)));
			$column=$table->columns[$columnName];

			foreach($values as &$value)
			{
				$value=$column->typecast($value);
				if(is_string($value))
					$value=$db->quoteValue($value);
			}
			if($n===1)
				return $prefix.$column->rawName.($values[0]===null?' IS NULL':'='.$values[0]);
			else
				return $prefix.$column->rawName.' IN ('.implode(', ',$values).')';
		}
		elseif(is_array($columnName)) // composite key: $values=array(array('pk1'=>'v1','pk2'=>'v2'),array(...))
		{
			foreach($columnName as $name)
			{
				if(!isset($table->columns[$name]))
					throw new CDbException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
					array('{table}'=>$table->name, '{column}'=>$name)));

				for($i=0;$i<$n;++$i)
				{
					if(isset($values[$i][$name]))
					{
						$value=$table->columns[$name]->typecast($values[$i][$name]);
						if(is_string($value))
							$values[$i][$name]=$db->quoteValue($value);
						else
							$values[$i][$name]=$value;
					}
					else
						throw new CDbException(Yii::t('yii','The value for the column "{column}" is not supplied when querying the table "{table}".',
							array('{table}'=>$table->name,'{column}'=>$name)));
				}
			}
			if(count($values)===1)
			{
				$entries=array();
				foreach($values[0] as $name=>$value)
					$entries[]=$prefix.$table->columns[$name]->rawName.($value===null?' IS NULL':'='.$value);
				return implode(' AND ',$entries);
			}

			return $this->createCompositeInCondition($table,$values,$prefix);
		}
		else
			throw new CDbException(Yii::t('yii','Column name must be either a string or an array.'));
	}

	/**
	 * Generates the expression for selecting rows with specified composite key values.
	 * @param CDbTableSchema $table the table schema
	 * @param array $values list of primary key values to be selected within
	 * @param string $prefix column prefix (ended with dot)
	 * @return string the expression for selection
	 */
	protected function createCompositeInCondition($table,$values,$prefix)
	{
		$keyNames=array();
		foreach(array_keys($values[0]) as $name)
			$keyNames[]=$prefix.$table->columns[$name]->rawName;
		$vs=array();
		foreach($values as $value)
			$vs[]='('.implode(', ',$value).')';
		return '('.implode(', ',$keyNames).') IN ('.implode(', ',$vs).')';
	}

	/**
	 * Checks if the parameter is a valid table schema.
	 * If it is a string, the corresponding table schema will be retrieved.
	 * @param mixed $table table schema ({@link CDbTableSchema}) or table name (string).
	 * If this refers to a valid table name, this parameter will be returned with the corresponding table schema.
	 * @throws CDbException if the table name is not valid
	 */
	protected function ensureTable(&$table)
	{
		if(is_string($table) && ($table=$this->_schema->getTable($tableName=$table))===null)
			throw new CDbException(Yii::t('yii','Table "{table}" does not exist.',
				array('{table}'=>$tableName)));
	}
}