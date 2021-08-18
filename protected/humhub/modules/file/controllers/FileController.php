<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\file\models\FileContent;
use Yii;
use yii\web\HttpException;
use humhub\modules\file\actions\DownloadAction;
use humhub\modules\file\actions\UploadAction;
use humhub\modules\file\models\File;

/**
 * UploadController provides uploading functions for files
 *
 * @since 0.5
 */
class FileController extends Controller
{
    /**
     * @inheritdoc
     */
    protected $access = ControllerAccess::class;

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['upload', 'delete']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'download' => [
                'class' => DownloadAction::class,
            ],
            'upload' => [
                'class' => UploadAction::class,
            ],
        ];
    }

    public function actionDelete()
    {
        $this->forcePostRequest();

        $file = $this->getFile();

        if (!$file->canDelete()) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        $file->delete();

        Yii::$app->response->format = 'json';
        return ['success' => true];
    }

    public function actionView()
    {
        $file = $this->getFile();

        if (!$file->canRead()) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        return $this->renderAjax('view', [
            'file' => $file,
        ]);
    }

    public function actionEdit()
    {
        $file = $this->getFile(FileContent::class);

        if (!$file->canEdit()) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        return $this->renderAjax('edit', [
            'file' => $file,
        ]);
    }

    private function getFile($class = File::class): File
    {
        $file = $class::findOne(['guid' => Yii::$app->request->get('guid')]);
        if (empty($file)) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        return $file;
    }

}
