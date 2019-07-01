<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


namespace humhub\modules\ui\widgets;

use humhub\modules\file\libs\FileHelper;
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

        if ($file !== null && is_file($file->tempName)) {
            Image::getImagine()
                ->open($file->tempName)
                ->save(Yii::getAlias(static::$iconFolderPath) . '/icon.png');
        }

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

    private static function getPath($size = null)
    {
        return static::$iconFolderUrl . DIRECTORY_SEPARATOR . static::getFileName($size);
    }


    private static function getFileName($size = null)
    {
        $fileName = ($size === null) ? 'icon.png' : $size . 'x' . $size . '.png';
        return $fileName;
    }
}
