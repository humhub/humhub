<?php
/**
 * CrudCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CrudCommand generates code implementing CRUD operations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.cli.commands.shell
 * @since 1.0
 */
class CrudCommand extends CConsoleCommand
{
	/**
	 * @var string the directory that contains templates for crud commands.
	 * Defaults to null, meaning using 'framework/cli/views/shell/crud'.
	 * If you set this path and some views are missing in the directory,
	 * the default views will be used.
	 */
	public $templatePath;
	/**
	 * @var string the directory that contains functional test classes.
	 * Defaults to null, meaning using 'protected/tests/functional'.
	 * If this is false, it means functional test file should NOT be generated.
	 */
	public $functionalTestPath;
	/**
	 * @var array list of actions to be created. Each action must be associated with a template file with the same name.
	 */
	public $actions=array('create','update','index','view','admin','_form','_view','_search');

	public function getHelp()
	{
		return <<<EOD
USAGE
  crud <model-class> [controller-ID] ...

DESCRIPTION
  This command generates a controller and views that accomplish
  CRUD operations for the specified data model.

PARAMETERS
 * model-class: required, the name of the data model class. This can
   also be specified as a path alias (e.g. application.models.Post).
   If the model class belongs to a module, it should be specified
   as 'ModuleID.models.ClassName'.

 * controller-ID: optional, the controller ID (e.g. 'post').
   If this is not specified, the model class name will be used
   as the controller ID. In this case, if the model belongs to
   a module, the controller will also be created under the same
   module.

   If the controller should be located under a subdirectory,
   please specify the controller ID as 'path/to/ControllerID'
   (e.g. 'admin/user').

   If the controller belongs to a module (different from the module
   that the model belongs to), please specify the controller ID
   as 'ModuleID/ControllerID' or 'ModuleID/path/to/Controller'.

EXAMPLES
 * Generates CRUD for the Post model:
        crud Post

 * Generates CRUD for the Post model which belongs to module 'admin':
        crud admin.models.Post

 * Generates CRUD for the Post model. The generated controller should
   belong to module 'admin', but not the model class:
        crud Post admin/post

EOD;
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
			echo "Error: data model class is required.\n";
			echo $this->getHelp();
			return 1;
		}
		$module=Yii::app();
		$modelClass=$args[0];
		if(($pos=strpos($modelClass,'.'))===false)
			$modelClass='application.models.'.$modelClass;
		else
		{
			$id=substr($modelClass,0,$pos);
			if(($m=Yii::app()->getModule($id))!==null)
				$module=$m;
		}
		$modelClass=Yii::import($modelClass);

