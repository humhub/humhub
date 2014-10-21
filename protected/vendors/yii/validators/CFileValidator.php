<?php
/**
 * CFileValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFileValidator verifies if an attribute is receiving a valid uploaded file.
 *
 * It uses the model class and attribute name to retrieve the information
 * about the uploaded file. It then checks if a file is uploaded successfully,
 * if the file size is within the limit and if the file type is allowed.
 *
 * This validator will attempt to fetch uploaded data if attribute is not
 * previously set. Please note that this cannot be done if input is tabular:
 * <pre>
 *  foreach($models as $i=>$model)
 *     $model->attribute = CUploadedFile::getInstance($model, "[$i]attribute");
 * </pre>
 * Please note that you must use {@link CUploadedFile::getInstances} for multiple
 * file uploads.
 *
 * When using CFileValidator with an active record, the following code is often used:
 * <pre>
 *  $model->attribute = CUploadedFile::getInstance($model, "attribute");
 *  if($model->save())
 *  {
 *     // single upload
 *     $model->attribute->saveAs($path);
 *     // multiple upload
 *     foreach($model->attribute as $file)
 *        $file->saveAs($path);
 *  }
 * </pre>
 *
 * You can use {@link CFileValidator} to validate the file attribute.
 *
 * In addition to the {@link message} property for setting a custom error message,
 * CFileValidator has a few custom error messages you can set that correspond to different
 * validation scenarios. When the file is too large, you may use the {@link tooLarge} property
 * to define a custom error message. Similarly for {@link tooSmall}, {@link wrongType} and
 * {@link tooMany}. The messages may contain additional placeholders that will be replaced
 * with the actual content. In addition to the "{attribute}" placeholder, recognized by all
 * validators (see {@link CValidator}), CFileValidator allows for the following placeholders
 * to be specified:
 * <ul>
 * <li>{file}: replaced with the name of the file.</li>
 * <li>{limit}: when using {@link tooLarge}, replaced with {@link maxSize};
 * when using {@link tooSmall}, replaced with {@link minSize}; and when using {@link tooMany}
 * replaced with {@link maxFiles}.</li>
 * <li>{extensions}: when using {@link wrongType}, it will be replaced with the allowed extensions.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 * @since 1.0
 */
class CFileValidator extends CValidator
{
	/**
	 * @var boolean whether the attribute requires a file to be uploaded or not.
	 * Defaults to false, meaning a file is required to be uploaded.
	 */
	public $allowEmpty=false;
	/**
	 * @var mixed a list of file name extensions that are allowed to be uploaded.
	 * This can be either an array or a string consisting of file extension names
	 * separated by space or comma (e.g. "gif, jpg").
	 * Extension names are case-insensitive. Defaults to null, meaning all file name
	 * extensions are allowed.
	 */
	public $types;
	/**
	 * @var mixed a list of MIME-types of the file that are allowed to be uploaded.
	 * This can be either an array or a string consisting of MIME-types separated
	 * by space or comma (e.g. "image/gif, image/jpeg"). MIME-types are
	 * case-insensitive. Defaults to null, meaning all MIME-types are allowed.
	 * In order to use this property fileinfo PECL extension should be installed.
	 * @since 1.1.11
	 */
	public $mimeTypes;
	/**
	 * @var integer the minimum number of bytes required for the uploaded file.
	 * Defaults to null, meaning no limit.
	 * @see tooSmall
	 */
	public $minSize;
	/**
	 * @var integer the maximum number of bytes required for the uploaded file.
	 * Defaults to null, meaning no limit.
	 * Note, the size limit is also affected by 'upload_max_filesize' INI setting
	 * and the 'MAX_FILE_SIZE' hidden field value.
	 * @see tooLarge
	 */
	public $maxSize;
	/**
	 * @var string the error message used when the uploaded file is too large.
	 * @see maxSize
	 */
	public $tooLarge;
	/**
	 * @var string the error message used when the uploaded file is too small.
	 * @see minSize
	 */
	public $tooSmall;
	/**
	 * @var string the error message used when the uploaded file has an extension name
	 * that is not listed among {@link types}.
	 */
	public $wrongType;
	/**
	 * @var string the error message used when the uploaded file has a MIME-type
	 * that is not listed among {@link mimeTypes}. In order to use this property
	 * fileinfo PECL extension should be installed.
	 * @since 1.1.11
	 */
	public $wrongMimeType;
	/**
	 * @var integer the maximum file count the given attribute can hold.
	 * It defaults to 1, meaning single file upload. By defining a higher number,
	 * multiple uploads become possible.
	 */
	public $maxFiles=1;
	/**
	 * @var string the error message used if the count of multiple uploads exceeds
	 * limit.
	 */
	public $tooMany;
	/**
	 * @var boolean whether attributes listed with this validator should be considered safe for massive assignment.
	 * For this validator it defaults to false.
	 * @since 1.1.12
	 */
	public $safe=false;

