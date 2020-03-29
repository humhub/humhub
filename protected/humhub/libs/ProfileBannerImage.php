<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use yii\helpers\Html;
use yii\imagine\Image;
use yii\web\UploadedFile;

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
     * @throws \yii\base\Exception
     */
    public function setNew($file)
    {
        if ($file instanceof UploadedFile) {
            $file = $file->tempName;
        }

        $this->delete();

        // Make sure original file is max. 800 width
        $image = Image::getImagine()->open($file);
        if ($image->getSize()->getWidth() > 2000) {
            $image->resize($image->getSize()->widen(2000));
        }
        $image->save($this->getPath('_org'), ['format' => 'jpg']);

        // Create version
        $image->thumbnail(new Box($this->width, $this->height), ManipulatorInterface::THUMBNAIL_OUTBOUND)
            ->save($this->getPath(''));
    }

    /**
     * @inheritDoc
     */
    public function render($width = 32, $cfg = [])
    {
        if(is_int($width)) {
            $width .= 'px';
        }

        Html::addCssStyle($cfg, ['width' => $width]);
        return Html::img($this->getUrl(),$cfg);
    }
}
