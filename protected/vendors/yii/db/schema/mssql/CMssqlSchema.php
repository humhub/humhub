<?php
/**
 * CMssqlSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMssqlSchema is the class for retrieving metadata information from a MS SQL Server database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @package system.db.schema.mssql
 */
class CMssqlSchema extends CDbSchema
{
	const DEFAULT_SCHEMA='dbo';

	/**
	 * @var array the abstract column types mapped to physical column types.
	 * @since 1.1.6
	 */
    public $columnTypes=array(
        'pk' => 'int IDENTITY PRIMARY KEY',
        'string' => 'varchar(255)',
        'text' => 'text',
        'integer' => 'int',
        'float' => 'float',
        'decimal' => 'decimal',
        'datetime' => 'datetime',
        'timestamp' => 'timestamp',
        'time' => 'time',
        'date' => 'date',
        'binary' => 'binary',
        'boolean' => 'bit',
    );

	/**
	 * Quotes a table name for use in a query.
	 * A simple table name does not schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 * @since 1.1.6
	 */
	public function quoteSimpleTableName($name)
	{
		return '['.$name.']';
	}

	/**
	 * Quotes a column name for use in a query.
	 * A simple column name does not contain prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 * @since 1.1.6
	 */
	public function quoteSimpleColumnName($name)
	{
		return '['.$name.']';
	}

	/**
	 * Compares two table names.
	 * The table names can be either quoted or unquoted. This method
	 * will consider both cases.
	 * @param string $name1 table name 1
	 * @param string $name2 table name 2
	 * @return boolean whether the two table names refer to the same table.
	 */
	public function compareTableNames($name1,$name2)
	{
		$name1=str_replace(array('[',']'),'',$name1);
		$name2=str_replace(array('[',']'),'',$name2);
		return parent::compareTableNames(strtolower($name1),strtolower($name2));
	}

	/**
	 * Resets the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param CDbTableSchema $table the table schema whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 * @since 1.1.6
	 */
	public function resetSequence($table,$value=null)
	{
		if($table->sequenceName!==null)
		{
			$db=$this->getDbConnection();
			if($value===null)
				$value=$db->createCommand("SELECT MAX(`{$table->primaryKey}`) FROM {$table->rawName}")->queryScalar();
			$value=(int)$value;
			$name=strtr($table->rawName,array('['=>'',']'=>''));
			$db->createCommand("DBCC CHECKIDENT ('$name', RESEED, $value)")->execute();
		}
	}

