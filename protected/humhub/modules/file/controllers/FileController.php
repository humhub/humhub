<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
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

        $guid = Yii::$app->request->post('guid');
        $file = File::findOne(['guid' => $guid]);

        if ($file == null) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        if (!$file->canDelete()) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        $file->delete();

        Yii::$app->response->format = 'json';
        return ['success' => true];
    }

}
