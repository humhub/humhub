<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\file\libs\FileVariants;
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
 * The file API (see `docs/develop/concept-api.md`): uploads files, deletes them again and
 * lists the versions of one and manages its persistent variants (the subtitles of a media
 * file, see {@see FileVariants}).
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
    protected bool $allowSessionAuth = true;

    /**
     * @inheritdoc
     */
    protected array $scopes = ['versions' => 'content:read', 'variants' => 'content:read', '*' => 'content:write'];

    /**
     * @inheritdoc
     */
    protected array $guestAllowedActions = ['versions', 'variants'];

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
                    'versions' => ['GET', 'HEAD'],
                    'variants' => ['GET', 'HEAD'],
                    'put-variant' => ['PUT'],
                    'delete-variant' => ['DELETE'],
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
     * The versions of a file the caller may see, newest first (see
     * {@see FileSerializer::versions()}).
     */
    public function actionVersions($id)
    {
        $file = File::findOne(['id' => (int)$id]);

        if ($file === null) {
            throw new NotFoundHttpException();
        }

        if (!$file->canView()) {
            throw new ForbiddenHttpException();
        }

        return ['results' => FileSerializer::versions($file)];
    }

    /**
     * The persistent variants of a file the caller may see, ordered by label (see
     * {@see FileSerializer::variants()}).
     */
    public function actionVariants($id)
    {
        $file = File::findOne(['id' => (int)$id]);

        if ($file === null) {
            throw new NotFoundHttpException();
        }

        if (!$file->canView()) {
            throw new ForbiddenHttpException();
        }

        return ['results' => FileSerializer::variants($file)];
    }

    /**
     * Creates or replaces a persistent variant of a file the caller may delete. The body is
     * `{"content": "…", "kind": "subtitles"|"captions"}`; `201` for a new variant, `200` for a
     * replaced one, `422 {"errors": {field: [messages]}}` for an invalid one.
     */
    public function actionPutVariant($id, $name)
    {
        $file = $this->findEditableFile($id);
        $variants = new FileVariants($file);
        $request = Yii::$app->request;

        $content = $request->getBodyParam('content');
        $kind = $request->getBodyParam('kind');
        $existed = $variants->has($name);

        if (!is_string($content)) {
            $errors = ['content' => [Yii::t('FileModule.base', 'The text is required.')]];
        } else {
            $errors = $variants->put($name, $content, is_string($kind) ? $kind : '');
        }

        if ($errors !== []) {
            Yii::$app->response->setStatusCode(422);

            return ['errors' => $errors];
        }

        Yii::$app->response->setStatusCode($existed ? 200 : 201);

        return FileSerializer::variant($file, $name, $variants->getEntries()[$name], FileVariants::parseName($name));
    }

    /**
     * Deletes a persistent variant of a file the caller may delete.
     */
    public function actionDeleteVariant($id, $name)
    {
        if (!(new FileVariants($this->findEditableFile($id)))->delete($name)) {
            throw new NotFoundHttpException();
        }

        Yii::$app->response->setStatusCode(204);

        return null;
    }

    /**
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException when the caller may not delete the file
     */
    private function findEditableFile($id): File
    {
        $file = File::findOne(['id' => (int)$id]);

        if ($file === null) {
            throw new NotFoundHttpException();
        }

        // The rule of the edit handlers of the "Open file" modal
        if (!$file->canDelete()) {
            throw new ForbiddenHttpException();
        }

        return $file;
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
