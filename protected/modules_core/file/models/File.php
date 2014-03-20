<?php

/**
 * This is the model class for table "file".
 *
 * The followings are the available columns in table 'file':
 * @property integer $id
 * @property string $guid
 * @property string $file_name
 * @property string $title
 * @property string $mime_type
 * @property string $size
 * @property string $object_model
 * @property integer $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.file.models
 * @since 0.5
 */
class File extends HActiveRecord {

    // Configuration
    protected $folder_uploads = "file";

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return File the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'file';
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors() {
        return array(
            'HUnderlyingObjectBehavior' => array(
                'class' => 'application.behaviors.HUnderlyingObjectBehavior',
            ),
        );
    }

    /**
     * Deletes the file
     */
    public function delete() {

        $path = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $this->folder_uploads . DIRECTORY_SEPARATOR . $this->guid . DIRECTORY_SEPARATOR;

        // Become really sure, that we dont delete something else :-)
        if ($this->guid != "" && $this->folder_uploads != "" && is_dir($path)) {

            $files = glob($path . "*"); // get all file names
            foreach ($files as $file) { // iterate files
                if (is_file($file))
                    unlink($file); // delete file
            }

            rmdir($path);
        }

        parent::delete();
    }

    /**
     * Before Save Addons
     *
     * @return type
     */
    protected function beforeSave() {

        if ($this->isNewRecord) {

            // Create GUID for new files
            $this->guid = UUID::v4();
        }

        return parent::beforeSave();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('guid, size', 'length', 'max' => 45),
            array('mime_type', 'length', 'max' => 150),
            array('mime_type', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9\.ä\/\-]/', 'message' => Yii::t('FileModule.base', 'Invalid Mime-Type')),
            array('file_name, title', 'length', 'max' => 255),
            array('created_at, updated_at', 'safe'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('base', 'ID'),
            'guid' => Yii::t('base', 'Guid'),
            'file_name' => Yii::t('FileModule.base', 'File name'),
            'title' => Yii::t('FileModule.base', 'Title'),
            'mime_type' => Yii::t('FileModule.base', 'Mime Type'),
            'size' => Yii::t('FileModule.base', 'Size'),
            'created_at' => Yii::t('base', 'Created at'),
            'created_by' => Yii::t('base', 'Created By'),
            'updated_at' => Yii::t('base', 'Updated at'),
            'updated_by' => Yii::t('base', 'Updated by'),
        );
    }

    public function save($runValidation = true, $attributes = null) {

        if (!self::HasValidExtension($this->file_name))
            return false;

        return parent::save($runValidation, $attributes);
    }

    /**
     * Saves given CUploadedFiles
     *
     */
    public static function store(CUploadedFile $cUploadedFile) {


        // Santize Filename
        $filename = $cUploadedFile->getName();
        $filename = trim($filename);

        $filename = preg_replace("/[^a-z0-9_\-s\.]/i", "", $filename);
        $pathInfo = pathinfo($filename);
        if (strlen($pathInfo['filename']) > 60) {
            $pathInfo['filename'] = substr($pathInfo['filename'], 0, 60);
        }


        $filename = $pathInfo['filename'];
        if (isset($pathInfo['extension']))
            $filename .= "." . $pathInfo['extension'];

        $file = new File();
        if (!self::HasValidExtension($filename))
            return false;

        $file->file_name = $filename;

        $file->title = $cUploadedFile->getName();
        $file->mime_type = $cUploadedFile->getType();

        #$file->size = $cUploadedFile->getSize();

        if ($file->save()) {

            // Add File to Filebase
            $file->slurp($cUploadedFile->getTempName());

            return $file;
        } else {
            return;
        }
    }

    /**
     * Returns the Path of the File
     */
    public function getPath($prefix = "") {

        $path = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $this->folder_uploads . DIRECTORY_SEPARATOR . $this->guid . DIRECTORY_SEPARATOR;

        if (!is_dir($path))
            mkdir($path);

        $path .= $this->getFilename($prefix);

        return $path;
    }

    /**
     * Returns the Url of the File
     */
    public function getUrl($suffix = "") {

        $params = array();
        $params['guid'] = $this->guid;
        if ($suffix) {
            $params['suffix'] = $suffix;
        }


        return Yii::app()->getController()->createAbsoluteUrl('//file/file/download', $params);
    }

