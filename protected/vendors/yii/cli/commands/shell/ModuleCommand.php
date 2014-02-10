<?php
/**
 * ModuleCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id: ModuleCommand.php 433 2008-12-30 22:59:17Z qiang.xue $
 */

/**
 * ModuleCommand generates a controller class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: ModuleCommand.php 433 2008-12-30 22:59:17Z qiang.xue $
 * @package system.cli.commands.shell
 */
class ModuleCommand extends CConsoleCommand
{
	/**
	 * @var string the directory that contains templates for the module command.
	 * Defaults to null, meaning using 'framework/cli/views/shell/module'.
	 * If you set this path and some views are missing in the directory,
	 * the default views will be used.
	 */
	public $templatePath;

	public function getHelp()
	{
		return <<<EOD
USAGE
  module <module-ID>

DESCRIPTION
  This command generates an application module.

PARAMETERS
 * module-ID: required, module ID. It is case-sensitive.

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 * @return integer|null non zero application exit code for help or null on success
	 */
	public function run($args)
	{
		if(!isset($args[0]))
		{
			echo "Error: module ID is required.\n";
			echo $this->getHelp();
			return 1;
		}

		$moduleID=$args[0];
		$moduleClass=ucfirst($moduleID).'Module';
		$modulePath=Yii::app()->getModulePath().DIRECTORY_SEPARATOR.$moduleID;

		$sourceDir=$this->templatePath===null?YII_PATH.'/cli/views/shell/module':$this->templatePath;
		$list=$this->buildFileList($sourceDir,$modulePath);
		$list['module.php']['target']=$modulePath.DIRECTORY_SEPARATOR.$moduleClass.'.php';
		$list['module.php']['callback']=array($this,'generateModuleClass');
		$list['module.php']['params']=array(
			'moduleClass'=>$moduleClass,
			'moduleID'=>$moduleID,
		);
		$list[$moduleClass.'.php']=$list['module.php'];
		unset($list['module.php']);

		$this->copyFiles($list);

		echo <<<EOD

Module '{$moduleID}' has been created under the following folder:
    $modulePath

You may access it in the browser using the following URL:
    http://hostname/path/to/index.php?r=$moduleID

Note, the module needs to be installed first by adding '{$moduleID}'
to the 'modules' property in the application configuration.

EOD;
	}

	public function generateModuleClass($source,$params)
	{
		return $this->renderFile($source,$params,true);
	}
}