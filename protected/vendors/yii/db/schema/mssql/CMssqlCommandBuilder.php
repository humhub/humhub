<?php
/**
 * CMsCommandBuilder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMssqlCommandBuilder provides basic methods to create query commands for tables for Mssql Servers.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @package system.db.schema.mssql
 */
class CMssqlCommandBuilder extends CDbCommandBuilder
{
	/**
	 * Creates a COUNT(*) command for a single table.
	 * Override parent implementation to remove the order clause of criteria if it exists
	 * @param CDbTableSchema $table the table metadata
	 * @param CDbCriteria $criteria the query criteria
	 * @param string $alias the alias name of the primary table. Defaults to 't'.
	 * @return CDbCommand query command.
	 */
	public function createCountCommand($table,$criteria,$alias='t')
	{
		$criteria->order='';
		return parent::createCountCommand($table, $criteria,$alias);
	}

	/**
	 * Creates a SELECT command for a single table.
	 * Override parent implementation to check if an orderby clause if specified when querying with an offset
	 * @param CDbTableSchema $table the table metadata
	 * @param CDbCriteria $criteria the query criteria
	 * @param string $alias the alias name of the primary table. Defaults to 't'.
	 * @return CDbCommand query command.
	 */
	public function createFindCommand($table,$criteria,$alias='t')
	{
		$criteria=$this->checkCriteria($table,$criteria);
		return parent::createFindCommand($table,$criteria,$alias);

	}