    /**
     * Returns Filename
     */
    public function getFilename($prefix = "") {

        // without prefix
        if ($prefix == "")
            return $this->file_name;

        $fileParts = pathinfo($this->file_name);

        return $fileParts['filename'] . "_" . $prefix . "." . $fileParts['extension'];
    }

    public function getMimeBaseType() {

        #Yii::log($msg, CLogger::LEVEL_INFO, 'ext.yii-mail.YiiMail'); // TODO: attempt to determine alias/category at runtime

        if ($this->mime_type != "") {
            list($baseType, $subType) = explode('/', $this->mime_type);
            return $baseType;
        }

        return "";
    }

    public function getMimeSubType() {

        #Yii::log($msg, CLogger::LEVEL_INFO, 'ext.yii-mail.YiiMail'); // TODO: attempt to determine alias/category at runtime

        if ($this->mime_type != "") {
            list($baseType, $subType) = explode('/', $this->mime_type);
            return $subType;
        }

        return "";
    }

    public function getPreviewImageUrl($maxWidth = 1000, $maxHeight = 1000) {

        $prefix = 'pi_' . $maxWidth . "x" . $maxHeight;

        // already generated
        if (is_file($this->getPath($prefix)))
            return $this->getUrl($prefix);


        if ($this->getMimeBaseType() != "image") {
            return "";
        }

        if (!is_file($this->getPath()))
            return "";

        $imageInfo = getimagesize($this->getPath());

        // Check if we got any dimensions
        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return "";
        }

        // Check if image type is supported
        if ($imageInfo[2] != IMAGETYPE_PNG && $imageInfo[2] != IMAGETYPE_JPEG && $imageInfo[2] != IMAGETYPE_GIF) {
            return "";
        }

