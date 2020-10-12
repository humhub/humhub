<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web\pwa\widgets;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\ui\view\components\View;
use Imagine\Image\Box;
use Yii;
use humhub\components\Widget;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\imagine\Image;
use yii\web\UploadedFile;


/**
 * Class SiteIcon handles icons of the installation.
 * Those icons will be used to represent the application in components such as mobile apps, bookmarks, etc.
 *
 * @since 1.4
 * @package humhub\modules\ui\widgets
 */
class SiteIcon extends Widget
{
    /**
     * Sets a new icon for the installation.
     *
     * @param UploadedFile|null $file
     */
    public static function set(UploadedFile $file = null)
    {
        static::deleteFiles();

        if ($file !== null) {
            try {
                FileHelper::createDirectory(Yii::getAlias('@webroot/uploads/icon/'));
            } catch (Exception $e) {
            }
            Image::getImagine()->open($file->tempName)->save(Yii::getAlias('@webroot/uploads/icon/icon.png'));
        }
    }

    /**
     * Returns the URL of the icon in desired size (width + height)
     *
     * @param int $size in px
     * @param bool $autoResize automatically resize to given size if not available yet
     * @return string|null
     */
    public static function getUrl($size, $autoResize = true)
    {
        $manualUploadedFile = Yii::getAlias('@webroot/uploads/icon/' . static::buildFileName($size));
        if(file_exists($manualUploadedFile)) {
            return Yii::getAlias('@web/uploads/icon/' . static::buildFileName($size)) . '?v=' . filemtime($manualUploadedFile);
        }

        $file = self::getFile($size);
        if (file_exists($file)) {
            return Yii::getAlias(Yii::$app->assetManager->baseUrl) . '/siteicons/' . static::buildFileName($size) . '?v=' . filemtime($file);
        } elseif ($autoResize) {
            $baseIcon = static::getOriginalFile();
            if (!file_exists($baseIcon)) {
                $baseIcon = Yii::$app->getModule('web')->getBasePath() . '/pwa/resources/default_icon.png';
            }
            try {
                FileHelper::createDirectory(Yii::getAlias(Yii::$app->assetManager->basePath . DIRECTORY_SEPARATOR . 'siteicons'));
            } catch (Exception $e) {
                // Directory already exists
            }

            try {
                Image::getImagine()->open($baseIcon)->resize(new Box($size, $size))->save($file);
            } catch (\Exception $ex) {
                Yii::error('Could not resize site icon: ' . $ex->getMessage());
            }
            return static::getUrl($size, false);
        }

        return null;
    }

    public static function hasImage()
    {
        return file_exists(static::getOriginalFile());
    }

    private static function getFile($size = null)
    {
        return Yii::getAlias(Yii::$app->assetManager->basePath . DIRECTORY_SEPARATOR . 'siteicons' . DIRECTORY_SEPARATOR . static::buildFileName($size));
    }


    private static function buildFileName($size = null)
    {
        $fileName = ($size === null) ? 'icon.png' : $size . 'x' . $size . '.png';
        return $fileName;
    }

    private static function getOriginalFile()
    {
        return Yii::getAlias('@webroot/uploads/icon/icon.png');
    }

    private static function deleteFiles()
    {
        // Delete assets folder if exists
        try {
            FileHelper::removeDirectory(Yii::getAlias(Yii::$app->assetManager->basePath . DIRECTORY_SEPARATOR . 'siteicons'));
        } catch (ErrorException $e) {
            Yii::error($e, 'admin');
        }

        // Delete uploads folder if exists
        try {
            FileHelper::removeDirectory(Yii::getAlias('@webroot/uploads/icon/'));
        } catch (ErrorException $e) {
            Yii::error($e, 'admin');
        }
    }

    /**
     * @param View $view
     */
    public static function registerMetaTags(View $view)
    {
        // Add Apple touch icons
        // https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html
        $view->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => static::getUrl(152), 'sizes' => '152x152']);
        $view->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => static::getUrl(180), 'sizes' => '180x180']);
        $view->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => static::getUrl(167), 'sizes' => '167x167']);

        // Chrome, Firefox & Co.
        $view->registerLinkTag(['rel' => 'icon', 'href' => static::getUrl(192), 'sizes' => '192x192']);
        $view->registerLinkTag(['rel' => 'icon', 'href' => static::getUrl(96), 'sizes' => '96x96']);
        $view->registerLinkTag(['rel' => 'icon', 'href' => static::getUrl(32), 'sizes' => '32x32']);
    }
}
