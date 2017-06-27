<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\helpers\Url;
use humhub\modules\file\libs\ImageConverter;

/**
 * ProfileImage is responsible for all profile images.
 *
 * This class handles all tasks related to profile images.
 * Will used for Space or User Profiles.
 *
 * Prefixes:
 *  "" = Resized profile image
 *  "_org" = Orginal uploaded file
 *
 * @since 0.5
 * @author Luke
 */
class ProfileImage
{

    /**
     * @var String is the guid of user or space
     */
    protected $guid = "";

    /**
     * @var Integer width of the Image
     */
    protected $width = 150;

    /**
     * @var Integer height of the Image
     */
    protected $height = 150;

    /**
     * @var String folder name inside the uploads directory
     */
    protected $folder_images = "profile_image";

    /**
     * @var String name of the default image
     */
    protected $defaultImage;

    /**
     * Constructor of Profile Image
     *
     * UserId is optional, if not given the current user will used
     *
     * @param type $guid
     */
    public function __construct($guid, $defaultImage = 'default_user')
    {
        $this->guid = $guid;
        $this->defaultImage = $defaultImage;
    }

    /**
     * Returns the URl of the Modified Profile Image
     *
     * @param String $prefix Prefix of the returned image
     * @param boolean $scheme URL Scheme
     * @return String Url of the profile image
     */
    public function getUrl($prefix = "", $scheme = false)
    {
        if (file_exists($this->getPath($prefix))) {
            $path = '@web/uploads/' . $this->folder_images . '/';
            $path .= $this->guid . $prefix;
            $path .= '.jpg?m=' . filemtime($this->getPath($prefix));
        } else {
            $path = '@web-static/img/' . $this->defaultImage;
            $path .= '.jpg';
            $path = Yii::$app->view->theme->applyTo($path);
        }

        return Url::to(Yii::getAlias($path), $scheme);
    }

    /**
     * Indicates there is a custom profile image
     *
     * @return Boolean is there a profile image
     */
    public function hasImage()
    {
        return file_exists($this->getPath("_org"));
    }

    /**
     * Returns the Path of the Modified Profile Image
     *
     * @param String $prefix for the profile image
     * @return String Path to the profile image
     */
    public function getPath($prefix = "")
    {
        $path = Yii::getAlias('@webroot/uploads/' . $this->folder_images . '/');

        if (!is_dir($path))
            mkdir($path);

        $path .= $this->guid;
        $path .= $prefix;
        $path .= ".jpg";

        return $path;
    }

    /**
     * Crops the Original Image
     *
     * @param Int $x
     * @param Int $y
     * @param Int $h
     * @param Int $w
     * @return boolean indicates the success
     */
    public function cropOriginal($x, $y, $h, $w)
    {
        $image = imagecreatefromjpeg($this->getPath('_org'));

        // Create new destination Image
        $destImage = imagecreatetruecolor($this->width, $this->height);

        if (!imagecopyresampled($destImage, $image, 0, 0, $x, $y, $this->width, $this->height, $w, $h)) {
            return false;
        }

        unlink($this->getPath(''));
        imagejpeg($destImage, $this->getPath(''), 100);
    }

    /**
     * Sets a new profile image by given temp file
     *
     * @param mixed $file CUploadedFile or file path
     */
    public function setNew($file)
    {
        if ($file instanceof \yii\web\UploadedFile) {
            $file = $file->tempName;
        }

        $this->delete();
        ImageConverter::TransformToJpeg($file, $this->getPath('_org'));
        ImageConverter::Resize($this->getPath('_org'), $this->getPath('_org'), ['width' => 800, 'mode' => 'max']);
        ImageConverter::Resize($this->getPath('_org'), $this->getPath(''), ['width' => $this->width, 'height' => $this->height]);
    }

    /**
     * Deletes current profile
     */
    public function delete()
    {
        @unlink($this->getPath());
        @unlink($this->getPath('_org'));
    }

}
