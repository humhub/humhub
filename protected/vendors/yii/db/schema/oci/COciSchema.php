<?php
/**
 * COciSchema class file.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * COciSchema is the class for retrieving metadata information from an Oracle database.
 *
 * @property string $defaultSchema Default schema.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @package system.db.schema.oci
 */
class COciSchema extends CDbSchema
{
	private $_defaultSchema = '';

	/**
	 * @var array the abstract column types mapped to physical column types.
	 * @since 1.1.6
	 */
    public $columnTypes=array(
        'pk' => 'NUMBER(10) NOT NULL PRIMARY KEY',
        'string' => 'VARCHAR2(255)',
        'text' => 'CLOB',
        'integer' => 'NUMBER(10)',
        'float' => 'NUMBER',
        'decimal' => 'NUMBER',
        'datetime' => 'TIMESTAMP',
        'timestamp' => 'TIMESTAMP',
        'time' => 'TIMESTAMP',
        'date' => 'DATE',
        'binary' => 'BLOB',
        'boolean' => 'NUMBER(1)',
		'money' => 'NUMBER(19,4)',
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
		return '"'.$name.'"';
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
		return '"'.$name.'"';
	}

	/**
	 * Creates a command builder for the database.
	 * This method may be overridden by child classes to create a DBMS-specific command builder.
	 * @return CDbCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new COciCommandBuilder($this);
	}

	/**
     * @param string $schema default schema.
     */
    public function setDefaultSchema($schema)
    {
		$this->_defaultSchema=$schema;
    }

    /**
     * @return string default schema.
     */
    public function getDefaultSchema()
    {
		if (!strlen($this->_defaultSchema))
		{
			$this->setDefaultSchema(strtoupper($this->getDbConnection()->username));
		}

		return $this->_defaultSchema;
    }

    /**
     * @param string $table table name with optional schema name prefix, uses default schema name prefix is not provided.
     * @return array tuple as ($schemaName,$tableName)
     */
    protected function getSchemaTableName($table)
    {
		$table = strtoupper($table);
		if(count($parts= explode('.', str_replace('"','',$table))) > 1)
			return array($parts[0], $parts[1]);
		else
			return array($this->getDefaultSchema(),$parts[0]);
    }

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return CDbTableSchema driver dependent table metadata.
	 */
	protected function loadTable($name)
	{
		$table=new COciTableSchema;
		$this->resolveTableNames($table,$name);

		if(!$this->findColumns($table))
			return null;
		$this->findConstraints($table);

		return $table;
	}

	/**
	 * Generates various kinds of table names.
	 * @param COciTableSchema $table the table instance
	 * @param string $name the unquoted table name
	 */
	protected function resolveTableNames($table,$name)
	{
		$parts=explode('.',str_replace('"','',$name));
		if(isset($parts[1]))
		{
			$schemaName=$parts[0];
			$tableName=$parts[1];
		}
		else
		{
			$schemaName=$this->getDefaultSchema();
			$tableName=$parts[0];
		}

		$table->name=$tableName;
		$table->schemaName=$schemaName;
		if($schemaName===$this->getDefaultSchema())
			$table->rawName=$this->quoteTableName($tableName);
		else
			$table->rawName=$this->quoteTableName($schemaName).'.'.$this->quoteTableName($tableName);
	}

