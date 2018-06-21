<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\modules\file\libs\ImageConverter;
use yii\helpers\FileHelper;

/**
 * ProfileBannerImage is responsible for the profile banner images.
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
class ProfileBannerImage extends ProfileImage
{

    /**
     * @var Integer width of the Image
     */
    protected $width = 1134;

    /**
     * @var Integer height of the Image
     */
    protected $height = 192;

    /**
     * @var String folder name inside the uploads directory
     */
    protected $folder_images = 'profile_image/banner';


    /**
     * Constructor of Profile Image
     *
     * UserId is optional, if not given the current user will used
     *
     * @param string $guid
     * @param string $defaultImage
     */
    public function __construct($guid, $defaultImage = 'default_banner')
    {
        parent::__construct($guid, $defaultImage);
    }

    /**
     * Sets a new profile image by given temp file
     *
     * @param \yii\web\UploadedFile $file
     */
    public function setNew($file)
    {
        $this->delete();
        ImageConverter::TransformToJpeg($file->tempName, $this->getPath('_org'));
        ImageConverter::Resize($this->getPath('_org'), $this->getPath('_org'), ['width' => 1134, 'mode' => 'max']);
        ImageConverter::Resize($this->getPath('_org'), $this->getPath(''), ['width' => $this->width, 'height' => $this->height]);
    }
}
