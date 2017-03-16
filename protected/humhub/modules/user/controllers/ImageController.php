<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\HttpException;
use humhub\models\forms\CropProfileImage;
use humhub\modules\user\models\User;
use humhub\models\forms\UploadProfileImage;
use humhub\libs\ProfileImage;
use humhub\libs\ProfileBannerImage;
use yii\web\UploadedFile;
use humhub\modules\user\components\BaseAccountController;

/**
 * ImageController handles user profile or user banner image modifications
 * 
 * @since 1.2
 * @author Luke
 */
class ImageController extends BaseAccountController
{

    const TYPE_PROFILE_IMAGE = 'image';
    const TYPE_PROFILE_BANNER_IMAGE = 'banner';

    /**
     * @var boolean allow modification of profile image
     * Note: this value may be changed via events (e.g. block auto synced images)
     */
    public $allowModifyProfileImage = false;

    /**
     * @var boolean allow modification of profile banner
     * Note: this value may be changed via events (e.g. block auto synced images)
     */
    public $allowModifyProfileBanner = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->getIdentity()->isSystemAdmin() && Yii::$app->getModule('user')->adminCanChangeUserProfileImages) {
                $this->allowModifyProfileBanner = true;
                $this->allowModifyProfileImage = true;
            } elseif (Yii::$app->user->getIdentity()->id == $this->getUser()->id) {
                $this->allowModifyProfileBanner = true;
                $this->allowModifyProfileImage = true;
            }
        }

        // Make sure to execute this on the end of initialization, to allow events
        // to modify the attributes (e.g. allowModifyProfileImage)
        parent::init();
    }

    /**
     * Uploads a new image
     * 
     * @param string $type
     * @return \yii\web\Response the response
     */
    public function actionUpload($type)
    {
        $model = new UploadProfileImage();

        $files = UploadedFile::getInstancesByName('images');
        if (isset($files[0])) {
            $model->image = $files[0];
        }

        if (!$model->validate()) {
            return $this->asJson(['files' => [
                            'error' => true,
                            'errors' => $model->getErrors()
            ]]);
        }

        $image = $this->getProfileImage($type);
        $image->setNew($model->image);

        return $this->asJson(['files' => [
                        'name' => '',
                        'deleteUrl' => '',
                        'deleteType' => '',
                        'size' => $model->image->size,
                        'url' => $image->getUrl(),
        ]]);
    }

    /**
     * Crops a image
     * 
     * @param string $type
     * @return \yii\web\Response the response
     */
    public function actionCrop($type)
    {
        $model = new CropProfileImage();

        if ($type == static::TYPE_PROFILE_IMAGE) {
            $title = Yii::t('UserModule.account', '<strong>Modify</strong> your profile image');
        } elseif ($type == static::TYPE_PROFILE_BANNER_IMAGE) {
            $title = Yii::t('UserModule.account', '<strong>Modify</strong> your title image');
            $model->aspectRatio = '6.3';
            $model->cropSetSelect = [0, 0, 267, 48];
        }

        $image = $this->getProfileImage($type);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $image->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            return $this->htmlRedirect($this->getUser()->getUrl());
        }

        return $this->renderAjax('crop', [
                    'model' => $model,
                    'profileImage' => $image,
                    'user' => $this->getUser(),
                    'type' => $type,
                    'title' => $title,
        ]);
    }

    /**
     * Delete an image
     * 
     * @param string $type
     * @return \yii\web\Response the response
     */
    public function actionDelete($type)
    {
        Yii::$app->response->format = 'json';

        $this->forcePostRequest();

        $image = $this->getProfileImage($type);
        $image->delete();

        return $this->asJson([
                    'type' => $type,
                    'defaultUrl' => $image->getUrl()
        ]);
    }

    /**
     * Returns the Profile Image
     * 
     * @param string $type
     * @return ProfileImage|ProfileBannerImage
     * @throws HttpException
     */
    protected function getProfileImage($type)
    {
        if ($type == static::TYPE_PROFILE_IMAGE) {
            if (!$this->allowModifyProfileImage) {
                throw new HttpException(403, 'Access denied!');
            }
            return new ProfileImage($this->getUser()->guid);
        } elseif ($type == static::TYPE_PROFILE_BANNER_IMAGE) {
            if (!$this->allowModifyProfileBanner) {
                throw new HttpException(403, 'Access denied!');
            }
            return new ProfileBannerImage($this->getUser()->guid);
        } else {
            throw new HttpException(400, 'Invalid image type given!');
        }
    }

    /**
     * Returns the current user of this account
     *
     * An administration can also pass a user id via GET parameter to change users
     * accounts settings.
     *
     * @return User the user
     */
    public function getUser()
    {
        if ($this->user === null && Yii::$app->request->get('userGuid') != '' && Yii::$app->user->getIdentity()->isSystemAdmin()) {
            $user = User::findOne(['guid' => Yii::$app->request->get('userGuid')]);
            if ($user === null) {
                throw new HttpException(404, 'Could not find user!');
            }
            $this->user = $user;
        }

        return parent::getUser();
    }

}
