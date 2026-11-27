<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use humhub\components\Controller;
use humhub\modules\file\components\RedirectsToFileModal;
use humhub\modules\file\models\File;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The modal managing the subtitles of an audio or video file (see
 * {@see \humhub\modules\file\handler\SubtitlesFileHandler})
 *
 * @since 1.20
 */
class SubtitlesController extends Controller
{
    use RedirectsToFileModal;

    public function actionIndex(string $guid = '')
    {
        $file = File::findOne(['guid' => $guid]);

        if ($file === null || !$this->isMedia($file)) {
            throw new NotFoundHttpException(Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        if (!$file->canDelete()) {
            throw new ForbiddenHttpException(Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        if (!Yii::$app->request->isAjax) {
            return $this->redirectToFileModal($file, ['/file/subtitles', 'guid' => $file->guid]);
        }

        return $this->renderAjax('index', ['file' => $file]);
    }

    private function isMedia(File $file): bool
    {
        return str_starts_with((string)$file->mime_type, 'video/') || str_starts_with((string)$file->mime_type, 'audio/');
    }
}
