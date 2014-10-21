<?php
/**
 * ModelCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * ModelCommand generates a model class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.cli.commands.shell
 * @since 1.0
 */
class ModelCommand extends CConsoleCommand
{
	/**
	 * @var string the directory that contains templates for the model command.
	 * Defaults to null, meaning using 'framework/cli/views/shell/model'.
	 * If you set this path and some views are missing in the directory,
	 * the default views will be used.
	 */
	public $templatePath;
	/**
	 * @var string the directory that contains test fixtures.
	 * Defaults to null, meaning using 'protected/tests/fixtures'.
	 * If this is false, it means fixture file should NOT be generated.
	 */
	public $fixturePath;
	/**
	 * @var string the directory that contains unit test classes.
	 * Defaults to null, meaning using 'protected/tests/unit'.
	 * If this is false, it means unit test file should NOT be generated.
	 */
	public $unitTestPath;

	private $_schema;
	private $_relations; // where we keep table relations
	private $_tables;
	private $_classes;

	public function getHelp()
	{
		return <<<EOD
USAGE
  model <class-name> [table-name]

DESCRIPTION
  This command generates a model class with the specified class name.

PARAMETERS
 * class-name: required, model class name. By default, the generated
   model class file will be placed under the directory aliased as
   'application.models'. To override this default, specify the class
   name in terms of a path alias, e.g., 'application.somewhere.ClassName'.

   If the model class belongs to a module, it should be specified
   as 'ModuleID.models.ClassName'.

   If the class name ends with '*', then a model class will be generated
   for EVERY table in the database.

   If the class name contains a regular expression deliminated by slashes,
   then a model class will be generated for those tables whose name
   matches the regular expression. If the regular expression contains
   sub-patterns, the first sub-pattern will be used to generate the model
   class name.

 * table-name: optional, the associated database table name. If not given,
   it is assumed to be the model class name.

   Note, when the class name ends with '*', this parameter will be
   ignored.

EXAMPLES
 * Generates the Post model:
        model Post

 * Generates the Post model which is associated with table 'posts':
        model Post posts

 * Generates the Post model which should belong to module 'admin':
        model admin.models.Post

 * Generates a model class for every table in the current database:
        model *

 * Same as above, but the model class files should be generated
   under 'protected/models2':
        model application.models2.*

 * Generates a model class for every table whose name is prefixed
   with 'tbl_' in the current database. The model class will not
   contain the table prefix.
        model /^tbl_(.*)$/

 * Same as above, but the model class files should be generated
   under 'protected/models2':
        model application.models2./^tbl_(.*)$/

EOD;
	}

	/**
	 * Checks if the given table is a "many to many" helper table.
	 * Their PK has 2 fields, and both of those fields are also FK to other separate tables.
	 * @param CDbTableSchema $table table to inspect
	 * @return boolean true if table matches description of helper table.
	 */
	protected function isRelationTable($table)
	{
		$pk=$table->primaryKey;
		return (count($pk) === 2 // we want 2 columns
			&& isset($table->foreignKeys[$pk[0]]) // pk column 1 is also a foreign key
			&& isset($table->foreignKeys[$pk[1]]) // pk column 2 is also a foreign key
			&& $table->foreignKeys[$pk[0]][0] !== $table->foreignKeys[$pk[1]][0]); // and the foreign keys point different tables
	}