	/**
	 * Set the attribute and then validates using {@link validateFile}.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object, $attribute)
	{
		if($this->maxFiles > 1)
		{
			$files=$object->$attribute;
			if(!is_array($files) || !isset($files[0]) || !$files[0] instanceof CUploadedFile)
				$files = CUploadedFile::getInstances($object, $attribute);
			if(array()===$files)
				return $this->emptyAttribute($object, $attribute);
			if(count($files) > $this->maxFiles)
			{
				$message=$this->tooMany!==null?$this->tooMany : Yii::t('yii', '{attribute} cannot accept more than {limit} files.');
				$this->addError($object, $attribute, $message, array('{attribute}'=>$attribute, '{limit}'=>$this->maxFiles));
			}
			else
				foreach($files as $file)
					$this->validateFile($object, $attribute, $file);
		}
		else
		{
			$file = $object->$attribute;
			if(!$file instanceof CUploadedFile)
			{
				$file = CUploadedFile::getInstance($object, $attribute);
				if(null===$file)
					return $this->emptyAttribute($object, $attribute);
			}
			$this->validateFile($object, $attribute, $file);
		}
	}

	/**
	 * Internally validates a file object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @param CUploadedFile $file uploaded file passed to check against a set of rules
	 * @throws CException if failed to upload the file
	 */
	protected function validateFile($object, $attribute, $file)
	{
		if(null===$file || ($error=$file->getError())==UPLOAD_ERR_NO_FILE)
			return $this->emptyAttribute($object, $attribute);
		elseif($error==UPLOAD_ERR_INI_SIZE || $error==UPLOAD_ERR_FORM_SIZE || $this->maxSize!==null && $file->getSize()>$this->maxSize)
		{
			$message=$this->tooLarge!==null?$this->tooLarge : Yii::t('yii','The file "{file}" is too large. Its size cannot exceed {limit} bytes.');
			$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{limit}'=>$this->getSizeLimit()));
		}
		elseif($error==UPLOAD_ERR_PARTIAL)
			throw new CException(Yii::t('yii','The file "{file}" was only partially uploaded.',array('{file}'=>$file->getName())));
		elseif($error==UPLOAD_ERR_NO_TMP_DIR)
			throw new CException(Yii::t('yii','Missing the temporary folder to store the uploaded file "{file}".',array('{file}'=>$file->getName())));
		elseif($error==UPLOAD_ERR_CANT_WRITE)
			throw new CException(Yii::t('yii','Failed to write the uploaded file "{file}" to disk.',array('{file}'=>$file->getName())));
		elseif(defined('UPLOAD_ERR_EXTENSION') && $error==UPLOAD_ERR_EXTENSION)  // available for PHP 5.2.0 or above
			throw new CException(Yii::t('yii','A PHP extension stopped the file upload.'));

		if($this->minSize!==null && $file->getSize()<$this->minSize)
		{
			$message=$this->tooSmall!==null?$this->tooSmall : Yii::t('yii','The file "{file}" is too small. Its size cannot be smaller than {limit} bytes.');
			$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{limit}'=>$this->minSize));
		}

		if($this->types!==null)
		{
			if(is_string($this->types))
				$types=preg_split('/[\s,]+/',strtolower($this->types),-1,PREG_SPLIT_NO_EMPTY);
			else
				$types=$this->types;
			if(!in_array(strtolower($file->getExtensionName()),$types))
			{
				$message=$this->wrongType!==null?$this->wrongType : Yii::t('yii','The file "{file}" cannot be uploaded. Only files with these extensions are allowed: {extensions}.');
				$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{extensions}'=>implode(', ',$types)));
			}
		}

		if($this->mimeTypes!==null)
		{
			if(function_exists('finfo_open'))
			{
				$mimeType=false;
				if($info=finfo_open(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME))
					$mimeType=finfo_file($info,$file->getTempName());
			}
			elseif(function_exists('mime_content_type'))
				$mimeType=mime_content_type($file->getTempName());
			else
				throw new CException(Yii::t('yii','In order to use MIME-type validation provided by CFileValidator fileinfo PECL extension should be installed.'));

			if(is_string($this->mimeTypes))
				$mimeTypes=preg_split('/[\s,]+/',strtolower($this->mimeTypes),-1,PREG_SPLIT_NO_EMPTY);
			else
				$mimeTypes=$this->mimeTypes;

			if($mimeType===false || !in_array(strtolower($mimeType),$mimeTypes))
			{
				$message=$this->wrongMimeType!==null?$this->wrongMimeType : Yii::t('yii','The file "{file}" cannot be uploaded. Only files of these MIME-types are allowed: {mimeTypes}.');
				$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{mimeTypes}'=>implode(', ',$mimeTypes)));
			}
		}
	}