        ImageConverter::Resize($this->getPath(), $this->getPath($prefix), array('mode' => 'max', 'width' => $maxWidth, 'height' => $maxHeight));
        return $this->getUrl($prefix);
    }

    /**
     * Store given filename into file record.
     *
     * @param type $tmpName
     */
    public function slurp($tmpName) {


        #CFileHelper::getMimeType
        if ($this->guid == "") {
            throw new CException("Could not use slurp on unsaved records!");
        }

        $this->size = filesize($tmpName);

        #Dont work on Temp Files
        #move_uploaded_file($tmpName, $this->getPath());

        rename($tmpName, $this->getPath());

        @chmod($this->getPath(), 0744);

        //$this->mime_type = CFileHelper::getMimeType($this->getPath());
        #$this->size = filesize($this->getPath());
        $this->save();

        #print "slurped";
    }

    /**
     * Store given content into file record.
     *
     * @param String $tmpName
     */
    public function slurpContent($content) {
        if ($this->guid == "") {
            throw new CException("Could not use slurp on unsaved records!");
        }
        file_put_contents($this->getPath(), $content);
        @chmod($this->getPath(), 0744);

        $this->size = filesize($this->getPath());
        $this->save();
    }

    /**
     * Returns Stylesheet Classname based on file extension
     *
     * @return string CSS Class
     */
    public function getMimeIconClass() {
        $fileParts = pathinfo($this->file_name);

        // Word
        if ($fileParts['extension'] == 'doc' || $fileParts['extension'] == 'docx') {
            return "mime-word";
            // Excel
        } else if ($fileParts['extension'] == 'xls' || $fileParts['extension'] == 'xlsx') {
            return "mime-excel";
            // Powerpoint
        } else if ($fileParts['extension'] == 'ppt' || $fileParts['extension'] == 'pptx') {
            return "mime-excel";
            // PDF
        } else if ($fileParts['extension'] == 'pdf') {
            return "mime-pdf";
            // Archive
        } else if ($fileParts['extension'] == 'zip' || $fileParts['extension'] == 'rar' || $fileParts['extension'] == 'tar' || $fileParts['extension'] == '7z') {
            return "mime-zip";
            // Audio
        } else if ($fileParts['extension'] == 'jpg' || $fileParts['extension'] == 'jpeg' || $fileParts['extension'] == 'png' || $fileParts['extension'] == 'gif') {
            return "mime-image";
            // Audio
        } else if ($fileParts['extension'] == 'mp3' || $fileParts['extension'] == 'aiff' || $fileParts['extension'] == 'wav') {
            return "mime-audio";
            // Adobe Flash
        } else if ($fileParts['extension'] == 'swf' || $fileParts['extension'] == 'fla' || $fileParts['extension'] == 'air') {
            return "mime-flash";
            // Adobe Photoshop
        } else if ($fileParts['extension'] == 'psd') {
            return "mime-photoshop";
            // Adobe Illustrator
        } else if ($fileParts['extension'] == 'ai') {
            return "mime-illustrator";
            // other file formats
        } else {
            return "mime-file";
        }
    }

    /**
     * Checks a given Filename if the extension is allowed
     *
     * @param type $fileName
     */
    public static function HasValidExtension($fileName) {

        $fileParts = pathinfo($fileName);
        $extension = trim(strtolower($fileParts['extension']));


        $invalid = array_map('trim', explode(",", HSetting::Get('forbiddenExtensions', 'file')));

        if (in_array($extension, $invalid))
            return false;

        return true;
    }

    /**
     * Checks if given file can read
     * Only permissions on Content or ContentAddons will be checked atm.
     */
    public function canRead($userId = null) {
        if ($userId == "")
            $userId = Yii::app()->user->id;


        // IS Content
        if (is_subclass_of($this->getUnderlyingObject(), 'HActiveRecordContent')) {
            if (!$this->getUnderlyingObject()->content->canRead($userId))
                return false;

            // Is ContentAddon
        } elseif (is_subclass_of($this->getUnderlyingObject(), 'HActiveRecordContentAddon')) {
            if (!$this->getUnderlyingObject()->getContentObject()->content->canRead($userId))
                return false;

            // We dont know on which object this file hangs, so allow file downloading
        } else {
            return true;
        }

        return true;
    }

    /**
     * Attaches a given list of files to an existing content object.
     *
     * @param Mixed $content is a HActiveRecordContent or Content Instance
     * @param String $files is a comma seperated list of uploaded file guids
     */
    public static function attachToContent($content, $files) {

        if (!$content instanceof HActiveRecordContent && !$content instanceof Content) {
            throw new CException("Invalid content object given!");
        }

        // Attach Files
        foreach (explode(",", $files) as $fileGuid) {

            $file = File::model()->findByAttributes(array('guid' => trim($fileGuid)));

            // Dont allow file overtaking (ensure object_model is null)
            if ($file != null && $file->object_model == "") {

                if ($content instanceof HActiveRecordContent) {
                    $file->object_model = get_class($content);
                    $file->object_id = $content->getPrimaryKey();
                } else {
                    $file->object_model = $content->object_model;
                    $file->object_id = $content->object_id;
                }

                $file->save();
            }
        }
    }

    /**
     * Attaches files by url which found in content text.
     * This is experimental and only supports image files at the moment.
     *
     * @param HActiveRecordContent $content
     * @param String $text
     */
    public static function attachFilesByUrlsToContent($content, $text) {

        if (!$content instanceof HActiveRecordContent) {
            throw new CException("Invalid content object given!");
        }

        $max = 5;
        $count = 1;

        $text = preg_replace_callback('/http(.*?)(\s|$)/i', function($match) use (&$count, &$max, &$content) {

            if ($max > $count) {

                $url = $match[0];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_HEADER, true);

                $ret = curl_exec($ch);
                $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                list($headers, $outputContent) = explode("\r\n\r\n", $ret, 2);
                curl_close($ch);

                if ($httpCode == 200 && substr($contentType, 0, 6) == 'image/') {

                    $extension = 'img';
                    if ($contentType == 'image/jpeg' || $contentType == 'image/jpg')
                        $extension = 'jpg';
                    elseif ($contentType == 'image/gif')
                        $extension = 'gif';
                    elseif ($contentType == 'image/png')
                        $extension = 'png';

                    $file = new File();
                    $file->object_model = get_class($content);
                    $file->mime_type = $contentType;
                    $file->title = "Link Image";
                    $file->file_name = "LinkImage." . $extension;
                    $file->object_id = $content->getPrimaryKey();
                    if ($file->save()) {
                        $file->slurpContent($outputContent);
                    }
                }
            }
            $count++;
        }, $text);
    }

}
