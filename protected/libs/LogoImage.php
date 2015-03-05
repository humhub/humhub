<?php

class LogoImage
{
    
    /**
     * @var Integer height of the image
     */
    protected $height = 40;

    /**
     * @var String folder name inside the uploads directory
     */
    protected $folder_images = "logo_image";

    protected $file_type = 'jpg';

    public function __construct()
    {
    }

    /**
     * Returns the URl of Logo Image
     *
     * @return String Url of the profile image
     */
    public function getUrl()
    {
        $cacheId = 0;
        $path = "";

        // Workaround for absolute urls in console applications (Cron)
        if (Yii::app() instanceof CConsoleApplication) {
            $path = Yii::app()->request->getBaseUrl();
        } else {
            $path = Yii::app()->getBaseUrl(true);
        }


        if (file_exists($this->getPath())) {
            $path .= '/uploads/' . $this->folder_images . '/logo.'.$this->file_type;
        }
        $path .= '?cacheId=' . $cacheId;
        return $path;
    }

    /**
     * Indicates there is a logo image
     *
     * @return Boolean is there a logo image
     */
    public function hasImage()
    {
        return file_exists($this->getPath());
    }

    /**
     * Returns the Path of the logo image
     *
     * @return String Path to the logo image
     */
    public function getPath()
    {
        $path = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $this->folder_images . DIRECTORY_SEPARATOR;

        if (!is_dir($path))
            mkdir($path);

        $path .= 'logo.'.$this->file_type;

        return $path;
    }


    /**
     * Sets a new logo image by given temp file
     *
     * @param mixed $file CUploadedFile or file path
     */
    public function setNew($file)
    {
        $file_type = $this->file_type;
        
        if ($file instanceof CUploadedFile) {
            $file_type = $file->getExtensionName();
            $file = $file->getTempName();
        }

        $this->delete();
        move_uploaded_file($file,  $this->getPath());

        ImageConverter::Resize($this->getPath(), $this->getPath(), array('height' => $this->height, 'width' => 0, 'mode' => 'max', 'transparent' => ($file_type == 'png' && ImageConverter::checkTransparent($this->getPath()))));
    }

    /**
     * Deletes current logo
     */
    public function delete()
    {
        @unlink($this->getPath());
    }

}

?>
