<?php
/**
 * CMultiFileUpload class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMultiFileUpload generates a file input that can allow uploading multiple files at a time.
 *
 * This is based on the {@link http://www.fyneworks.com/jquery/multiple-file-upload/ jQuery Multi File Upload plugin}.
 * The uploaded file information can be accessed via $_FILES[widget-name], which gives an array of the uploaded
 * files. Note, you have to set the enclosing form's 'enctype' attribute to be 'multipart/form-data'.
 *
 * Example:
 * <pre>
 * <?php
 *   $this->widget('CMultiFileUpload', array(
 *      'model'=>$model,
 *      'attribute'=>'files',
 *      'accept'=>'jpg|gif',
 *      'options'=>array(
 *         'onFileSelect'=>'function(e, v, m){ alert("onFileSelect - "+v) }',
 *         'afterFileSelect'=>'function(e, v, m){ alert("afterFileSelect - "+v) }',
 *         'onFileAppend'=>'function(e, v, m){ alert("onFileAppend - "+v) }',
 *         'afterFileAppend'=>'function(e, v, m){ alert("afterFileAppend - "+v) }',
 *         'onFileRemove'=>'function(e, v, m){ alert("onFileRemove - "+v) }',
 *         'afterFileRemove'=>'function(e, v, m){ alert("afterFileRemove - "+v) }',
 *      ),
 *   ));
 * ?>
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CMultiFileUpload extends CInputWidget
{
	/**
	 * @var string the file types that are allowed (eg "gif|jpg").
	 * Note, the server side still needs to check if the uploaded files have allowed types.
	 */
	public $accept;
	/**
	 * @var integer the maximum number of files that can be uploaded. If -1, it means no limits. Defaults to -1.
	 */
	public $max=-1;
	/**
	 * @var string the label for the remove button. Defaults to "Remove".
	 */
	public $remove;
	/**
	 * @var string message that is displayed when a file type is not allowed.
	 */
	public $denied;
	/**
	 * @var string message that is displayed when a file is selected.
	 */
	public $selected;
	/**
	 * @var string message that is displayed when a file appears twice.
	 */
	public $duplicate;
	/**
	 * @var string the message template for displaying the uploaded file name
	 * @since 1.1.3
	 */
	public $file;
	/**
	 * @var array additional options that can be passed to the constructor of the multifile js object.
	 * @since 1.1.7
	 */
	public $options=array();


	/**
	 * Runs the widget.
	 * This method registers all needed client scripts and renders
	 * the multiple file uploader.
	 */
	public function run()
	{
		list($name,$id)=$this->resolveNameID();
		if(substr($name,-2)!=='[]')
			$name.='[]';
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		$this->registerClientScript();
		echo CHtml::fileField($name,'',$this->htmlOptions);
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 */
	public function registerClientScript()
	{
		$id=$this->htmlOptions['id'];

		$options=$this->getClientOptions();
		$options=$options===array()? '' : CJavaScript::encode($options);

		$cs=Yii::app()->getClientScript();
		$cs->registerCoreScript('multifile');
		$cs->registerScript('Yii.CMultiFileUpload#'.$id,"jQuery(\"#{$id}\").MultiFile({$options});");
	}

	/**
	 * @return array the javascript options
	 */
	protected function getClientOptions()
	{
		$options=$this->options;
		foreach(array('onFileRemove','afterFileRemove','onFileAppend','afterFileAppend','onFileSelect','afterFileSelect') as $event)
		{
			if(isset($options[$event]) && !($options[$event] instanceof CJavaScriptExpression))
				$options[$event]=new CJavaScriptExpression($options[$event]);
		}

		if($this->accept!==null)
			$options['accept']=$this->accept;
		if($this->max>0)
			$options['max']=$this->max;

		$messages=array();
		foreach(array('remove','denied','selected','duplicate','file') as $messageName)
		{
			if($this->$messageName!==null)
				$messages[$messageName]=$this->$messageName;
		}
		if($messages!==array())
			$options['STRING']=$messages;

		return $options;
	}
}
