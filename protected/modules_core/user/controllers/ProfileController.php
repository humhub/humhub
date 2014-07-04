<?php

/**
 * ProfileController is responsible for all user profiles.
 * Also the following functions are implemented here.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class ProfileController extends Controller {

    public $subLayout = "_layout";

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors() {
        return array(
            'ProfileControllerBehavior' => array(
                'class' => 'application.modules_core.user.behaviors.ProfileControllerBehavior',
            ),
        );
    }

    /**
     *
     */
    public function actionIndex() {
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.ui.widget.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.iframe-transport.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.fileupload.js');

        $this->render('index');
    }

    /**
     * Handle the profile image upload
     */
    public function actionProfileImageUpload()
    {

        $model = new UploadProfileImageForm();

        $json = array();

        //$model->image = CUploadedFile::getInstance($model, 'image');
        $files = CUploadedFile::getInstancesByName('profilefiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new ProfileImage(Yii::app()->user->guid);
            $profileImage->setNew($model->image);

            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->getSize();
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";

        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }


        return $this->renderJson(array('files' => $json));
    }


    /**
     * Crops the profile image of the user
     */
    public function actionCropProfileImage()
    {

        $model = new CropProfileImageForm;
        $profileImage = new ProfileImage(Yii::app()->user->guid);

        if (isset($_POST['CropProfileImageForm'])) {
            $_POST['CropProfileImageForm'] = Yii::app()->input->stripClean($_POST['CropProfileImageForm']);
            $model->attributes = $_POST['CropProfileImageForm'];
            if ($model->validate()) {
                $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
                $this->htmlRedirect($this->createUrl('//user/profile')); //redirect($this->createUrl('//user/account/edit'));
            }
        }

        //$this->render('cropImage', array('model' => $model, 'profileImage' => $profileImage, 'user' => Yii::app()->user->getModel()));

        $output = $this->renderPartial('cropProfileImage', array('model' => $model, 'profileImage' => $profileImage, 'user' => Yii::app()->user->getModel()));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }



    /**
     * Handle the banner image upload
     */
    public function actionBannerImageUpload()
    {

        $model = new UploadProfileImageForm();

        $json = array();

        //$model->image = CUploadedFile::getInstance($model, 'image');
        $files = CUploadedFile::getInstancesByName('bannerfiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new ProfileBannerImage(Yii::app()->user->guid);
            $profileImage->setNew($model->image);

            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->getSize();
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";

        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }


        return $this->renderJson(array('files' => $json));
    }




    /**
     * Crops the banner image of the user
     */
    public function actionCropBannerImage()
    {

        $model = new CropProfileImageForm;
        $profileImage = new ProfileBannerImage(Yii::app()->user->guid);

        if (isset($_POST['CropProfileImageForm'])) {
            $_POST['CropProfileImageForm'] = Yii::app()->input->stripClean($_POST['CropProfileImageForm']);
            $model->attributes = $_POST['CropProfileImageForm'];
            if ($model->validate()) {
                $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
                $this->htmlRedirect($this->createUrl('//user/profile')); //redirect($this->createUrl('//user/account/edit'));
            }
        }

        //$this->render('cropImage', array('model' => $model, 'profileImage' => $profileImage, 'user' => Yii::app()->user->getModel()));

        $output = $this->renderPartial('cropBannerImage', array('model' => $model, 'profileImage' => $profileImage, 'user' => Yii::app()->user->getModel()));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }



    /**
     *
     */
    public function actionAbout() {
        $this->render('about',array('user'=>$this->getUser()));
    }

    /**
     * Unfollows a User
     *
     */
    public function actionFollow() {

        if (Yii::app()->user->isGuest)
            Yii::app()->user->loginRequired();

        $haunted = $this->getUser();

        // Self Following?
        if ($haunted->id == Yii::app()->user->id)
            throw new CHttpException(500, 'Not supported!');

        // Really not folllowed yet?
        if (!$haunted->isFollowedBy(Yii::app()->user->id)) {

            // Create Follower Object
            $follow = new UserFollow;
            $follow->user_followed_id = $haunted->id;
            $follow->user_follower_id = Yii::app()->user->id;
            $follow->save();
        }


        $this->redirect($this->createUrl('profile/', array('guid' => $haunted->guid)));
    }

    /**
     * Unfollows a User
     */
    public function actionUnfollow() {

        if (Yii::app()->user->isGuest)
            Yii::app()->user->loginRequired();

        $haunted = $this->getUser();

        if ($haunted->isFollowedBy(Yii::app()->user->id)) {

            $follow = UserFollow::model()->findByAttributes(array(
                'user_follower_id' => Yii::app()->user->id,
                'user_followed_id' => $haunted->id,
            ));
            $follow->delete();
        }

        $this->redirect($this->createUrl('profile/', array('guid' => $haunted->guid)));
    }

}

?>
