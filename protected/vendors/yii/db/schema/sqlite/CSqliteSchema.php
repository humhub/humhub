<?php
/**
 * CSqliteSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSqliteSchema is the class for retrieving metadata information from a SQLite (2/3) database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.schema.sqlite
 * @since 1.0
 */
class CSqliteSchema extends CDbSchema
{
	/**
	 * @var array the abstract column types mapped to physical column types.
	 * @since 1.1.6
	 */
    public $columnTypes=array(
        'pk' => 'integer PRIMARY KEY AUTOINCREMENT NOT NULL',
        'string' => 'varchar(255)',
        'text' => 'text',
        'integer' => 'integer',
        'float' => 'float',
        'decimal' => 'decimal',
        'datetime' => 'datetime',
        'timestamp' => 'timestamp',
        'time' => 'time',
        'date' => 'date',
        'binary' => 'blob',
        'boolean' => 'tinyint(1)',
		'money' => 'decimal(19,4)',
	);

	/**
	 * Resets the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param CDbTableSchema $table the table schema whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 * @since 1.1
	 */
	public function resetSequence($table,$value=null)
	{
		if($table->sequenceName!==null)
		{
			if($value===null)
				$value=$this->getDbConnection()->createCommand("SELECT MAX(`{$table->primaryKey}`) FROM {$table->rawName}")->queryScalar();
			else
				$value=(int)$value-1;
			try
			{
				// it's possible sqlite_sequence does not exist
				$this->getDbConnection()->createCommand("UPDATE sqlite_sequence SET seq='$value' WHERE name='{$table->name}'")->execute();
			}
			catch(Exception $e)
			{
			}
		}
	}

	/**
	 * Enables or disables integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @since 1.1
	 */
	public function checkIntegrity($check=true,$schema='')
	{
		// SQLite doesn't enforce integrity
		return;
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. This is not used for sqlite database.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema='')
	{
		$sql="SELECT DISTINCT tbl_name FROM sqlite_master WHERE tbl_name<>'sqlite_sequence'";
		return $this->getDbConnection()->createCommand($sql)->queryColumn();
	}

	/**
	 * Creates a command builder for the database.
	 * @return CSqliteCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new CSqliteCommandBuilder($this);
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return CDbTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTable($name)
	{
		$table=new CDbTableSchema;
		$table->name=$name;
		$table->rawName=$this->quoteTableName($name);

		if($this->findColumns($table))
		{
			$this->findConstraints($table);
			return $table;
		}
		else
			return null;
	}

	/**
	 * Collects the table column metadata.
	 * @param CDbTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql="PRAGMA table_info({$table->rawName})";
		$columns=$this->getDbConnection()->createCommand($sql)->queryAll();
		if(empty($columns))
			return false;

		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			$table->columns[$c->name]=$c;
			if($c->isPrimaryKey)
			{
				if($table->primaryKey===null)
					$table->primaryKey=$c->name;
				elseif(is_string($table->primaryKey))
					$table->primaryKey=array($table->primaryKey,$c->name);
				else
					$table->primaryKey[]=$c->name;
			}
		}
		if(is_string($table->primaryKey) && !strncasecmp($table->columns[$table->primaryKey]->dbType,'int',3))
		{
			$table->sequenceName='';
			$table->columns[$table->primaryKey]->autoIncrement=true;
		}

		return true;
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param CDbTableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$foreignKeys=array();
		$sql="PRAGMA foreign_key_list({$table->rawName})";
		$keys=$this->getDbConnection()->createCommand($sql)->queryAll();
		foreach($keys as $key)
		{
			$column=$table->columns[$key['from']];
			$column->isForeignKey=true;
			$foreignKeys[$key['from']]=array($key['table'],$key['to']);
		}
		$table->foreignKeys=$foreignKeys;
	}

	/**
	 * Creates a table column.
	 * @param array $column column metadata
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c=new CSqliteColumnSchema;
		$c->name=$column['name'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=!$column['notnull'];
		$c->isPrimaryKey=$column['pk']!=0;
		$c->isForeignKey=false;
		$c->comment=null; // SQLite does not support column comments at all

		$c->init(strtolower($column['type']),$column['dflt_value']);
		return $c;
	}

	/**
	 * Builds a SQL statement for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB table.
	 * @since 1.1.13
	 */
	public function renameTable($table, $newName)
	{
		return 'ALTER TABLE ' . $this->quoteTableName($table) . ' RENAME TO ' . $this->quoteTableName($newName);
	}