	/**
	 * Collects the table column metadata.
	 * @param COciTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$schemaName=$table->schemaName;
		$tableName=$table->name;

		$sql=<<<EOD
SELECT a.column_name, a.data_type ||
    case
        when data_precision is not null
            then '(' || a.data_precision ||
                    case when a.data_scale > 0 then ',' || a.data_scale else '' end
                || ')'
        when data_type = 'DATE' then ''
        when data_type = 'NUMBER' then ''
        else '(' || to_char(a.data_length) || ')'
    end as data_type,
    a.nullable, a.data_default,
    (   SELECT D.constraint_type
        FROM ALL_CONS_COLUMNS C
        inner join ALL_constraints D on D.OWNER = C.OWNER and D.constraint_name = C.constraint_name
        WHERE C.OWNER = B.OWNER
           and C.table_name = B.object_name
           and C.column_name = A.column_name
           and D.constraint_type = 'P') as Key,
    com.comments as column_comment
FROM ALL_TAB_COLUMNS A
inner join ALL_OBJECTS B ON b.owner = a.owner and ltrim(B.OBJECT_NAME) = ltrim(A.TABLE_NAME)
LEFT JOIN user_col_comments com ON (A.table_name = com.table_name AND A.column_name = com.column_name)
WHERE
    a.owner = '{$schemaName}'
	and (b.object_type = 'TABLE' or b.object_type = 'VIEW')
	and b.object_name = '{$tableName}'
ORDER by a.column_id
EOD;

		$command=$this->getDbConnection()->createCommand($sql);

		if(($columns=$command->queryAll())===array()){
			return false;
		}

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
				$table->sequenceName='';
				$c->autoIncrement=true;
			}
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
		$c=new COciColumnSchema;
		$c->name=$column['COLUMN_NAME'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=$column['NULLABLE']==='Y';
		$c->isPrimaryKey=strpos($column['KEY'],'P')!==false;
		$c->isForeignKey=false;
		$c->init($column['DATA_TYPE'],$column['DATA_DEFAULT']);
		$c->comment=$column['COLUMN_COMMENT']===null ? '' : $column['COLUMN_COMMENT'];

		return $c;
	}

	/**
	 * Collects the primary and foreign key column details for the given table.
	 * @param COciTableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$sql=<<<EOD
		SELECT D.constraint_type as CONSTRAINT_TYPE, C.COLUMN_NAME, C.position, D.r_constraint_name,
                E.table_name as table_ref, f.column_name as column_ref,
            	C.table_name
        FROM ALL_CONS_COLUMNS C
        inner join ALL_constraints D on D.OWNER = C.OWNER and D.constraint_name = C.constraint_name
        left join ALL_constraints E on E.OWNER = D.r_OWNER and E.constraint_name = D.r_constraint_name
        left join ALL_cons_columns F on F.OWNER = E.OWNER and F.constraint_name = E.constraint_name and F.position = c.position
        WHERE C.OWNER = '{$table->schemaName}'
           and C.table_name = '{$table->name}'
           and D.constraint_type <> 'P'
        order by d.constraint_name, c.position
EOD;
		$command=$this->getDbConnection()->createCommand($sql);
		foreach($command->queryAll() as $row)
		{
			if($row['CONSTRAINT_TYPE']==='R')   // foreign key
			{
				$name = $row["COLUMN_NAME"];
				$table->foreignKeys[$name]=array($row["TABLE_REF"], $row["COLUMN_REF"]);
				if(isset($table->columns[$name]))
					$table->columns[$name]->isForeignKey=true;
			}

		}
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema='')
	{
		if($schema==='')
		{
			$sql=<<<EOD
SELECT table_name, '{$schema}' as table_schema FROM user_tables
EOD;
			$command=$this->getDbConnection()->createCommand($sql);
		}
		else
		{
			$sql=<<<EOD
SELECT object_name as table_name, owner as table_schema FROM all_objects
WHERE object_type = 'TABLE' AND owner=:schema
EOD;
			$command=$this->getDbConnection()->createCommand($sql);
			$command->bindParam(':schema',$schema);
		}

		$rows=$command->queryAll();
		$names=array();
		foreach($rows as $row)
		{
			if($schema===$this->getDefaultSchema() || $schema==='')
				$names[]=$row['TABLE_NAME'];
			else
				$names[]=$row['TABLE_SCHEMA'].'.'.$row['TABLE_NAME'];
		}
		return $names;
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
		return 'ALTER TABLE ' . $this->quoteTableName($table) . ' RENAME TO ' . $this->quoteTableName($newName);
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
		$sql='ALTER TABLE ' . $this->quoteTableName($table) . ' MODIFY '
			. $this->quoteColumnName($column) . ' '
			. $this->getColumnType($type);
		return $sql;
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
	 * Resets the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param CDbTableSchema $table the table schema whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 * @since 1.1.13
	 */
	public function resetSequence($table,$value=1)
	{
		$seq = $table->name."_SEQ";
		if($table->sequenceName!==null)
		{
			$this->getDbConnection()->createCommand("DROP SEQUENCE ".$seq)->execute();

			$createSequenceSql = <<< SQL
create sequence $seq
start with $value
increment by 1
nomaxvalue
nocache
SQL;
			$this->getDbConnection()->createCommand($createSequenceSql)->execute();
		}
	}
}
