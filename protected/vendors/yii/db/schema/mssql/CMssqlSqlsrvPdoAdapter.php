<?php
/**
 * CMssqlSqlsrvPdoAdapter class file.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * This is an extension of default PDO class for MSSQL SQLSRV driver only.
 * It provides workaround of the improperly implemented functionalities of PDO SQLSRV driver.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @package system.db.schema.mssql
 * @since 1.1.13
 */
class CMssqlSqlsrvPdoAdapter extends PDO
{
	/**
	 * Returns last inserted ID value.
	 * SQLSRV driver supports PDO::lastInsertId() with one peculiarity: when $sequence's value is null or empty
	 * string it returns empty string. But when parameter is not specified at all it's working as expected
	 * and returns actual last inserted ID (like other PDO drivers).
	 *
	 * @param string|null the sequence name. Defaults to null.
	 * @return integer last inserted ID value.
	 */
	public function lastInsertId($sequence=null)
	{
		if(!$sequence)
			return parent::lastInsertId();
		return parent::lastInsertId($sequence);
	}
}
