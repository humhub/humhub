<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use humhub\modules\space\modules\manage\components\Controller;
use humhub\models\forms\UploadProfileImage;
use humhub\models\forms\CropProfileImage;
use humhub\libs\ProfileBannerImage;
use humhub\libs\ProfileImage;
use Yii;
use yii\web\UploadedFile;

/**
 * ImageControllers handles space profile and banner image
 *
 * @author Luke
 */
class ImageController extends Controller
{

    /**
     * Handle the profile image upload
     */
    public function actionUpload()
    {
        Yii::$app->response->format = 'json';

        $model = new UploadProfileImage();

        $json = [];

        $files = UploadedFile::getInstancesByName('spacefiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new ProfileImage($this->getSpace()->guid);
            $profileImage->setNew($model->image);

            $json['name'] = '';
            $json['space_id'] = $this->getSpace()->id;
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->size;
            $json['deleteUrl'] = '';
            $json['deleteType'] = '';
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }

        return ['files' => $json];
    }

    /**
     * Crops the space image
     */
    public function actionCrop()
    {
        $space = $this->getSpace();
        $model = new CropProfileImage();
        $profileImage = new ProfileImage($space->guid);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            return $this->htmlRedirect($space->getUrl());
        }

        return $this->renderAjax('crop', ['model' => $model, 'profileImage' => $profileImage, 'space' => $space]);
    }

    /**
     * Handle the banner image upload
     */
    public function actionBannerUpload()
    {
        Yii::$app->response->format = 'json';

        $model = new UploadProfileImage();
        $json = [];

        $files = UploadedFile::getInstancesByName('bannerfiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {
            $profileImage = new ProfileBannerImage($this->getSpace()->guid);
            $profileImage->setNew($model->image);

            $json['error'] = false;
            $json['name'] = '';
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->size;
            $json['deleteUrl'] = '';
            $json['deleteType'] = '';
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }

        return ['files' => $json];
    }

    /**
     * Crops the banner image
     */
    public function actionCropBanner()
    {
        $space = $this->getSpace();
        $model = new CropProfileImage();
        $profileImage = new ProfileBannerImage($space->guid);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            return $this->htmlRedirect($space->getUrl());
        }

        return $this->renderAjax('cropBanner', ['model' => $model, 'profileImage' => $profileImage, 'space' => $space]);
    }

    /**
     * Deletes the profile image or profile banner
     */
    public function actionDelete()
    {
        Yii::$app->response->format = 'json';
        $this->forcePostRequest();

        $space = $this->getSpace();

        $type = Yii::$app->request->get('type', 'profile');
        $json = ['type' => $type];

        $image = null;
        if ($type == 'profile') {
            $image = new ProfileImage($space->guid, 'default_space');
        } elseif ($type == 'banner') {
            $image = new ProfileBannerImage($space->guid);
        }

        if ($image) {
            $image->delete();
            $json['space_id'] = $space->id;
            $json['defaultUrl'] = $image->getUrl();
        }

        return $json;
    }

}
