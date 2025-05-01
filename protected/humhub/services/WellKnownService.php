<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use Exception;
use humhub\modules\file\Module;
use Yii;
use yii\helpers\Json;
use yii\web\Response;

class WellKnownService
{
    public const URL_ROUTE = '/well-known';
    public const URL_PREFIX = '.well-known/';
    public const ALLOWED_FILES = [
        'fileAssetLinks' => 'assetlinks.json',
        'fileAppleAssociation' => 'apple-app-site-association',
    ];

    private ?string $file;

    public function __construct(string $path)
    {
        $this->file = preg_replace('#^' . preg_quote(self::URL_PREFIX) . '#', '', $path);
    }

    public static function instance(string $path): self
    {
        return new self($path);
    }

    public static function getFileName(string $settingName): string
    {
        return self::ALLOWED_FILES[$settingName] ?? '';
    }

    public static function getFileRoute(string $settingName): array
    {
        return [self::URL_ROUTE, 'file' => self::getFileName($settingName)];
    }

    public function isAllowed(): bool
    {
        return in_array($this->file, self::ALLOWED_FILES);
    }

    public function getRuleRoute(): ?array
    {
        return $this->isAllowed() ? [self::URL_ROUTE, ['file' => $this->file]] : null;
    }

    public function getFileContent(): string
    {
        $settingName = array_search($this->file, self::ALLOWED_FILES);
        if ($settingName === false) {
            return '';
        }

        /* @var Module $module */
        $module = Yii::$app->getModule('file');

        return $module->settings->get($settingName, '');
    }

    public function renderFile(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            Yii::$app->response->data = Json::decode($this->getFileContent());
        } catch (Exception $ex) {
            Yii::$app->response->data = '';
            Yii::error('Wrong file format "' . $this->file . '". Error: ' . $ex->getMessage(), 'fcm-push');
        }

        return Yii::$app->response;
    }
}
