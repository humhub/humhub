<?php
/**
 * WebAppCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * WebAppCommand creates an Yii Web application at the specified location.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.cli.commands
 * @since 1.0
 */
class WebAppCommand extends CConsoleCommand
{
	private $_rootPath;

	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic webapp <app-path> [<vcs>]

DESCRIPTION
  This command generates an Yii Web Application at the specified location.

PARAMETERS
 * app-path: required, the directory where the new application will be created.
   If the directory does not exist, it will be created. After the application
   is created, please make sure the directory can be accessed by Web users.
 * vcs: optional, version control system you're going to use in the new project.
   Application generator will create all needed files to the specified VCS
   (such as .gitignore, .gitkeep, etc.). Possible values: git, hg. Do not
   use this argument if you're going to create VCS files yourself.

EOD;
	}

	/**
	 * Execute the action.
	 * @param array $args command line parameters specific for this command
	 */
	public function run($args)
	{
		$vcs=false;
		if(isset($args[1]))
		{
			if($args[1]!='git' && $args[1]!='hg')
				$this->usageError('Unsupported VCS specified. Currently only git and hg supported.');
			$vcs=$args[1];
		}
		if(!isset($args[0]))
			$this->usageError('the Web application location is not specified.');
		$path=strtr($args[0],'/\\',DIRECTORY_SEPARATOR);
		if(strpos($path,DIRECTORY_SEPARATOR)===false)
			$path='.'.DIRECTORY_SEPARATOR.$path;
		if(basename($path)=='..')
			$path.=DIRECTORY_SEPARATOR.'.';
		$dir=rtrim(realpath(dirname($path)),'\\/');
		if($dir===false || !is_dir($dir))
			$this->usageError("The directory '$path' is not valid. Please make sure the parent directory exists.");
		if(basename($path)==='.')
			$this->_rootPath=$path=$dir;
		else
			$this->_rootPath=$path=$dir.DIRECTORY_SEPARATOR.basename($path);
		if($this->confirm("Create a Web application under '$path'?"))
		{
			$sourceDir=$this->getSourceDir();
			if($sourceDir===false)
				die("\nUnable to locate the source directory.\n");
			$ignoreFiles=array();
			$renameMap=array();
			switch($vcs)
			{
				case 'git':
					$renameMap=array('git-gitignore'=>'.gitignore','git-gitkeep'=>'.gitkeep'); // move with rename git files
					$ignoreFiles=array('hg-hgignore','hg-hgkeep'); // ignore only hg files
					break;
				case 'hg':
					$renameMap=array('hg-hgignore'=>'.hgignore','hg-hgkeep'=>'.hgkeep'); // move with rename hg files
					$ignoreFiles=array('git-gitignore','git-gitkeep'); // ignore only git files
					break;
				default:
					// no files for renaming
					$ignoreFiles=array('git-gitignore','git-gitkeep','hg-hgignore','hg-hgkeep'); // ignore both git and hg files
					break;
			}
			$list=$this->buildFileList($sourceDir,$path,'',$ignoreFiles,$renameMap);
			$this->addFileModificationCallbacks($list);
			$this->copyFiles($list);
			$this->setPermissions($path);
			echo "\nYour application has been created successfully under {$path}.\n";
		}
	}

	/**
	 * Adjusts created application file and directory permissions
	 *
	 * @param string $targetDir path to created application
	 */
	protected function setPermissions($targetDir)
	{
		@chmod($targetDir.'/assets',0777);
		@chmod($targetDir.'/protected/runtime',0777);
		@chmod($targetDir.'/protected/data',0777);
		@chmod($targetDir.'/protected/data/testdrive.db',0777);
		@chmod($targetDir.'/protected/yiic',0755);
	}

	/**
	 * @return string path to application bootstrap source files
	 */
	protected function getSourceDir()
	{
		return realpath(dirname(__FILE__).'/../views/webapp');
	}

	/**
	 * Adds callbacks that will modify source files
	 *
	 * @param array $fileList
	 */
	protected function addFileModificationCallbacks(&$fileList)
	{
		$fileList['index.php']['callback']=array($this,'generateIndex');
		$fileList['index-test.php']['callback']=array($this,'generateIndex');
		$fileList['protected/tests/bootstrap.php']['callback']=array($this,'generateTestBoostrap');
		$fileList['protected/yiic.php']['callback']=array($this,'generateYiic');
	}

	/**
	 * Inserts path to framework's yii.php into application's index.php
	 *
	 * @param string $source source file path
	 * @param array $params
	 * @return string modified source file content
	 */
	public function generateIndex($source,$params)
	{
		$content=file_get_contents($source);
		$yii=realpath(dirname(__FILE__).'/../../yii.php');
		$yii=$this->getRelativePath($yii,$this->_rootPath.DIRECTORY_SEPARATOR.'index.php');
		$yii=str_replace('\\','\\\\',$yii);
		return preg_replace('/\$yii\s*=(.*?);/',"\$yii=$yii;",$content);
	}

	/**
	 * Inserts path to framework's yiit.php into application's index-test.php
	 *
	 * @param string $source source file path
	 * @param array $params
	 * @return string modified source file content
	 */
	public function generateTestBoostrap($source,$params)
	{
		$content=file_get_contents($source);
		$yii=realpath(dirname(__FILE__).'/../../yiit.php');
		$yii=$this->getRelativePath($yii,$this->_rootPath.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'bootstrap.php');
		$yii=str_replace('\\','\\\\',$yii);
		return preg_replace('/\$yiit\s*=(.*?);/',"\$yiit=$yii;",$content);
	}

	/**
	 * Inserts path to framework's yiic.php into application's yiic.php
	 *
	 * @param string $source source file path
	 * @param array $params
	 * @return string modified source file content
	 */
	public function generateYiic($source,$params)
	{
		$content=file_get_contents($source);
		$yiic=realpath(dirname(__FILE__).'/../../yiic.php');
		$yiic=$this->getRelativePath($yiic,$this->_rootPath.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'yiic.php');
		$yiic=str_replace('\\','\\\\',$yiic);
		return preg_replace('/\$yiic\s*=(.*?);/',"\$yiic=$yiic;",$content);
	}

	/**
	 * Returns variant of $path1 relative to $path2
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return string $path1 relative to $path2
	 */
	protected function getRelativePath($path1,$path2)
	{
		$segs1=explode(DIRECTORY_SEPARATOR,$path1);
		$segs2=explode(DIRECTORY_SEPARATOR,$path2);
		$n1=count($segs1);
		$n2=count($segs2);

		for($i=0;$i<$n1 && $i<$n2;++$i)
		{
			if($segs1[$i]!==$segs2[$i])
				break;
		}

		if($i===0)
			return "'".$path1."'";
		$up='';
		for($j=$i;$j<$n2-1;++$j)
			$up.='/..';
		for(;$i<$n1-1;++$i)
			$up.='/'.$segs1[$i];

		return 'dirname(__FILE__).\''.$up.'/'.basename($path1).'\'';
	}
}