	/**
	 * Builds a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 * @return string the SQL statement for truncating a DB table.
	 * @since 1.1.6
	 */
	public function truncateTable($table)
	{
		return "DELETE FROM ".$this->quoteTableName($table);
	}

	/**
	 * Builds a SQL statement for dropping a DB column.
	 * Because SQLite does not support dropping a DB column, calling this method will throw an exception.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a DB column.
	 * @since 1.1.6
	 */
	public function dropColumn($table, $column)
	{
		throw new CDbException(Yii::t('yii', 'Dropping DB column is not supported by SQLite.'));
	}

	/**
	 * Builds a SQL statement for renaming a column.
	 * Because SQLite does not support renaming a DB column, calling this method will throw an exception.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @since 1.1.6
	 */
	public function renameColumn($table, $name, $newName)
	{
		throw new CDbException(Yii::t('yii', 'Renaming a DB column is not supported by SQLite.'));
	}

	/**
	 * Builds a SQL statement for adding a foreign key constraint to an existing table.
	 * Because SQLite does not support adding foreign key to an existing table, calling this method will throw an exception.
	 * @param string $name the name of the foreign key constraint.
	 * @param string $table the table that the foreign key constraint will be added to.
	 * @param string $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
	 * @param string $refTable the table that the foreign key references to.
	 * @param string $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
	 * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @return string the SQL statement for adding a foreign key constraint to an existing table.
	 * @since 1.1.6
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete=null, $update=null)
	{
		throw new CDbException(Yii::t('yii', 'Adding a foreign key constraint to an existing table is not supported by SQLite.'));
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * Because SQLite does not support dropping a foreign key constraint, calling this method will throw an exception.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 * @since 1.1.6
	 */
	public function dropForeignKey($name, $table)
	{
		throw new CDbException(Yii::t('yii', 'Dropping a foreign key constraint is not supported by SQLite.'));
	}

	/**
	 * Builds a SQL statement for changing the definition of a column.
	 * Because SQLite does not support altering a DB column, calling this method will throw an exception.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 * @return string the SQL statement for changing the definition of a column.
	 * @since 1.1.6
	 */
	public function alterColumn($table, $column, $type)
	{
		throw new CDbException(Yii::t('yii', 'Altering a DB column is not supported by SQLite.'));
	}

	/**
	 * Builds a SQL statement for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping an index.
	 * @since 1.1.6
	 */
	public function dropIndex($name, $table)
	{
		return 'DROP INDEX '.$this->quoteTableName($name);
	}

	/**
	 * Builds a SQL statement for adding a primary key constraint to an existing table.
	 * Because SQLite does not support adding a primary key on an existing table this method will throw an exception
	 * @param string $name the name of the primary key constraint.
	 * @param string $table the table that the primary key constraint will be added to.
	 * @param string $columns the name of the column to that the constraint will be added on.
	 * @return string the SQL statement for adding a primary key constraint to an existing table.
	 * @since 1.1.13
	 */
	public function addPrimaryKey($name,$table,$columns)
	{
		throw new CDbException(Yii::t('yii', 'Adding a primary key after table has been created is not supported by SQLite.'));
	}


	/**
	 * Builds a SQL statement for removing a primary key constraint to an existing table.
	 * Because SQLite does not support dropping a primary key from an existing table this method will throw an exception
	 * @param string $name the name of the primary key constraint to be removed.
	 * @param string $table the table that the primary key constraint will be removed from.
	 * @return string the SQL statement for removing a primary key constraint from an existing table.
	 * @since 1.1.13
	 */
	public function dropPrimaryKey($name,$table)
	{
		throw new CDbException(Yii::t('yii', 'Removing a primary key after table has been created is not supported by SQLite.'));

	}
}
