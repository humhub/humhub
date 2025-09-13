<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\mails;

use humhub\libs\LogoImage;
use Yii;
use yii\base\Widget;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\imagine\Image;

/**
 * @since 1.18
 */
class MailHeaderImage extends Widget
{
    private const ASSETS_PATH = 'mail-header';
    private const ASSETS_FILE = 'header.png';

    private const STORE_PATH = '@webroot/uploads/' . self::ASSETS_PATH;

    public const MIN_WIDTH = 50; // For the DesignSettingsForm rules check
    public const MAX_WIDTH = 600; // For image resizing after upload
    public const MIN_HEIGHT = 50; // For the DesignSettingsForm rules check
    public const MAX_HEIGHT = 300; // For image resizing after upload
    public const LOGO_MAX_HEIGHT = 60; // For image resizing after upload

    public int $verticalMargin = 10; // In pixels

    public static function set(?string $fileName): void
    {
        foreach ([static::getAssetsFile(), static::getStoreFile()] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        if ($fileName) {
            $image = Image::getImagine()->open($fileName);
            if ($image->getSize()->getWidth() > static::MAX_WIDTH) {
                $image->resize($image->getSize()->widen(static::MAX_WIDTH));
            }
            if ($image->getSize()->getHeight() > static::MAX_HEIGHT) {
                $image->resize($image->getSize()->heighten(static::MAX_HEIGHT));
            }
            $image->save(self::getStoreFile());
        }
    }

    public static function getUrl(): ?string
    {
        // Check if the file exists at all
        if (!static::hasImage()) {
            return null;
        }

        if (!file_exists(self::getAssetsFile())) {
            copy(self::getStoreFile(), self::getAssetsFile());
        }

        return Yii::getAlias(Yii::$app->assetManager->baseUrl) . DIRECTORY_SEPARATOR . static::ASSETS_PATH . DIRECTORY_SEPARATOR . static::ASSETS_FILE . '?v=' . filemtime(self::getAssetsFile());
    }

    public static function hasImage(): bool
    {
        if (file_exists(self::getStoreFile())) {
            return true;
        }

        return false;
    }

    private static function getStorePath(): string
    {
        $path = Yii::getAlias(static::STORE_PATH);
        FileHelper::createDirectory($path);
        return $path;
    }

    private static function getStoreFile(): string
    {
        return self::getStorePath() . DIRECTORY_SEPARATOR . static::ASSETS_FILE;
    }

    private static function getAssetsPath(): string
    {
        $path = Yii::getAlias(Yii::$app->assetManager->basePath) . DIRECTORY_SEPARATOR . static::ASSETS_PATH;
        FileHelper::createDirectory($path);
        return $path;
    }

    private static function getAssetsFile(): string
    {
        return self::getAssetsPath() . DIRECTORY_SEPARATOR . static::ASSETS_FILE;
    }

    public function run()
    {
        $hasMailHeaderImage = static::hasImage();
        $hasLogoImage = LogoImage::hasImage();
        $showNameInsteadOfLogo = (bool)Yii::$app->settings->get('showNameInsteadOfLogo');

        // Get relative image URL
        $imgUrl = null;
        if ($hasMailHeaderImage) {
            $imgUrl = static::getUrl();
        } elseif ($hasLogoImage && !$showNameInsteadOfLogo) {
            $imgUrl = LogoImage::getUrl(static::MAX_WIDTH, static::LOGO_MAX_HEIGHT);
        }

        // Change it to absolute URL
        $imgUrl = $imgUrl ? Url::to($imgUrl, true) : null;

        return $this->render('mailHeaderImage', [
            'imgUrl' => $imgUrl,
            'appName' => Yii::$app->name,
            'verticalMargin' => $this->verticalMargin,
        ]);
    }
}
