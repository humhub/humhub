<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\user\widgets\Image as UserImage;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use yii\web\UploadedFile;

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
    protected $guid = '';

    /**
     * @var ContentContainerActiveRecord
     */
    protected $container;

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
    protected $folder_images = 'profile_image';

    /**
     * @var String name of the default image
     */
    protected $defaultImage;

    /**
     * Constructor of Profile Image
     *
     * UserId is optional, if not given the current user will used
     *
     * @param string $guid
     * @param string $defaultImage
     */
    public function __construct($guid, $defaultImage = 'default_user')
    {
        if ($guid instanceof ContentContainerActiveRecord) {
            $this->container = $guid;
            $this->guid = $this->container->guid;
        } else {
            $this->guid = $guid;
        }
        $this->defaultImage = $defaultImage;
    }

    /**
     * Returns the URl of the Modified Profile Image
     *
     * @param String $prefix Prefix of the returned image
     * @param boolean $scheme URL Scheme
     * @return String Url of the profile image
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function getUrl($prefix = '', $scheme = false)
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
     * @throws \yii\base\Exception
     */
    public function hasImage()
    {
        return file_exists($this->getPath('_org'));
    }

    /**
     * Returns the Path of the Modified Profile Image
     *
     * @param String $prefix for the profile image
     * @return String Path to the profile image
     * @throws \yii\base\Exception
     */
    public function getPath($prefix = '')
    {
        $path = Yii::getAlias('@webroot/uploads/' . $this->folder_images . '/');

        FileHelper::createDirectory($path);

        $path .= $this->guid;
        $path .= $prefix;
        $path .= '.jpg';

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
     * @throws \yii\base\Exception
     */
    public function cropOriginal($x, $y, $h, $w)
    {
        $image = Image::getImagine()->open($this->getPath('_org'))
            ->crop(new Point($x, $y), new Box($w, $h));

        $image->resize($image->getSize()->heighten($this->height))
            ->resize($image->getSize()->widen($this->width))
            ->save($this->getPath());
    }

    /**
     * Sets a new profile image by given temp file
     *
     * @param mixed $file CUploadedFile or file path
     * @throws Exception
     */
    public function setNew($file)
    {
        if ($file instanceof UploadedFile) {
            $file = $file->tempName;
        }

        ImageHelper::checkMaxDimensions($file);

        $this->delete();

        // Convert image to uploaded JPEG, fix orientation and remove additional meta information
        $image = Image::getImagine()->open($file);
        ImageHelper::fixJpegOrientation($image, $file);
        $image->thumbnail($image->getSize())
            ->save($this->getPath('_org'), ['format' => 'jpg']);

        // Make sure original file is max. 800 width
        $image = Image::getImagine()->open($this->getPath('_org'));
        if ($image->getSize()->getWidth() > 800) {
            $image->resize($image->getSize()->widen(800));
        }
        $image->save($this->getPath('_org'), ['format' => 'jpg']);

        // Create squared version
        $image->thumbnail(new Box($this->width, $this->height), ManipulatorInterface::THUMBNAIL_OUTBOUND)
            ->save($this->getPath(''));
    }

    /** 1
     * Deletes current profile
     */
    public function delete()
    {
        if (file_exists(($this->getPath()))) {
            FileHelper::unlink($this->getPath());
        }
        if (file_exists(($this->getPath('_org')))) {
            FileHelper::unlink($this->getPath('_org'));
        }
        if (file_exists(($this->getPath('_cropped')))) {
            FileHelper::unlink($this->getPath('_cropped'));
        }
    }

    /**
     * @return ContentContainerActiveRecord|string
     * @throws \yii\db\IntegrityException
     * @since 1.4
     */
    public function getContainer()
    {
        if (!$this->container) {
            $this->container = ContentContainer::findRecord([$this->guid]);
        }

        return $this->container;
    }

    /**
     * Renders this profile image
     * @param int $width
     * @param array $cfg
     * @return string
     * @throws \yii\db\IntegrityException
     * @since 1.4
     */
    public function render($width = 32, $cfg = [])
    {
        $container = $this->getContainer();

        if (!$container) {
            return '';
        }

        $cfg['width'] = $width;
        $widgetOptions = ['width' => $width];

        // TODO: improve option handling...
        if (isset($cfg['link'])) {
            $widgetOptions['link'] = $cfg['link'];
            unset($cfg['link']);
        }

        if (isset($cfg['showTooltip'])) {
            $widgetOptions['showTooltip'] = $cfg['showTooltip'];
            unset($cfg['showTooltip']);
        }

        if (isset($cfg['tooltipText'])) {
            $widgetOptions['tooltipText'] = $cfg['tooltipText'];
            unset($cfg['tooltipText']);
        }

        if ($container instanceof Space) {
            $widgetOptions['space'] = $container;
            $widgetOptions['htmlOptions'] = $cfg;
            return SpaceImage::widget($widgetOptions);
        }


        $htmlOptions = [];

        if (isset($cfg['htmlOptions'])) {
            $htmlOptions = $cfg['htmlOptions'];
            unset($cfg['htmlOptions']);
        }

        $widgetOptions['user'] = $container;
        $widgetOptions['imageOptions'] = $cfg;
        $widgetOptions['htmlOptions'] = $htmlOptions;

        return UserImage::widget($widgetOptions);
    }

    /**
     * Get width
     *
     * @return int
     */
    public function width()
    {
        return $this->width;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function height()
    {
        return $this->height;
    }

    /**
     * Get aspect ratio
     *
     * @return float
     */
    public function getAspectRatio()
    {
        return $this->width() / $this->height();
    }
}
