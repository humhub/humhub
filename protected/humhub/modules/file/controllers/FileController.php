<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use Yii;
use yii\web\HttpException;
use humhub\components\behaviors\AccessControl;
use humhub\modules\file\actions\DownloadAction;
use humhub\modules\file\actions\UploadAction;
use humhub\modules\file\models\File;

/**
 * UploadController provides uploading functions for files
 *
 * @since 0.5
 */
class FileController extends \humhub\components\Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
                'guestAllowedActions' => ['download']
            ],
            'httpCache' => [
                'class' => 'yii\filters\HttpCache',
                'etagSeed' => function ($action, $params) {
                    return serialize([\yii\helpers\Url::current()]);
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'download' => [
                'class' => DownloadAction::className(),
            ],
            'upload' => [
                'class' => UploadAction::className(),
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
    }

}
