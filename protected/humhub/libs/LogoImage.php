<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\imagine\Image;
use yii\web\UploadedFile;
use yii\helpers\Url;
use yii\helpers\FileHelper;

/**
 * LogoImage
 */
class LogoImage
{
    /**
     * @var Integer height of the image
     */
    protected $height = 40;

    /**
     * @var String folder name inside the uploads directory
     */
    protected $folder_images = 'logo_image';

    /**
     * Returns the URl of Logo Image
     *
     * @return String Url of the profile image
     * @throws \yii\base\Exception
     */
    public function getUrl()
    {
        // Workaround for absolute urls in console applications (Cron)
        if (Yii::$app->request->isConsoleRequest) {
            $path = Url::base(true);
        } else {
            $path = Url::base();
        }

        if (file_exists($this->getPath())) {
            $path .= '/uploads/' . $this->folder_images . '/logo.png';
            $cacheId = '?v=' . filemtime($this->getPath());
        }

        return $path;
    }

    /**
     * Indicates there is a logo image
     *
     * @return Boolean is there a logo image
     * @throws \yii\base\Exception
     */
    public function hasImage()
    {
        return file_exists($this->getPath());
    }

    /**
     * Returns the Path of the logo image
     *
     * @return String Path to the logo image
     * @throws \yii\base\Exception
     */
    public function getPath()
    {
        $path = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $this->folder_images . DIRECTORY_SEPARATOR;
        FileHelper::createDirectory($path);
        $path .= 'logo.png';

        return $path;
    }

    /**
     * Sets a new logo image by given temp file
     *
     * @param UploadedFile $file
     * @throws \yii\base\Exception
     */
    public function setNew(UploadedFile $file)
    {
        $this->delete();
        $image = Image::getImagine()->open($file->tempName);

        if ($image->getSize()->getHeight() > $this->height) {
            $image->resize($image->getSize()->heighten($this->height));
        }
        $image->save($this->getPath());
    }

    /**
     * Deletes current logo
     */
    public function delete()
    {
        $path = $this->getPath();
        if (file_exists($path)) {
            FileHelper::unlink($this->getPath());
        }
    }
}
