<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\helpers;

use Yii;
use yii\imagine\Image;
use yii\helpers\FileHelper;

final class LoginBackgroundImageHelper
{

    private const ASSETS_PATH = 'login-bg';
    private const ASSETS_FILE = 'background.png';

    private const STORE_PATH = '@webroot/uploads/login-bg';

    public static function set(?string $fileName): void
    {
        @unlink(static::getAssetsFile());
        @unlink(static::getStoreFile());

        if ($fileName) {
            Image::getImagine()->open($fileName)->save(self::getStoreFile());
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

        return Yii::getAlias(Yii::$app->assetManager->baseUrl) . '/' . static::ASSETS_PATH . '/' . static::ASSETS_FILE;
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
}