	/**
	 * Generate code to put in ActiveRecord class's relations() function.
	 * @return array indexed by table names, each entry contains array of php code to go in appropriate ActiveRecord class.
	 *		Empty array is returned if database couldn't be connected.
	 */
	protected function generateRelations()
	{
		$this->_relations=array();
		$this->_classes=array();
		foreach($this->_schema->getTables() as $table)
		{
			$tableName=$table->name;

			if ($this->isRelationTable($table))
			{
				$pks=$table->primaryKey;
				$fks=$table->foreignKeys;

				$table0=$fks[$pks[1]][0];
				$table1=$fks[$pks[0]][0];
				$className0=$this->getClassName($table0);
				$className1=$this->getClassName($table1);

				$unprefixedTableName=$this->removePrefix($tableName,true);

				$relationName=$this->generateRelationName($table0, $table1, true);
				$this->_relations[$className0][$relationName]="array(self::MANY_MANY, '$className1', '$unprefixedTableName($pks[0], $pks[1])')";

				$relationName=$this->generateRelationName($table1, $table0, true);
				$this->_relations[$className1][$relationName]="array(self::MANY_MANY, '$className0', '$unprefixedTableName($pks[0], $pks[1])')";
			}
			else
			{
				$this->_classes[$tableName]=$className=$this->getClassName($tableName);
				foreach ($table->foreignKeys as $fkName => $fkEntry)
				{
					// Put table and key name in variables for easier reading
					$refTable=$fkEntry[0]; // Table name that current fk references to
					$refKey=$fkEntry[1];   // Key in that table being referenced
					$refClassName=$this->getClassName($refTable);

					// Add relation for this table
					$relationName=$this->generateRelationName($tableName, $fkName, false);
					$this->_relations[$className][$relationName]="array(self::BELONGS_TO, '$refClassName', '$fkName')";

					// Add relation for the referenced table
					$relationType=$table->primaryKey === $fkName ? 'HAS_ONE' : 'HAS_MANY';
					$relationName=$this->generateRelationName($refTable, $this->removePrefix($tableName), $relationType==='HAS_MANY');
					$this->_relations[$refClassName][$relationName]="array(self::$relationType, '$className', '$fkName')";
				}
			}
		}
	}

	protected function getClassName($tableName)
	{
		return isset($this->_tables[$tableName]) ? $this->_tables[$tableName] : $this->generateClassName($tableName);
	}