	private $_normalTables=array();  // non-view tables
	/**
	 * Enables or disables integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @since 1.1.6
	 */
	public function checkIntegrity($check=true,$schema='')
	{
		$enable=$check ? 'CHECK' : 'NOCHECK';
		if(!isset($this->_normalTables[$schema]))
			$this->_normalTables[$schema]=$this->findTableNames($schema,false);
		$db=$this->getDbConnection();
		foreach($this->_normalTables[$schema] as $tableName)
		{
			$tableName=$this->quoteTableName($tableName);
			$db->createCommand("ALTER TABLE $tableName $enable CONSTRAINT ALL")->execute();
		}
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return CMssqlTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTable($name)
	{
		$table=new CMssqlTableSchema;
		$this->resolveTableNames($table,$name);
		//if (!in_array($table->name, $this->tableNames)) return null;
		$table->primaryKey=$this->findPrimaryKey($table);
		$table->foreignKeys=$this->findForeignKeys($table);
		if($this->findColumns($table))
		{
			return $table;
		}
		else
			return null;
	}

	/**
	 * Generates various kinds of table names.
	 * @param CMssqlTableSchema $table the table instance
	 * @param string $name the unquoted table name
	 */
	protected function resolveTableNames($table,$name)
	{
		$parts=explode('.',str_replace(array('[',']'),'',$name));
		if(($c=count($parts))==3)
		{
			// Catalog name, schema name and table name provided
			$table->catalogName=$parts[0];
			$table->schemaName=$parts[1];
			$table->name=$parts[2];
			$table->rawName=$this->quoteTableName($table->catalogName).'.'.$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
		elseif ($c==2)
		{
			// Only schema name and table name provided
			$table->name=$parts[1];
			$table->schemaName=$parts[0];
			$table->rawName=$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
		else
		{
			// Only the name given, we need to get at least the schema name
			//if (empty($this->_schemaNames)) $this->findTableNames();
			$table->name=$parts[0];
			$table->schemaName=self::DEFAULT_SCHEMA;
			$table->rawName=$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
	}

	/**
	 * Gets the primary key column(s) details for the given table.
	 * @param CMssqlTableSchema $table table
	 * @return mixed primary keys (null if no pk, string if only 1 column pk, or array if composite pk)
	 */
	protected function findPrimaryKey($table)
	{
		$kcu='INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
		$tc='INFORMATION_SCHEMA.TABLE_CONSTRAINTS';
		if (isset($table->catalogName))
		{
			$kcu=$table->catalogName.'.'.$kcu;
			$tc=$table->catalogName.'.'.$tc;
		}

		$sql = <<<EOD
		SELECT k.column_name field_name
			FROM {$this->quoteTableName($kcu)} k
		    LEFT JOIN {$this->quoteTableName($tc)} c
		      ON k.table_name = c.table_name
		     AND k.constraint_name = c.constraint_name
		   WHERE c.constraint_type ='PRIMARY KEY'
		   	    AND k.table_name = :table
				AND k.table_schema = :schema
EOD;
		$command = $this->getDbConnection()->createCommand($sql);
		$command->bindValue(':table', $table->name);
		$command->bindValue(':schema', $table->schemaName);
		$primary=$command->queryColumn();
		switch (count($primary))
		{
			case 0: // No primary key on table
				$primary=null;
				break;
			case 1: // Only 1 primary key
				$primary=$primary[0];
				break;
		}
		return $primary;
	}

	/**
	 * Gets foreign relationship constraint keys and table name
	 * @param CMssqlTableSchema $table table
	 * @return array foreign relationship table name and keys.
	 */
	protected function findForeignKeys($table)
	{
		$rc='INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS';
		$kcu='INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
		if (isset($table->catalogName))
		{
			$kcu=$table->catalogName.'.'.$kcu;
			$rc=$table->catalogName.'.'.$rc;
		}

		//From http://msdn2.microsoft.com/en-us/library/aa175805(SQL.80).aspx
		$sql = <<<EOD
		SELECT
		     KCU1.CONSTRAINT_NAME AS 'FK_CONSTRAINT_NAME'
		   , KCU1.TABLE_NAME AS 'FK_TABLE_NAME'
		   , KCU1.COLUMN_NAME AS 'FK_COLUMN_NAME'
		   , KCU1.ORDINAL_POSITION AS 'FK_ORDINAL_POSITION'
		   , KCU2.CONSTRAINT_NAME AS 'UQ_CONSTRAINT_NAME'
		   , KCU2.TABLE_NAME AS 'UQ_TABLE_NAME'
		   , KCU2.COLUMN_NAME AS 'UQ_COLUMN_NAME'
		   , KCU2.ORDINAL_POSITION AS 'UQ_ORDINAL_POSITION'
		FROM {$this->quoteTableName($rc)} RC
		JOIN {$this->quoteTableName($kcu)} KCU1
		ON KCU1.CONSTRAINT_CATALOG = RC.CONSTRAINT_CATALOG
		   AND KCU1.CONSTRAINT_SCHEMA = RC.CONSTRAINT_SCHEMA
		   AND KCU1.CONSTRAINT_NAME = RC.CONSTRAINT_NAME
		JOIN {$this->quoteTableName($kcu)} KCU2
		ON KCU2.CONSTRAINT_CATALOG =
		RC.UNIQUE_CONSTRAINT_CATALOG
		   AND KCU2.CONSTRAINT_SCHEMA =
		RC.UNIQUE_CONSTRAINT_SCHEMA
		   AND KCU2.CONSTRAINT_NAME =
		RC.UNIQUE_CONSTRAINT_NAME
		   AND KCU2.ORDINAL_POSITION = KCU1.ORDINAL_POSITION
		WHERE KCU1.TABLE_NAME = :table
EOD;
		$command = $this->getDbConnection()->createCommand($sql);
		$command->bindValue(':table', $table->name);
		$fkeys=array();
		foreach($command->queryAll() as $info)
		{
			$fkeys[$info['FK_COLUMN_NAME']]=array($info['UQ_TABLE_NAME'],$info['UQ_COLUMN_NAME'],);

		}
		return $fkeys;
	}


	/**
	 * Collects the table column metadata.
	 * @param CMssqlTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$columnsTable="INFORMATION_SCHEMA.COLUMNS";
		$where=array();
		$where[]="t1.TABLE_NAME='".$table->name."'";
		if (isset($table->catalogName))
		{
			$where[]="t1.TABLE_CATALOG='".$table->catalogName."'";
			$columnsTable = $table->catalogName.'.'.$columnsTable;
		}
		if (isset($table->schemaName))
			$where[]="t1.TABLE_SCHEMA='".$table->schemaName."'";

		$sql="SELECT t1.*, columnproperty(object_id(t1.table_schema+'.'+t1.table_name), t1.column_name, 'IsIdentity') AS IsIdentity, ".
			 "CONVERT(VARCHAR, t2.value) AS Comment FROM ".$this->quoteTableName($columnsTable)." AS t1 ".
			 "LEFT OUTER JOIN sys.extended_properties AS t2 ON t1.ORDINAL_POSITION = t2.minor_id AND ".
			 "object_name(t2.major_id) = t1.TABLE_NAME AND t2.class=1 AND t2.class_desc='OBJECT_OR_COLUMN' AND t2.name='MS_Description' ".
			 "WHERE ".join(' AND ',$where);
		if (($columns=$this->getDbConnection()->createCommand($sql)->queryAll())===array())
			return false;

		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			if (is_array($table->primaryKey))
				$c->isPrimaryKey=in_array($c->name, $table->primaryKey);
			else
				$c->isPrimaryKey=strcasecmp($c->name,$table->primaryKey)===0;

			$c->isForeignKey=isset($table->foreignKeys[$c->name]);
			$table->columns[$c->name]=$c;
			if ($c->autoIncrement && $table->sequenceName===null)
				$table->sequenceName=$table->name;
		}
		return true;
	}

	/**
	 * Creates a table column.
	 * @param array $column column metadata
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c=new CMssqlColumnSchema;
		$c->name=$column['COLUMN_NAME'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=$column['IS_NULLABLE']=='YES';
		if ($column['NUMERIC_PRECISION_RADIX']!==null)
		{
			// We have a numeric datatype
			$c->size=$c->precision=$column['NUMERIC_PRECISION']!==null?(int)$column['NUMERIC_PRECISION']:null;
			$c->scale=$column['NUMERIC_SCALE']!==null?(int)$column['NUMERIC_SCALE']:null;
		}
		elseif ($column['DATA_TYPE']=='image' || $column['DATA_TYPE']=='text')
			$c->size=$c->precision=null;
		else
			$c->size=$c->precision=($column['CHARACTER_MAXIMUM_LENGTH']!== null)?(int)$column['CHARACTER_MAXIMUM_LENGTH']:null;
		$c->autoIncrement=$column['IsIdentity']==1;
		$c->comment=$column['Comment']===null ? '' : $column['Comment'];

		$c->init($column['DATA_TYPE'],$column['COLUMN_DEFAULT']);
		return $c;
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @param boolean $includeViews whether to include views in the result. Defaults to true.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema='',$includeViews=true)
	{
		if($schema==='')
			$schema=self::DEFAULT_SCHEMA;
		if($includeViews)
			$condition="TABLE_TYPE in ('BASE TABLE','VIEW')";
		else
			$condition="TABLE_TYPE='BASE TABLE'";
		$sql=<<<EOD
SELECT TABLE_NAME, TABLE_SCHEMA FROM [INFORMATION_SCHEMA].[TABLES]
WHERE TABLE_SCHEMA=:schema AND $condition
EOD;
		$command=$this->getDbConnection()->createCommand($sql);
		$command->bindParam(":schema", $schema);
		$rows=$command->queryAll();
		$names=array();
		foreach ($rows as $row)
		{
			if ($schema == self::DEFAULT_SCHEMA)
				$names[]=$row['TABLE_NAME'];
			else
				$names[]=$schema.'.'.$row['TABLE_SCHEMA'].'.'.$row['TABLE_NAME'];
		}

		return $names;
	}

	/**
	 * Creates a command builder for the database.
	 * This method overrides parent implementation in order to create a MSSQL specific command builder
	 * @return CDbCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new CMssqlCommandBuilder($this);
	}

	/**
	 * Builds a SQL statement for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB table.
	 * @since 1.1.6
	 */
	public function renameTable($table, $newName)
	{
		return "sp_rename '$table', '$newName'";
	}

	/**
	 * Builds a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @since 1.1.6
	 */
	public function renameColumn($table, $name, $newName)
	{
		return "sp_rename '$table.$name', '$newName', 'COLUMN'";
	}

	/**
	 * Builds a SQL statement for changing the definition of a column.
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
		$type=$this->getColumnType($type);
		$sql='ALTER TABLE ' . $this->quoteTableName($table) . ' ALTER COLUMN '
			. $this->quoteColumnName($column) . ' '
			. $this->getColumnType($type);
		return $sql;
	}
}
