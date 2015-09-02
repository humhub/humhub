<?php
/**
 * CMysqlCommandBuilder class file.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMysqlCommandBuilder provides basic methods to create query commands for tables.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @package system.db.schema.mysql
 * @since 1.1.13
 */
class CMysqlCommandBuilder extends CDbCommandBuilder
{
	/**
	 * Alters the SQL to apply JOIN clause.
	 * This method handles the mysql specific syntax where JOIN has to come before SET in UPDATE statement
	 * and for DELETE where JOIN has to be after FROM part.
	 * @param string $sql the SQL statement to be altered
	 * @param string $join the JOIN clause (starting with join type, such as INNER JOIN)
	 * @return string the altered SQL statement
	 */
	public function applyJoin($sql,$join)
	{
		if($join=='')
			return $sql;

		if(strpos($sql,'UPDATE')===0 && ($pos=strpos($sql,'SET'))!==false)
			return substr($sql,0,$pos).$join.' '.substr($sql,$pos);
		elseif(strpos($sql,'DELETE FROM ')===0)
		{
			$tableName=substr($sql,12);
			return "DELETE {$tableName} FROM {$tableName} ".$join;
		}
		else
			return $sql.' '.$join;
	}
}
