<?php
/**
 * FormCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * FormCommand generates a form view based on a specified model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.cli.commands.shell
 * @since 1.0
 */
class FormCommand extends CConsoleCommand
{
	/**
	 * @var string the directory that contains templates for the form command.
	 * Defaults to null, meaning using 'framework/cli/views/shell/form'.
	 * If you set this path and some views are missing in the directory,
	 * the default views will be used.
	 */
	public $templatePath;

	public function getHelp()
	{
		return <<<EOD
USAGE
  form <model-class> <view-name> [scenario]

DESCRIPTION
  This command generates a form view that can be used to collect inputs
  for the specified model.

PARAMETERS
 * model-class: required, model class. This can be either the name of
   the model class (e.g. 'ContactForm') or the path alias of the model
   class file (e.g. 'application.models.ContactForm'). The former can
   be used only if the class can be autoloaded.

 * view-name: required, the name of the view to be generated. This should
   be the path alias of the view script (e.g. 'application.views.site.contact').

 * scenario: optional, the name of the scenario in which the model is used
   (e.g. 'update', 'login'). This determines which model attributes the
   generated form view will be used to collect user inputs for. If this
   is not provided, the scenario will be assumed to be '' (empty string).

EXAMPLES
 * Generates the view script for the 'ContactForm' model:
        form ContactForm application.views.site.contact

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 * @return integer|null non zero application exit code for help or null on success
	 */
	public function run($args)
	{
		if(!isset($args[0],$args[1]))
		{
			echo "Error: both model class and view name are required.\n";
			echo $this->getHelp();
			return 1;
		}
		$scenario=isset($args[2]) ? $args[2] : '';
		$modelClass=Yii::import($args[0],true);
		$model=new $modelClass($scenario);
		$attributes=$model->getSafeAttributeNames();

		$templatePath=$this->templatePath===null?YII_PATH.'/cli/views/shell/form':$this->templatePath;
		$viewPath=Yii::getPathOfAlias($args[1]);
		$viewName=basename($viewPath);
		$viewPath.='.php';
		$params=array(
			'modelClass'=>$modelClass,
			'viewName'=>$viewName,
			'attributes'=>$attributes,
		);
		$list=array(
			basename($viewPath)=>array(
				'source'=>$templatePath.'/form.php',
				'target'=>$viewPath,
				'callback'=>array($this,'generateForm'),
				'params'=>$params,
			),
		);

		$this->copyFiles($list);

		$actionFile=$templatePath.'/action.php';
		if(!is_file($actionFile))  // fall back to default ones
			$actionFile=YII_PATH.'/cli/views/shell/form/action.php';

		echo "The following form view has been successfully created:\n";
		echo "\t$viewPath\n\n";
		echo "You may use the following code in your controller action:\n\n";
		echo $this->renderFile($actionFile,$params,true);
		echo "\n";
	}

	public function generateForm($source,$params)
	{
		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/form/'.basename($source);

		return $this->renderFile($source,$params,true);
	}

	public function class2id($className)
	{
		if(strrpos($className,'Form')===strlen($className)-4)
			$className=substr($className,0,strlen($className)-4);
		return trim(strtolower(str_replace('_','-',preg_replace('/(?<![A-Z])[A-Z]/', '-\0', $className))),'-');
	}
}