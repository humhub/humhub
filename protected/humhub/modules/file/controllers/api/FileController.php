<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileUpload;
use humhub\modules\file\serializers\FileSerializer;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * The file API (see `docs/develop/concept-api.md`): uploads files and deletes them again.
 *
 * This is what the Vue `UploadField` posts to (see `docs/develop/ui-js-vuejs-forms.md`), and
 * the API counterpart of the legacy {@see \humhub\modules\file\actions\UploadAction} the
 * jQuery upload widget uses — same model, same validation, same image downscaling, so both
 * paths produce identical files.
 *
 * ## An upload is a batch, and reports per file
 *
 * A single request carries any number of files, and one invalid file among them must not
 * discard the valid ones — the user picked all of them in one go and would have to repeat the
 * whole selection. The response therefore reports outcomes per file:
 *
 * ```json
 * { "results": [ <file>, … ], "errors": [ { "fileName": "big.pdf", "messages": ["…"] } ] }
 * ```
 *
 * `200` whenever the request carried at least one file, even if every single one was
 * rejected: the request itself was processed, and a client showing per-file errors takes the
 * same code path either way. This is the one **deliberate deviation** from the platform's
 * `422 {"errors": …}` validation convention, which describes a request whose *fields* are
 * invalid — the case here is a batch item, not a field. A request with no file at all IS such
 * a malformed request, and answers `422`.
 *
 * ## Files arrive unattached
 *
 * Nothing is attached to a record here (no `objectModel`/`objectId` as in `UploadAction`): a
 * client uploads, keeps the returned guids, and submits them with its own form
 * (`fileList`) — which is also what makes removing a file before saving a purely client-side
 * operation. Unattached files are cleaned up by the file module's own cron job, exactly as
 * for the legacy path.
 *
 * @since 1.20
 */
class FileController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected bool $enableSessionAuth = true;

    /**
     * @var string the request field name carrying the uploaded files
     */
    public const UPLOAD_FIELD = 'files';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'delete' => ['DELETE'],
                ],
            ],
        ]);
    }

    /**
     * Stores the uploaded files of the caller.
     */
    public function actionCreate()
    {
        $uploads = UploadedFile::getInstancesByName(self::UPLOAD_FIELD);

        if ($uploads === []) {
            Yii::$app->response->statusCode = 422;

            return ['errors' => [
                self::UPLOAD_FIELD => [Yii::t('FileModule.base', 'No file was uploaded.')],
            ]];
        }

        $results = [];
        $errors = [];

        foreach ($uploads as $upload) {
            $file = new FileUpload();
            $file->setUploadedFile($upload);

            if ($file->save()) {
                // Same post-processing the legacy upload action applies, so an image uploaded
                // through either path is stored at the same dimensions.
                ImageHelper::downscaleImage($file);
                $results[] = FileSerializer::file($file);
                continue;
            }

            $errors[] = [
                // The name as sent, not as stored: the client has to match the entry against
                // the file the user picked, and `FileValidator` may have rewritten
                // `file_name` (see its `validateFileName()`).
                'fileName' => $upload->name,
                'messages' => array_values(ArrayHelper::flatten($file->getErrors())),
            ];
        }

        return ['results' => $results, 'errors' => $errors];
    }

    /**
     * Deletes a file the caller is allowed to delete.
     */
    public function actionDelete($id)
    {
        $file = File::findOne(['id' => (int)$id]);

        if ($file === null) {
            throw new NotFoundHttpException();
        }

        // Covers all three ownership cases (standalone, unattached, attached to a record) -
        // see File::canDelete().
        if (!$file->canDelete()) {
            throw new ForbiddenHttpException();
        }

        $file->delete();

        Yii::$app->response->setStatusCode(204);

        return null;
    }
}
