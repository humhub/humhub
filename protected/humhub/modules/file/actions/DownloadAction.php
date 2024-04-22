<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\actions;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\filters\HttpCache;
use yii\web\HttpException;

/**
 * DownloadAction
 *
 * @since 1.2
 *
 * @property-read string $storedFilePath
 * @property-read string $fileName
 * @property-read Module $module
 */
class DownloadAction extends Action
{
    /**
     * @see HttpCache
     * @var bool enable Http Caching
     */
    public bool $enableHttpCache = true;

    /**
     * @var File|null the requested file object
     */
    protected ?File $file = null;

    /**
     * @var string|null the requested file variant
     */
    protected ?string $variant = null;

    /**
     * @var bool force download response
     */
    protected bool $download = false;

    /**
     * @inheritdoc
     * @throws HttpException
     */
    public function init()
    {
        $this->loadFile(Yii::$app->request->get('guid'), Yii::$app->request->get('token'));
        $this->download = (bool)Yii::$app->request->get('download', false);
        $this->loadVariant(Yii::$app->request->get('variant'));
        $this->checkFileExists();
    }

    /**
     * @inheritdoc
     * @throws HttpException
     */
    public function beforeRun()
    {
        if (Yii::$app->request->isPjax) {
            throw new HttpException(400, 'File downloads are not allowed with pjax!');
        }

        if (!parent::beforeRun()) {
            return false;
        }
        if (!$this->enableHttpCache) {
            return true;
        }

        $httpCache = new HttpCache();
        $httpCache->lastModified = function () {
            return Yii::$app->formatter->asTimestamp($this->file->updated_at);
        };
        $httpCache->etagSeed = function () {
            if (file_exists($this->getStoredFilePath())) {
                return md5_file($this->getStoredFilePath());
            }
            return null;
        };
        if (!$httpCache->beforeAction($this)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $fileName = $this->getFileName();
        $mimeType = FileHelper::getMimeType($this->getStoredFilePath());

        $options = [
            'inline' => (!$this->download && in_array($mimeType, $this->getModule()->inlineMimeTypes, true)),
            'mimeType' => $mimeType,
        ];

        if ($this->useXSendFile()) {
            Yii::$app->response->xSendFile($this->getStoredFilePath(), $fileName, $options);
        } else {
            Yii::$app->response->sendFile($this->getStoredFilePath(), $fileName, $options);
        }
    }

    /**
     * Loads the file by given guid
     *
     * @param string $guid
     * @param string|null $token
     *
     * @throws HttpException
     */
    protected function loadFile(?string $guid, ?string $token = null)
    {
        $file = File::findOne(['guid' => $guid]);

        if ($file === null) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        $user = null;
        if ($token !== null) {
            $user = static::getUserByDownloadToken($token, $file);
        }

        // File is not assigned to any database record (yet)
        if (empty($file->object_model) && (Yii::$app->user->isGuest || $file->created_by != Yii::$app->user->id)) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        if (!$file->canView($user)) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        $this->file = $file;
    }


    /**
     * Loads a variant and verifies
     *
     * @param string|null $variant
     *
     * @throws HttpException
     */
    protected function loadVariant($variant)
    {
        // For compatibility reasons (prior 1.1) check the old 'suffix' parameter
        if ($variant === null) {
            $variant = Yii::$app->request->get('suffix');
        }

        // Check if variant is available by file
        if (($variant !== null) && !in_array($variant, $this->file->store->getVariants(), true)) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file variant!'));
        }

        $this->variant = $variant;
    }

    /**
     * Returns the file module
     *
     * @return Module
     */
    protected function getModule()
    {
        return Yii::$app->getModule('file');
    }

    /**
     * Check if requested file exists
     *
     * @throws HttpException
     */
    protected function checkFileExists()
    {
        if (!file_exists($this->file->store->get($this->variant))) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }
    }

    /**
     * Returns the filename
     *
     * @return string
     */
    protected function getFileName()
    {
        if (!$this->variant) {
            return $this->file->file_name;
        }

        // Build filename by variant
        if (FileHelper::hasExtension($this->variant)) {
            // Use extension of variant
            $variantParts = pathinfo($this->variant);
            $orgParts = pathinfo($this->file->file_name);
            return $orgParts['filename'] . '_' . $variantParts['filename'] . '.' . $variantParts['extension'];
        } elseif (FileHelper::hasExtension($this->file->file_name)) {
            // Use extension of original file
            $parts = pathinfo($this->file->file_name);
            return $parts['filename'] . '_' . $this->variant . '.' . $parts['extension'];
        }

        return $this->file->file_name . '_' . $this->variant;
    }

    /**
     * Checks if XSendFile downloads are enabled
     *
     * @return bool
     */
    protected function useXSendFile()
    {
        return ($this->getModule()->settings->get('useXSendfile'));
    }

    /**
     * Returns the file path of the stored file
     *
     * @return string path to the saved file
     */
    protected function getStoredFilePath()
    {
        return $this->file->store->get($this->variant);
    }

    /**
     * Returns the User model by given JWT token
     *
     * @param string $token
     * @param File $file
     * @return User|null
     */
    public static function getUserByDownloadToken(string $token, File $file)
    {
        try {
            $decoded = JWT::decode($token, new Key(static::getDownloadTokenKey(), 'HS256'));
        } catch (\Exception $ex) {
            Yii::warning('Could not decode provided JWT token. ' . $ex->getMessage());
        }
        if (!empty($decoded->sub) && !empty($decoded->aud) && $decoded->aud == $file->id) {
            return User::findOne(['id' => $decoded->sub]);
        }

        return null;
    }

    /**
     * Returns a token to access this file by JWT token
     *
     * @param File $file
     * @param User $user
     * @return string
     */
    public static function generateDownloadToken(File $file, User $user)
    {
        $token = [
            'iss' => 'dld-token-v1',
            'sub' => $user->id,
            'aud' => $file->id,
        ];
        return JWT::encode($token, static::getDownloadTokenKey(), 'HS256');
    }


    /**
     * @return string the secret key for file download tokens
     * @throws Exception
     */
    private static function getDownloadTokenKey()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        $key = $module->settings->get('downloadTokenKey');
        if (empty($key)) {
            $key = Yii::$app->security->generateRandomString(32);
            $module->settings->set('downloadTokenKey', $key);
        }

        return $key;
    }
}
