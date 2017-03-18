<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\actions;

use Yii;
use yii\web\HttpException;
use yii\base\Action;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;
use yii\filters\HttpCache;

/**
 * DownloadAction
 *
 * @since 1.2
 * @author Luke
 */
class DownloadAction extends Action
{

    /**
     * @see HttpCache
     * @var boolean enable Http Caching
     */
    public $enableHttpCache = true;

    /**
     * @var File the requested file object
     */
    protected $file;

    /**
     * @var string the requested file variant
     */
    protected $variant;

    /**
     * @var boolean force download response 
     */
    protected $download = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->loadFile(Yii::$app->request->get('guid'));
        $this->download = (boolean) Yii::$app->request->get('download', false);
        $this->loadVariant(Yii::$app->request->get('variant', null));
        $this->checkFileExists();
    }

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (!parent::beforeRun()) {
            return false;
        }
        if (!$this->enableHttpCache) {
            return true;
        }

        $httpCache = new HttpCache();
        $httpCache->lastModified = function() {
            return Yii::$app->formatter->asTimestamp($this->file->updated_at);
        };
        $httpCache->etagSeed = function() {
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
        $mimeType = FileHelper::getMimeTypeByExtension($fileName);

        $options = [
            'inline' => (!$this->download && in_array($mimeType, $this->getModule()->inlineMimeTypes)),
            'mimeType' => $mimeType
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
     * @return File the loaded file instance
     * @throws HttpException
     */
    protected function loadFile($guid)
    {
        $file = File::findOne(['guid' => $guid]);

        if ($file == null) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }
        if (!$file->canRead()) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        $this->file = $file;
    }

    /**
     * Loads a variant and verifies
     * 
     * @param string $variant
     * @throws HttpException
     */
    protected function loadVariant($variant)
    {
        // For compatibility reasons (prior 1.1) check the old 'suffix' parameter
        if ($variant === null) {
            $variant = Yii::$app->request->get('suffix', null);
        }

        if ($variant !== null) {
            // Check if variant is available by file
            if (!in_array($variant, $this->file->store->getVariants())) {
                throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file variant!'));
            }
        }

        $this->variant = $variant;
    }

    /**
     * Returns the file module
     * 
     * @return \humhub\modules\file\Module
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
     * @return boolean 
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

}
