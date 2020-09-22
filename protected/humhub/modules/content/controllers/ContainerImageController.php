<?php

namespace humhub\modules\content\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use humhub\libs\ProfileImage;
use humhub\models\forms\CropProfileImage;
use humhub\models\forms\UploadProfileImage;
use humhub\modules\content\components\ContentContainerController;

/**
 * Contains profile image and profile banner image upload actions.
 *
 * @package humhub\modules\ui\profile\controllers
 * @since 1.4
 */
abstract class ContainerImageController extends ContentContainerController
{
    const TYPE_PROFILE_IMAGE = 'image';
    const TYPE_PROFILE_BANNER_IMAGE = 'banner';

    /**
     * @var string file upload name for profile image, this exists due to legacy compatibility for views prio to v1.4
     */
    public $imageUploadName = 'images';

    /**
     * @var string file upload name for banner image, this exists due to legacy compatibility for views prio to v1.4
     */
    public $bannerUploadName = 'images';

    /**
     * Handle the profile image upload
     */
    public function actionUpload($type = self::TYPE_PROFILE_IMAGE)
    {
        return $this->handleImageUpload($this->imageUploadName, $type);
    }

    /**
     * Handle the banner image upload
     */
    public function actionBannerUpload()
    {
        return $this->handleImageUpload($this->bannerUploadName, static::TYPE_PROFILE_BANNER_IMAGE);
    }

    /**
     * Crops the space image
     */
    public function actionCrop($type = self::TYPE_PROFILE_IMAGE)
    {
        return $this->handleCrop($type);
    }

    /**
     * Crops the banner image
     */
    public function actionCropBanner()
    {
        return $this->handleCrop(static::TYPE_PROFILE_BANNER_IMAGE);
    }

    /**
     * @param $uploadName string
     * @param string $type
     * @return Response
     */
    protected function handleImageUpload($uploadName, $type = self::TYPE_PROFILE_IMAGE)
    {
        $files = UploadedFile::getInstancesByName($uploadName);

        $model = new UploadProfileImage(['image' => isset($files[0]) ? $files[0] : null]);

        if ($model->validate()) {
            $profileImage = $this->getImageByType($type);
            $profileImage->setNew($model->image);

            return $this->asJson([
                'files' => [
                    [
                        'url' => $profileImage->getUrl(),
                        'type' => $type,
                        'container_id' => $this->contentContainer->contentcontainer_id,
                        'space_id' => $this->contentContainer->id, // Deprecated, only remained for legacy themes prior to 1.4
                    ]
                ]]);
        }

        return $this->asJson([
            'files' => [
                [
                    'name' => isset($files[0]) ? $files[0]->name : '',
                    'error' => true,
                    'errors' => $model->getErrorSummary(false)
                ]
            ]
        ]);
    }

    public function handleCrop($type = self::TYPE_PROFILE_IMAGE)
    {
        $model = new CropProfileImage();
        $profileImage = $this->getImageByType($type);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            $this->view->saved();
            return $this->htmlRedirect($this->contentContainer->getUrl());
        }

        return $this->renderAjax('@content/views/container-image/cropModal', [
            'model' => $model,
            'profileImage' => $profileImage,
            'container' => $this->contentContainer
        ]);
    }

    /**
     * Deletes the profile image or profile banner
     * @throws \yii\web\HttpException
     */
    public function actionDelete($type)
    {
        $this->forcePostRequest();

        $image = $this->getImageByType($type);
        $image->delete();

        $result = ['type' => $type];
        $result['defaultUrl'] = $image->getUrl();
        $result['space_id'] = $this->contentContainer->id; // deprecated since 1.4 only used in legacy profile image logic


        return $this->asJson($result);
    }

    /**
     * @param $type
     * @return ProfileImage
     */
    protected function getImageByType($type)
    {
        return $type === static::TYPE_PROFILE_BANNER_IMAGE
            ? $this->contentContainer->getProfileBannerImage()
            : $this->contentContainer->getProfileImage();
    }
}
