<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web\pwa\widgets;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\ui\view\components\View;
use humhub\modules\web\Module;
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

    protected static $iconFolderPath = '@webroot/uploads/icon';
    protected static $iconFolderUrl = '@web/uploads/icon';

    /**
     * Sets a new icon for the installation.
     *
     * @param UploadedFile|null $file
     */
    public static function set(UploadedFile $file = null)
    {
        static::setNewFile(($file !== null) ? $file->tempName : null);
    }

    /**
     * Returns the URL of the icon in desired size (width + height)
     *
     * @param int|null $size if size is empty the original file will be returned
     * @param bool $autoResize automatically resize to given size if not available yet
     * @return string
     */
    public static function getUrl($size = null, $autoResize = true)
    {
        $fileName = static::getFileName($size);
        $file = Yii::getAlias(static::$iconFolderPath) . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($file)) {
            return Yii::getAlias(static::$iconFolderUrl) . '/' . $fileName;
        }

        if ($autoResize) {
            $originalFile = Yii::getAlias(static::$iconFolderPath) . '/' . static::getFileName();

            if (file_exists($originalFile)) {
                Image::getImagine()
                    ->open($originalFile)
                    ->resize(new Box($size, $size))
                    ->save($file);

                return static::getUrl($size, false);
            }
        }

        return null;
    }


    public static function getPath($size = null)
    {
        return Yii::getAlias(static::$iconFolderPath . DIRECTORY_SEPARATOR . static::getFileName($size));
    }


    private static function getFileName($size = null)
    {
        $fileName = ($size === null) ? 'icon.png' : $size . 'x' . $size . '.png';
        return $fileName;
    }

    private static function setNewFile($fileName = null)
    {
        try {
            FileHelper::removeDirectory(Yii::getAlias(static::$iconFolderPath));
        } catch (ErrorException $e) {
            Yii::error($e, 'admin');
        }

        try {
            FileHelper::createDirectory(Yii::getAlias(static::$iconFolderPath));
        } catch (Exception $e) {
            Yii::error($e, 'admin');
        }

        if ($fileName !== null && is_file($fileName)) {
            Image::getImagine()
                ->open($fileName)
                ->save(Yii::getAlias(static::$iconFolderPath) . '/icon.png');
        }
    }

    private static function setDefaultIcon()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('web');

        static::setNewFile($module->getBasePath() . '/pwa/resources/default_icon.png');
    }


    /**
     * @param View $view
     */
    public static function registerMetaTags(View $view)
    {
        if (!file_exists(static::getPath())) {
            static::setDefaultIcon();
        }

        // Add Apple touch icons
        // https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html
        $view->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => static::getUrl()]);
        $view->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => static::getUrl(152), 'sizes' => '152x152']);
        $view->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => static::getUrl(180), 'sizes' => '180x180']);
        $view->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => static::getUrl(167), 'sizes' => '167x167']);

        // Chrome, Firefox & Co.
        $view->registerLinkTag(['rel' => 'icon', 'href' => static::getUrl(192), 'sizes' => '192x192']);
        $view->registerLinkTag(['rel' => 'icon', 'href' => static::getUrl(96), 'sizes' => '96x96']);
        $view->registerLinkTag(['rel' => 'icon', 'href' => static::getUrl(32), 'sizes' => '32x32']);
    }
}
