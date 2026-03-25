<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\actions;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use humhub\components\fs\LocalMountConfig;
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

        if (!$this->file->store->has($this->variant)) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }
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
        $httpCache->lastModified = (fn() => Yii::$app->formatter->asTimestamp($this->file->updated_at));
        $httpCache->etagSeed = (fn() => $this->file->store->checksum($this->variant, 'md5'));
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
        $mimeType = $this->file->store->mimeType($this->variant);

        $options = [
            'inline' => (!$this->download && in_array($mimeType, $this->getModule()->inlineMimeTypes, true)),
            'mimeType' => $mimeType,
        ];

        $dataMountConfig = Yii::$app->fs->getDataMountConfig();
        if ($this->getModule()->settings->get('useXSendfile')) {
            if ($dataMountConfig instanceof LocalMountConfig) {
                Yii::$app->response->xSendFile(
                    Yii::getAlias($dataMountConfig->path) . DIRECTORY_SEPARATOR . $this->file->store->get($this->variant),
                    $fileName,
                    $options,
                );
            } else {
                Yii::error(
                    'XSendfile is only supported by ' . LocalMountConfig::class . ' mounts. '
                    . get_class($dataMountConfig) . ' given.',
                );
            }
        } elseif ($dataMountConfig->useTemporaryUrls()) {
            $url = Yii::$app->fs->getDataMount()->temporaryUrl(
                $this->file->store->get($this->variant),
                new DateTimeImmutable('+1 hour'),
            );
            Yii::$app->response->redirect($url);
        } else {
            $options['fileSize'] = $this->file->store->fileSize($this->variant);
            Yii::$app->response->sendStreamAsFile(
                $this->file->store->getContentStream($this->variant),
                $fileName,
                $options,
            );
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
    protected function getModule(): Module
    {
        return Yii::$app->getModule('file');
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