		if(isset($args[1]))
		{
			$controllerID=$args[1];
			if(($pos=strrpos($controllerID,'/'))===false)
			{
				$controllerClass=ucfirst($controllerID).'Controller';
				$controllerFile=$module->controllerPath.DIRECTORY_SEPARATOR.$controllerClass.'.php';
				$controllerID[0]=strtolower($controllerID[0]);
			}
			else
			{
				$last=substr($controllerID,$pos+1);
				$last[0]=strtolower($last);
				$pos2=strpos($controllerID,'/');
				$first=substr($controllerID,0,$pos2);
				$middle=$pos===$pos2?'':substr($controllerID,$pos2+1,$pos-$pos2);

				$controllerClass=ucfirst($last).'Controller';
				$controllerFile=($middle===''?'':$middle.'/').$controllerClass.'.php';
				$controllerID=$middle===''?$last:$middle.'/'.$last;
				if(($m=Yii::app()->getModule($first))!==null)
					$module=$m;
				else
				{
					$controllerFile=$first.'/'.$controllerFile;
					$controllerID=$first.'/'.$controllerID;
				}

				$controllerFile=$module->controllerPath.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$controllerFile);
			}
		}
		else
		{
			$controllerID=$modelClass;
			$controllerClass=ucfirst($controllerID).'Controller';
			$controllerFile=$module->controllerPath.DIRECTORY_SEPARATOR.$controllerClass.'.php';
			$controllerID[0]=strtolower($controllerID[0]);
		}

		$templatePath=$this->templatePath===null?YII_PATH.'/cli/views/shell/crud':$this->templatePath;
		$functionalTestPath=$this->functionalTestPath===null?Yii::getPathOfAlias('application.tests.functional'):$this->functionalTestPath;

		$viewPath=$module->viewPath.DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,$controllerID);
		$fixtureName=$this->pluralize($modelClass);
		$fixtureName[0]=strtolower($fixtureName);
		$list=array(
			basename($controllerFile)=>array(
				'source'=>$templatePath.'/controller.php',
				'target'=>$controllerFile,
				'callback'=>array($this,'generateController'),
				'params'=>array($controllerClass,$modelClass),
			),
		);

		if($functionalTestPath!==false)
		{
			$list[$modelClass.'Test.php']=array(
				'source'=>$templatePath.'/test.php',
				'target'=>$functionalTestPath.DIRECTORY_SEPARATOR.$modelClass.'Test.php',
				'callback'=>array($this,'generateTest'),
				'params'=>array($controllerID,$fixtureName,$modelClass),
			);
		}

		foreach($this->actions as $action)
		{
			$list[$action.'.php']=array(
				'source'=>$templatePath.'/'.$action.'.php',
				'target'=>$viewPath.'/'.$action.'.php',
				'callback'=>array($this,'generateView'),
				'params'=>$modelClass,
			);
		}

		$this->copyFiles($list);

		if($module instanceof CWebModule)
			$moduleID=$module->id.'/';
		else
			$moduleID='';

		echo "\nCrud '{$controllerID}' has been successfully created. You may access it via:\n";
		echo "http://hostname/path/to/index.php?r={$moduleID}{$controllerID}\n";
	}

	public function generateController($source,$params)
	{
		list($controllerClass,$modelClass)=$params;
		$model=CActiveRecord::model($modelClass);
		$id=$model->tableSchema->primaryKey;
		if($id===null)
			throw new ShellException(Yii::t('yii','Error: Table "{table}" does not have a primary key.',array('{table}'=>$model->tableName())));
		elseif(is_array($id))
			throw new ShellException(Yii::t('yii','Error: Table "{table}" has a composite primary key which is not supported by crud command.',array('{table}'=>$model->tableName())));

		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/crud/'.basename($source);

		return $this->renderFile($source,array(
			'ID'=>$id,
			'controllerClass'=>$controllerClass,
			'modelClass'=>$modelClass,
		),true);
	}

	public function generateView($source,$modelClass)
	{
		$model=CActiveRecord::model($modelClass);
		$table=$model->getTableSchema();
		$columns=$table->columns;
		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/crud/'.basename($source);
		return $this->renderFile($source,array(
			'ID'=>$table->primaryKey,
			'modelClass'=>$modelClass,
			'columns'=>$columns),true);
	}

	public function generateTest($source,$params)
	{
		list($controllerID,$fixtureName,$modelClass)=$params;
		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/crud/'.basename($source);
		return $this->renderFile($source, array(
			'controllerID'=>$controllerID,
			'fixtureName'=>$fixtureName,
			'modelClass'=>$modelClass,
		),true);
	}

	public function generateInputLabel($modelClass,$column)
	{
		return "CHtml::activeLabelEx(\$model,'{$column->name}')";
	}

	public function generateInputField($modelClass,$column)
	{
		if($column->type==='boolean')
			return "CHtml::activeCheckBox(\$model,'{$column->name}')";
		elseif(stripos($column->dbType,'text')!==false)
			return "CHtml::activeTextArea(\$model,'{$column->name}',array('rows'=>6, 'cols'=>50))";
		else
		{
			if(preg_match('/^(password|pass|passwd|passcode)$/i',$column->name))
				$inputField='activePasswordField';
			else
				$inputField='activeTextField';

			if($column->type!=='string' || $column->size===null)
				return "CHtml::{$inputField}(\$model,'{$column->name}')";
			else
			{
				if(($size=$maxLength=$column->size)>60)
					$size=60;
				return "CHtml::{$inputField}(\$model,'{$column->name}',array('size'=>$size,'maxlength'=>$maxLength))";
			}
		}
	}

	public function generateActiveLabel($modelClass,$column)
	{
		return "\$form->labelEx(\$model,'{$column->name}')";
	}

	public function generateActiveField($modelClass,$column)
	{
		if($column->type==='boolean')
			return "\$form->checkBox(\$model,'{$column->name}')";
		elseif(stripos($column->dbType,'text')!==false)
			return "\$form->textArea(\$model,'{$column->name}',array('rows'=>6, 'cols'=>50))";
		else
		{
			if(preg_match('/^(password|pass|passwd|passcode)$/i',$column->name))
				$inputField='passwordField';
			else
				$inputField='textField';

			if($column->type!=='string' || $column->size===null)
				return "\$form->{$inputField}(\$model,'{$column->name}')";
			else
			{
				if(($size=$maxLength=$column->size)>60)
					$size=60;
				return "\$form->{$inputField}(\$model,'{$column->name}',array('size'=>$size,'maxlength'=>$maxLength))";
			}
		}
	}

	public function guessNameColumn($columns)
	{
		foreach($columns as $column)
		{
			if(!strcasecmp($column->name,'name'))
				return $column->name;
		}
		foreach($columns as $column)
		{
			if(!strcasecmp($column->name,'title'))
				return $column->name;
		}
		foreach($columns as $column)
		{
			if($column->isPrimaryKey)
				return $column->name;
		}
		return 'id';
	}

	public function class2id($className)
	{
		return trim(strtolower(str_replace('_','-',preg_replace('/(?<![A-Z])[A-Z]/', '-\0', $className))),'-');
	}

	public function class2name($className,$pluralize=false)
	{
		if($pluralize)
			$className=$this->pluralize($className);
		return ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $className)))));
	}
}
