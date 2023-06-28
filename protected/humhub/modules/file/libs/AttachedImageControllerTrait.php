<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\components\FileAction;
use humhub\models\forms\CropProfileImage;
use humhub\modules\file\actions\DownloadAction;
use humhub\modules\file\actions\UploadAction;
use humhub\modules\file\models\AttachedImage;
use humhub\modules\file\models\File;
use humhub\modules\file\models\forms\AttachedImageUpload;
use humhub\widgets\LayoutAddons;
use humhub\widgets\ModalClose;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\IntegrityException;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UploadedFile;

trait AttachedImageControllerTrait
{
    //  public properties

    /**
     * @var string file upload name for image, this exists due to legacy compatibility for views prio to v1.4
     */
    public string $imageUploadName = 'images';
    public string $cropModalViewFile = '@content/views/container-image/cropModal';

    /**
     * @var string|CropProfileImage
     */
    public string $cropModelClass = CropProfileImage::class;

    /**
     * @var string|AttachedImageUpload
     */
    public string $uploadModelClass = AttachedImageUpload::class;

    /**
     * @var string|UploadedFile
     */
    public string $uploadedFileClass = UploadedFile::class;


    /**
     * Deletes the image
     *
     * @param \humhub\modules\file\models\AttachedImage $image
     * @param array $result
     *
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws HttpException
     */
    protected function actionDeleteInternal(
        AttachedImage $image,
        array $result
    ): Response {
        $this->forcePostRequest();

        $image->delete();

        if (!isset($result['defaultUrl'])) {
            $result['defaultUrl'] = $image->getUrl();
        }

        return $this->asJson($result);
    }

    /**
     * @param string|null $redirectOnSuccess
     * @param array $params Result parameters
     * @param array $args Parameters to the static::getImage() function
     *
     * @return string
     * @throws Exception
     * @throws \JsonException
     */
    public function handleCropInternal(
        ?string $redirectOnSuccess,
        array $params,
        array $args
    ): string {
        $model = new $this->cropModelClass();
        $attachedImage = $this->getImage(null, $args);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $attachedImage->store->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            $this->view->saved();

            if ($redirectOnSuccess) {
                return $this->htmlRedirect($redirectOnSuccess);
            }

            if (Yii::$app->request->isPjax) {
                return "Done.";
            }

            if (Yii::$app->request->isAjax) {
                return $this->renderModalClose();
                return ModalClose::widget([
                    'success' => Yii::t('UserModule.base', 'User has been invited.')
                ]);
                return $this->asJson(['files' => []]);
            }
        }

        return $this->renderAjax(
            $this->cropModalViewFile,
            array_merge($params, [
                'model' => $model,
                'attachedImage' => $attachedImage,
            ])
        );
    }

    /**
     * @param string $uploadName
     * @param array $params Result parameters
     * @param mixed ...$args Parameters to the static::getImage() function
     *
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function handleImageUpload(
        string $uploadName,
        array $params,
        ...$args
    ): Response {
        $files = $this->uploadedFileClass::getInstancesByName($uploadName);

        array_walk($files, function (&$uploadedFile) use (&$params, &$args) {
            $model = new $this->uploadModelClass(
                [
                    $this->uploadModelClass::$fileUploadFieldName => $uploadedFile,
                ]
            );

            if (!$model->validate()) {
                $uploadedFile = [
                    'name' => isset($files[0])
                        ? $files[0]->name
                        : '',
                    'error' => true,
                    'errors' => $model->getErrorSummary(false),
                ];

                return;
            }

            try {
                $image = $this->getImage(null, $args);

                if ($image === null) {
                    throw new \ErrorException('Could not load or create image.');
                }

                $image->setStoredFile($model->{$this->uploadModelClass::$fileUploadFieldName});

                if (!$image->save()) {
                    throw new \ErrorException('Could not save image: ' . implode(" - ", $image->getErrorSummary(true)));
                }
            } catch (\Exception $e) {
                $uploadedFile = [
                    'name' => isset($files[0])
                        ? $files[0]->name
                        : '',
                    'error' => true,
                    'errors' => [$e->getMessage()],
                ];

                return;
            }

            $uploadedFile =
                array_merge($params, [
                    'url' => $image->getUrl(),
                ]);
        });

        return $this->asJson(['files' => $files]);
    }

    /**
     * @throws InvalidConfigException
     * @throws IntegrityException
     * @throws HttpException
     * @throws \Throwable
     * @throws Exception
     */
    public function getImage(?FileAction $action = null, ?array $args = []): ?AttachedImage
    {
        if ($action === null) {
            if ($args === null) {
                return null;
            }

            $get = $args;
        } else {
            $get = $action->getGet();
        }

        $file = $args['__file'] ?? File::findByGuid($get);

        if ($file instanceof AttachedImage) {
            if ($action instanceof DownloadAction && !$file->canRead()) {
                throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
            }

            if ($action instanceof UploadAction && !$file->canEdit()) {
                throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
            }

            if ($file->isNewRecord) {
                $file->save();
            }

            return $file;
        }

        return null;
    }
}
