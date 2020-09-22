<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
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
     * Sets a new icon for the installation.
     * @param UploadedFile|null $file
     */
    public static function set(UploadedFile $file = null)
    {
        static::deleteFiles();

        if ($file !== null) {
            try {
                FileHelper::createDirectory(Yii::getAlias('@webroot/uploads/logo_image/'));
            } catch (Exception $e) {
            }
            Image::getImagine()->open($file->tempName)->save(Yii::getAlias('@webroot/uploads/logo_image/logo.png'));
        }
    }

    /**
     * Returns the URL of the logo image in desired maximum sizes (width and/or height)
     *
     * @param int|null $maxWidth the maximum width in pixel
     * @param int|null $maxHeight the maximum width in pixel
     * @param bool $autoResize automatically resize to given size if not available yet
     * @return string|null
     */
    public static function getUrl($maxWidth = null, $maxHeight = null, $autoResize = true)
    {
        if ($maxWidth === null) {
            // Will change in future!
            $maxWidth = 300;
        }

        if ($maxHeight === null) {
            // Will change in future!
            $maxHeight = 40;
        }

        $file = self::getFile($maxWidth, $maxHeight);
        if (file_exists($file)) {
            // Workaround for absolute urls in console applications (Cron)
            $base = '';
            if (Yii::$app->request->isConsoleRequest) {
                $base = Url::base(true);
            }
            return $base . Yii::getAlias(Yii::$app->assetManager->baseUrl) . '/logo/' . static::buildFileName($maxWidth, $maxHeight) . '?v=' . filemtime($file);
        } elseif (static::hasImage() && $autoResize) {
            try {
                FileHelper::createDirectory(Yii::getAlias(Yii::$app->assetManager->basePath . DIRECTORY_SEPARATOR . 'logo'));
            } catch (Exception $e) {
            }

            $image = Image::getImagine()->open(static::getOriginalFile());
            if ($image->getSize()->getHeight() > $maxHeight) {
                $image->resize($image->getSize()->heighten($maxHeight));
            }
            if ($image->getSize()->getWidth() > $maxWidth) {
                $image->resize($image->getSize()->widen($maxWidth));
            }
            $image->save($file);

            return static::getUrl($maxWidth, $maxHeight, false);
        }

        return null;
    }

    /**
     * Indicates there is a logo image
     *
     * @return Boolean is there a logo image
     */
    public static function hasImage()
    {
        return file_exists(static::getOriginalFile());
    }

    private static function getFile($maxWidth, $maxHeight)
    {
        return Yii::getAlias(Yii::$app->assetManager->basePath . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . static::buildFileName($maxWidth, $maxHeight));
    }

    private static function buildFileName($maxWidth, $maxHeight)
    {
        $fileName = $maxWidth . 'x' . $maxHeight . '.png';
        return $fileName;
    }

    private static function getOriginalFile()
    {
        return Yii::getAlias('@webroot/uploads/logo_image/logo.png');
    }

    private static function deleteFiles()
    {
        // Delete assets folder if exists
        try {
            \humhub\modules\file\libs\FileHelper::removeDirectory(Yii::getAlias(Yii::$app->assetManager->basePath . DIRECTORY_SEPARATOR . 'logo'));
        } catch (ErrorException $e) {
            Yii::error($e, 'admin');
        }

        // Delete uploads folder if exists
        try {
            FileHelper::removeDirectory(Yii::getAlias('@webroot/uploads/logo_image/'));
        } catch (ErrorException $e) {
            Yii::error($e, 'admin');
        }
    }

}
