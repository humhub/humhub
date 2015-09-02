<?php
/**
 * CViewRenderer class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CViewRenderer is the base class for view renderer classes.
 *
 * A view renderer is an application component that renders views written
 * in a customized syntax.
 *
 * Once installing a view renderer as a 'viewRenderer' application component,
 * the normal view rendering process will be intercepted by the renderer.
 * The renderer will first parse the source view file and then render the
 * the resulting view file.
 *
 * Parsing results are saved as temporary files that may be stored
 * under the application runtime directory or together with the source view file.
 *
 * @author Steve Heyns http://customgothic.com/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.renderers
 * @since 1.0
 */
abstract class CViewRenderer extends CApplicationComponent implements IViewRenderer
{
	/**
	 * @var boolean whether to store the parsing results in the application's
	 * runtime directory. Defaults to true. If false, the parsing results will
	 * be saved as files under the same directory as the source view files and the
	 * file names will be the source file names appended with letter 'c'.
	 */
	public $useRuntimePath=true;
	/**
	 * @var integer the chmod permission for temporary directories and files
	 * generated during parsing. Defaults to 0755 (owner rwx, group rx and others rx).
	 */
	public $filePermission=0755;
	/**
	 * @var string the extension name of the view file. Defaults to '.php'.
	 */
	public $fileExtension='.php';

	/**
	 * Parses the source view file and saves the results as another file.
	 * @param string $sourceFile the source view file path
	 * @param string $viewFile the resulting view file path
	 */
	abstract protected function generateViewFile($sourceFile,$viewFile);

	/**
	 * Renders a view file.
	 * This method is required by {@link IViewRenderer}.
	 * @param CBaseController $context the controller or widget who is rendering the view file.
	 * @param string $sourceFile the view file path
	 * @param mixed $data the data to be passed to the view
	 * @param boolean $return whether the rendering result should be returned
	 * @return mixed the rendering result, or null if the rendering result is not needed.
	 */
	public function renderFile($context,$sourceFile,$data,$return)
	{
		if(!is_file($sourceFile) || ($file=realpath($sourceFile))===false)
			throw new CException(Yii::t('yii','View file "{file}" does not exist.',array('{file}'=>$sourceFile)));
		$viewFile=$this->getViewFile($sourceFile);
		if(@filemtime($sourceFile)>@filemtime($viewFile))
		{
			$this->generateViewFile($sourceFile,$viewFile);
			@chmod($viewFile,$this->filePermission);
		}
		return $context->renderInternal($viewFile,$data,$return);
	}

	/**
	 * Generates the resulting view file path.
	 * @param string $file source view file path
	 * @return string resulting view file path
	 */
	protected function getViewFile($file)
	{
		if($this->useRuntimePath)
		{
			$crc=sprintf('%x', crc32(get_class($this).Yii::getVersion().dirname($file)));
			$viewFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$crc.DIRECTORY_SEPARATOR.basename($file);
			if(!is_file($viewFile))
				@mkdir(dirname($viewFile),$this->filePermission,true);
			return $viewFile;
		}
		else
			return $file.'c';
	}
}