	/**
	 * Generates model class name based on a table name
	 * @param string $tableName the table name
	 * @return string the generated model class name
	 */
	protected function generateClassName($tableName)
	{
		return str_replace(' ','',
			ucwords(
				trim(
					strtolower(
						str_replace(array('-','_'),' ',
							preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $tableName))))));
	}

	/**
	 * Generates the mapping table between table names and class names.
	 * @param CDbSchema $schema the database schema
	 * @param string $pattern a regular expression that may be used to filter table names
	 */
	protected function generateClassNames($schema,$pattern=null)
	{
		$this->_tables=array();
		foreach($schema->getTableNames() as $name)
		{
			if($pattern===null)
				$this->_tables[$name]=$this->generateClassName($this->removePrefix($name));
			elseif(preg_match($pattern,$name,$matches))
			{
				if(count($matches)>1 && !empty($matches[1]))
					$className=$this->generateClassName($matches[1]);
				else
					$className=$this->generateClassName($matches[0]);
				$this->_tables[$name]=empty($className) ? $name : $className;
			}
		}
	}

	/**
	 * Generate a name for use as a relation name (inside relations() function in a model).
	 * @param string $tableName the name of the table to hold the relation
	 * @param string $fkName the foreign key name
	 * @param boolean $multiple whether the relation would contain multiple objects
	 * @return string the generated relation name
	 */
	protected function generateRelationName($tableName, $fkName, $multiple)
	{
		if(strcasecmp(substr($fkName,-2),'id')===0 && strcasecmp($fkName,'id'))
			$relationName=rtrim(substr($fkName, 0, -2),'_');
		else
			$relationName=$fkName;
		$relationName[0]=strtolower($relationName);

		$rawName=$relationName;
		if($multiple)
			$relationName=$this->pluralize($relationName);

		$table=$this->_schema->getTable($tableName);
		$i=0;
		while(isset($table->columns[$relationName]))
			$relationName=$rawName.($i++);
		return $relationName;
	}

	/**
	 * Execute the action.
	 * @param array $args command line parameters specific for this command
	 * @return integer|null non zero application exit code for help or null on success
	 */
	public function run($args)
	{
		if(!isset($args[0]))
		{
			echo "Error: model class name is required.\n";
			echo $this->getHelp();
			return 1;
		}
		$className=$args[0];

		if(($db=Yii::app()->getDb())===null)
		{
			echo "Error: an active 'db' connection is required.\n";
			echo "If you already added 'db' component in application configuration,\n";
			echo "please quit and re-enter the yiic shell.\n";
			return 1;
		}

		$db->active=true;
		$this->_schema=$db->schema;

		if(!preg_match('/^[\w\.\-\*]*(.*?)$/',$className,$matches))
		{
			echo "Error: model class name is invalid.\n";
			return 1;
		}

		if(empty($matches[1]))  // without regular expression
		{
			$this->generateClassNames($this->_schema);
			if(($pos=strrpos($className,'.'))===false)
				$basePath=Yii::getPathOfAlias('application.models');
			else
			{
				$basePath=Yii::getPathOfAlias(substr($className,0,$pos));
				$className=substr($className,$pos+1);
			}
			if($className==='*') // generate all models
				$this->generateRelations();
			else
			{
				$tableName=isset($args[1])?$args[1]:$className;
				$tableName=$this->addPrefix($tableName);
				$this->_tables[$tableName]=$className;
				$this->generateRelations();
				$this->_classes=array($tableName=>$className);
			}
		}
		else  // with regular expression
		{
			$pattern=$matches[1];
			$pos=strrpos($className,$pattern);
			if($pos>0)  // only regexp is given
				$basePath=Yii::getPathOfAlias(rtrim(substr($className,0,$pos),'.'));
			else
				$basePath=Yii::getPathOfAlias('application.models');
			$this->generateClassNames($this->_schema,$pattern);
			$classes=$this->_tables;
			$this->generateRelations();
			$this->_classes=$classes;
		}

		if(count($this->_classes)>1)
		{
			$entries=array();
			$count=0;
			foreach($this->_classes as $tableName=>$className)
				$entries[]=++$count.". $className ($tableName)";
			echo "The following model classes (tables) match your criteria:\n";
			echo implode("\n",$entries)."\n\n";
			if(!$this->confirm("Do you want to generate the above classes?"))
				return;
		}

		$templatePath=$this->templatePath===null?YII_PATH.'/cli/views/shell/model':$this->templatePath;
		$fixturePath=$this->fixturePath===null?Yii::getPathOfAlias('application.tests.fixtures'):$this->fixturePath;
		$unitTestPath=$this->unitTestPath===null?Yii::getPathOfAlias('application.tests.unit'):$this->unitTestPath;

		$list=array();
		$files=array();
		foreach ($this->_classes as $tableName=>$className)
		{
			$files[$className]=$classFile=$basePath.DIRECTORY_SEPARATOR.$className.'.php';
			$list['models/'.$className.'.php']=array(
				'source'=>$templatePath.DIRECTORY_SEPARATOR.'model.php',
				'target'=>$classFile,
				'callback'=>array($this,'generateModel'),
				'params'=>array($className,$tableName),
			);
			if($fixturePath!==false)
			{
				$list['fixtures/'.$tableName.'.php']=array(
					'source'=>$templatePath.DIRECTORY_SEPARATOR.'fixture.php',
					'target'=>$fixturePath.DIRECTORY_SEPARATOR.$tableName.'.php',
					'callback'=>array($this,'generateFixture'),
					'params'=>$this->_schema->getTable($tableName),
				);
			}
			if($unitTestPath!==false)
			{
				$fixtureName=$this->pluralize($className);
				$fixtureName[0]=strtolower($fixtureName);
				$list['unit/'.$className.'Test.php']=array(
					'source'=>$templatePath.DIRECTORY_SEPARATOR.'test.php',
					'target'=>$unitTestPath.DIRECTORY_SEPARATOR.$className.'Test.php',
					'callback'=>array($this,'generateTest'),
					'params'=>array($className,$fixtureName),
				);
			}
		}

		$this->copyFiles($list);

		foreach($files as $className=>$file)
		{
			if(!class_exists($className,false))
				include_once($file);
		}

		$classes=implode(", ", $this->_classes);

		echo <<<EOD

The following model classes are successfully generated:
    $classes

If you have a 'db' database connection, you can test these models now with:
    \$model={$className}::model()->find();
    print_r(\$model);

EOD;
	}

	public function generateModel($source,$params)
	{
		list($className,$tableName)=$params;
		$rules=array();
		$labels=array();
		$relations=array();
		if(($table=$this->_schema->getTable($tableName))!==null)
		{
			$required=array();
			$integers=array();
			$numerical=array();
			$length=array();
			$safe=array();
			foreach($table->columns as $column)
			{
				$label=ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $column->name)))));
				$label=preg_replace('/\s+/',' ',$label);
				if(strcasecmp(substr($label,-3),' id')===0)
					$label=substr($label,0,-3);
				$labels[$column->name]=$label;
				if($column->isPrimaryKey && $table->sequenceName!==null)
					continue;
				$r=!$column->allowNull && $column->defaultValue===null;
				if($r)
					$required[]=$column->name;
				if($column->type==='integer')
					$integers[]=$column->name;
				elseif($column->type==='double')
					$numerical[]=$column->name;
				elseif($column->type==='string' && $column->size>0)
					$length[$column->size][]=$column->name;
				elseif(!$column->isPrimaryKey && !$r)
					$safe[]=$column->name;
			}
			if($required!==array())
				$rules[]="array('".implode(', ',$required)."', 'required')";
			if($integers!==array())
				$rules[]="array('".implode(', ',$integers)."', 'numerical', 'integerOnly'=>true)";
			if($numerical!==array())
				$rules[]="array('".implode(', ',$numerical)."', 'numerical')";
			if($length!==array())
			{
				foreach($length as $len=>$cols)
					$rules[]="array('".implode(', ',$cols)."', 'length', 'max'=>$len)";
			}
			if($safe!==array())
				$rules[]="array('".implode(', ',$safe)."', 'safe')";

			if(isset($this->_relations[$className]) && is_array($this->_relations[$className]))
				$relations=$this->_relations[$className];
		}
		else
			echo "Warning: the table '$tableName' does not exist in the database.\n";

		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/model/'.basename($source);
		return $this->renderFile($source,array(
			'className'=>$className,
			'tableName'=>$this->removePrefix($tableName,true),
			'columns'=>isset($table) ? $table->columns : array(),
			'rules'=>$rules,
			'labels'=>$labels,
			'relations'=>$relations,
		),true);
	}

	public function generateFixture($source,$table)
	{
		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/model/'.basename($source);
		return $this->renderFile($source, array(
			'table'=>$table,
		),true);
	}

	public function generateTest($source,$params)
	{
		list($className,$fixtureName)=$params;
		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/model/'.basename($source);
		return $this->renderFile($source, array(
			'className'=>$className,
			'fixtureName'=>$fixtureName,
		),true);
	}

	protected function removePrefix($tableName,$addBrackets=false)
	{
		$tablePrefix=Yii::app()->getDb()->tablePrefix;
		if($tablePrefix!='' && !strncmp($tableName,$tablePrefix,strlen($tablePrefix)))
		{
			$tableName=substr($tableName,strlen($tablePrefix));
			if($addBrackets)
				$tableName='{{'.$tableName.'}}';
		}
		return $tableName;
	}

	protected function addPrefix($tableName)
	{
		$tablePrefix=Yii::app()->getDb()->tablePrefix;
		if($tablePrefix!='' && strncmp($tableName,$tablePrefix,strlen($tablePrefix)))
			$tableName=$tablePrefix.$tableName;
		return $tableName;
	}
}