	/**
	 * Raises an error to inform end user about blank attribute.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function emptyAttribute($object, $attribute)
	{
		if(!$this->allowEmpty)
		{
			$message=$this->message!==null?$this->message : Yii::t('yii','{attribute} cannot be blank.');
			$this->addError($object,$attribute,$message);
		}
	}

	/**
	 * Returns the maximum size allowed for uploaded files.
	 * This is determined based on three factors:
	 * <ul>
	 * <li>'upload_max_filesize' in php.ini</li>
	 * <li>'MAX_FILE_SIZE' hidden field</li>
	 * <li>{@link maxSize}</li>
	 * </ul>
	 *
	 * @return integer the size limit for uploaded files.
	 */
	protected function getSizeLimit()
	{
		$limit=ini_get('upload_max_filesize');
		$limit=$this->sizeToBytes($limit);
		if($this->maxSize!==null && $limit>0 && $this->maxSize<$limit)
			$limit=$this->maxSize;
		if(isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE']>0 && $_POST['MAX_FILE_SIZE']<$limit)
			$limit=$_POST['MAX_FILE_SIZE'];
		return $limit;
	}

	/**
	 * Converts php.ini style size to bytes. Examples of size strings are: 150, 1g, 500k, 5M (size suffix
	 * is case insensitive). If you pass here the number with a fractional part, then everything after
	 * the decimal point will be ignored (php.ini values common behavior). For example 1.5G value would be
	 * treated as 1G and 1073741824 number will be returned as a result. This method is public
	 * (was private before) since 1.1.11.
	 *
	 * @param string $sizeStr the size string to convert.
	 * @return integer the byte count in the given size string.
	 * @since 1.1.11
	 */
	public function sizeToBytes($sizeStr)
	{
		// get the latest character
		switch (strtolower(substr($sizeStr, -1)))
		{
			case 'm': return (int)$sizeStr * 1048576; // 1024 * 1024
			case 'k': return (int)$sizeStr * 1024; // 1024
			case 'g': return (int)$sizeStr * 1073741824; // 1024 * 1024 * 1024
			default: return (int)$sizeStr; // do nothing
		}
	}
}