	/**
	 * Creates an UPDATE command.
	 * Override parent implementation because mssql don't want to update an identity column
	 * @param CDbTableSchema $table the table metadata
	 * @param array $data list of columns to be updated (name=>value)
	 * @param CDbCriteria $criteria the query criteria
	 * @throws CDbException if no columns are being updated
	 * @return CDbCommand update command.
	 */
	public function createUpdateCommand($table,$data,$criteria)
	{
		$criteria=$this->checkCriteria($table,$criteria);
		$fields=array();
		$values=array();
		$bindByPosition=isset($criteria->params[0]);
		$i=0;
		foreach($data as $name=>$value)
		{
			if(($column=$table->getColumn($name))!==null)
			{
				if ($table->sequenceName !== null && $column->isPrimaryKey === true) continue;
				if ($column->dbType === 'timestamp') continue;
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

		$command=$this->getDbConnection()->createCommand($sql);
		$this->bindValues($command,array_merge($values,$criteria->params));

		return $command;
	}

	/**
	 * Creates a DELETE command.
	 * Override parent implementation to check if an orderby clause if specified when querying with an offset
	 * @param CDbTableSchema $table the table metadata
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand delete command.
	 */
	public function createDeleteCommand($table,$criteria)
	{
		$criteria=$this->checkCriteria($table, $criteria);
		return parent::createDeleteCommand($table, $criteria);
	}

	/**
	 * Creates an UPDATE command that increments/decrements certain columns.
	 * Override parent implementation to check if an orderby clause if specified when querying with an offset
	 * @param CDbTableSchema $table the table metadata
	 * @param CDbCriteria $counters the query criteria
	 * @param array $criteria counters to be updated (counter increments/decrements indexed by column names.)
	 * @return CDbCommand the created command
	 * @throws CException if no counter is specified
	 */
	public function createUpdateCounterCommand($table,$counters,$criteria)
	{
		$criteria=$this->checkCriteria($table, $criteria);
		return parent::createUpdateCounterCommand($table, $counters, $criteria);
	}

	/**
	 * This is a port from Prado Framework.
	 *
	 * Overrides parent implementation. Alters the sql to apply $limit and $offset.
	 * The idea for limit with offset is done by modifying the sql on the fly
	 * with numerous assumptions on the structure of the sql string.
	 * The modification is done with reference to the notes from
	 * http://troels.arvin.dk/db/rdbms/#select-limit-offset
	 *
	 * <code>
	 * SELECT * FROM (
	 *  SELECT TOP n * FROM (
	 *    SELECT TOP z columns      -- (z=n+skip)
	 *    FROM tablename
	 *    ORDER BY key ASC
	 *  ) AS FOO ORDER BY key DESC -- ('FOO' may be anything)
	 * ) AS BAR ORDER BY key ASC    -- ('BAR' may be anything)
	 * </code>
	 *
	 * <b>Regular expressions are used to alter the SQL query. The resulting SQL query
	 * may be malformed for complex queries.</b> The following restrictions apply
	 *
	 * <ul>
	 *   <li>
	 * In particular, <b>commas</b> should <b>NOT</b>
	 * be used as part of the ordering expression or identifier. Commas must only be
	 * used for separating the ordering clauses.
	 *   </li>
	 *   <li>
	 * In the ORDER BY clause, the column name should NOT be be qualified
	 * with a table name or view name. Alias the column names or use column index.
	 *   </li>
	 *   <li>
	 * No clauses should follow the ORDER BY clause, e.g. no COMPUTE or FOR clauses.
	 *   </li>
	 * </ul>
	 *
	 * @param string $sql SQL query string.
	 * @param integer $limit maximum number of rows, -1 to ignore limit.
	 * @param integer $offset row offset, -1 to ignore offset.
	 * @return string SQL with limit and offset.
	 *
	 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
	 */
	public function applyLimit($sql, $limit, $offset)
	{
		$limit = $limit!==null ? (int)$limit : -1;
		$offset = $offset!==null ? (int)$offset : -1;
		if ($limit > 0 && $offset <= 0) //just limit
			$sql = preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i',"\\1SELECT\\2 TOP $limit", $sql);
		elseif($limit > 0 && $offset > 0)
			$sql = $this->rewriteLimitOffsetSql($sql, $limit,$offset);
		return $sql;
	}

	/**
	 * Rewrite sql to apply $limit > and $offset > 0 for MSSQL database.
	 * See http://troels.arvin.dk/db/rdbms/#select-limit-offset
	 * @param string $sql sql query
	 * @param integer $limit $limit > 0
	 * @param integer $offset $offset > 0
	 * @return string modified sql query applied with limit and offset.
	 *
	 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
	 */
	protected function rewriteLimitOffsetSql($sql, $limit, $offset)
	{
		$fetch = $limit+$offset;
		$sql = preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i',"\\1SELECT\\2 TOP $fetch", $sql);
		$ordering = $this->findOrdering($sql);
		$orginalOrdering = $this->joinOrdering($ordering, '[__outer__]');
		$reverseOrdering = $this->joinOrdering($this->reverseDirection($ordering), '[__inner__]');
		$sql = "SELECT * FROM (SELECT TOP {$limit} * FROM ($sql) as [__inner__] {$reverseOrdering}) as [__outer__] {$orginalOrdering}";
		return $sql;
	}

	/**
	 * Base on simplified syntax http://msdn2.microsoft.com/en-us/library/aa259187(SQL.80).aspx
	 *
	 * @param string $sql $sql
	 * @return array ordering expression as key and ordering direction as value
	 *
	 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
	 */
	protected function findOrdering($sql)
	{
		if(!preg_match('/ORDER BY/i', $sql))
			return array();
		$matches=array();
		$ordering=array();
		preg_match_all('/(ORDER BY)[\s"\[](.*)(ASC|DESC)?(?:[\s"\[]|$|COMPUTE|FOR)/i', $sql, $matches);
		if(count($matches)>1 && count($matches[2]) > 0)
		{
			$parts = explode(',', $matches[2][0]);
			foreach($parts as $part)
			{
				$subs=array();
				if(preg_match_all('/(.*)[\s"\]](ASC|DESC)$/i', trim($part), $subs))
				{
					if(count($subs) > 1 && count($subs[2]) > 0)
					{
						$name='';
						foreach(explode('.', $subs[1][0]) as $p)
						{
							if($name!=='')
								$name.='.';
							$name.='[' . trim($p, '[]') . ']';
						}
						$ordering[$name] = $subs[2][0];
					}
					//else what?
				}
				else
					$ordering[trim($part)] = 'ASC';
			}
		}

		// replacing column names with their alias names
		foreach($ordering as $name => $direction)
		{
			$matches = array();
			$pattern = '/\s+'.str_replace(array('[',']'), array('\[','\]'), $name).'\s+AS\s+(\[[^\]]+\])/i';
			preg_match($pattern, $sql, $matches);
			if(isset($matches[1]))
			{
				$ordering[$matches[1]] = $ordering[$name];
				unset($ordering[$name]);
			}
		}

		return $ordering;
	}

	/**
	 * @param array $orders ordering obtained from findOrdering()
	 * @param string $newPrefix new table prefix to the ordering columns
	 * @return string concat the orderings
	 *
	 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
	 */
	protected function joinOrdering($orders, $newPrefix)
	{
		if(count($orders)>0)
		{
			$str=array();
			foreach($orders as $column => $direction)
				$str[] = $column.' '.$direction;
			$orderBy = 'ORDER BY '.implode(', ', $str);
			return preg_replace('/\s+\[[^\]]+\]\.(\[[^\]]+\])/i', ' '.$newPrefix.'.\1', $orderBy);
		}
	}

	/**
	 * @param array $orders original ordering
	 * @return array ordering with reversed direction.
	 *
	 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
	 */
	protected function reverseDirection($orders)
	{
		foreach($orders as $column => $direction)
			$orders[$column] = strtolower(trim($direction))==='desc' ? 'ASC' : 'DESC';
		return $orders;
	}


	/**
	 * Checks if the criteria has an order by clause when using offset/limit.
	 * Override parent implementation to check if an orderby clause if specified when querying with an offset
	 * If not, order it by pk.
	 * @param CMssqlTableSchema $table table schema
	 * @param CDbCriteria $criteria criteria
	 * @return CDbCriteria the modified criteria
	 */
	protected function checkCriteria($table, $criteria)
	{
		if ($criteria->offset > 0 && $criteria->order==='')
		{
			$criteria->order=is_array($table->primaryKey)?implode(',',$table->primaryKey):$table->primaryKey;
		}
		return $criteria;
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
		$vs=array();
		foreach($values as $value)
		{
			$c=array();
			foreach($value as $k=>$v)
				$c[]=$prefix.$table->columns[$k]->rawName.'='.$v;
			$vs[]='('.implode(' AND ',$c).')';
		}
		return '('.implode(' OR ',$vs).')';
	}
}
