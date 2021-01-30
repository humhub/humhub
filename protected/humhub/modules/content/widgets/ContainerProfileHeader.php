<?php


namespace humhub\modules\content\widgets;

use humhub\modules\user\controllers\ImageController;
use Yii;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\content\controllers\ContainerImageController;
use humhub\widgets\JsWidget;

class ContainerProfileHeader extends JsWidget
{
    public $jsWidget = 'content.container.Header';

    public $init = true;

    /**
     * @var ContentContainerActiveRecord
     */
    public $container;

    public $canEdit = false;

    public $imageUploadUrl;

    public $coverUploadUrl;

    public $imageCropUrl;

    public $coverCropUrl;

    public $imageDeleteUrl;

    public $coverDeleteUrl;

    public $title;

    public $subTitle;

    public $classPrefix;

    public $headerControlView;

    public $coverUploadName;

    public $imageUploadName;


    public function init()
    {
        parent::init();

        $this->title = $this->container->getDisplayName();
        $this->subTitle = $this->container->getDisplayNameSub();

        if ($this->container instanceof Space) {
            $this->initSpaceData();
        } else {
            $this->initUserData();
        }
    }

    public function initSpaceData()
    {
        $this->imageUploadUrl = $this->container->createUrl('/space/manage/image/upload');
        $this->coverUploadUrl = $this->container->createUrl('/space/manage/image/banner-upload');
        $this->coverCropUrl = $this->container->createUrl('/space/manage/image/crop-banner');
        $this->imageCropUrl = $this->container->createUrl('/space/manage/image/crop');
        $this->imageDeleteUrl = $this->container->createUrl('/space/manage/image/delete', ['type' => ContainerImageController::TYPE_PROFILE_IMAGE]);
        $this->coverDeleteUrl = $this->container->createUrl('/space/manage/image/delete', ['type' => ContainerImageController::TYPE_PROFILE_BANNER_IMAGE]);
        $this->headerControlView = '@space/widgets/views/profileHeaderControls.php';
        $this->classPrefix = 'space';

        // This is required in order to stay compatible with old themes...
        $this->imageUploadName = 'spacefiles[]';
        $this->coverUploadName = 'bannerfiles[]';
        $this->canEdit = !Yii::$app->user->isGuest && $this->container->isAdmin();
    }

    public function initUserData()
    {
        $this->imageUploadUrl = $this->container->createUrl('/user/image/upload');
        $this->coverUploadUrl = $this->container->createUrl('/user/image/banner-upload');
        $this->coverCropUrl = $this->container->createUrl('/user/image/crop-banner');
        $this->imageCropUrl = $this->container->createUrl('/user/image/crop');
        $this->imageDeleteUrl = $this->container->createUrl('/user/image/delete', ['type' => ContainerImageController::TYPE_PROFILE_IMAGE]);
        $this->coverDeleteUrl = $this->container->createUrl('/user/image/delete', ['type' => ContainerImageController::TYPE_PROFILE_BANNER_IMAGE]);
        $this->headerControlView = '@user/widgets/views/profileHeaderControls.php';
        $this->coverUploadName = $this->imageUploadName = 'images[]';
        $this->classPrefix = 'profile';

        if(!Yii::$app->user->isGuest) {
            /** @TODO move this out of ImageController layer... */
            $this->canEdit = ImageController::canEditProfileImage($this->container);
        }
    }

    public function run()
    {
        return $this->render('containerProfileHeader', [
            'options' => $this->getOptions(),
            'container' => $this->container,
            'canEdit' => $this->canEdit,
            'title' => $this->title,
            'subTitle' => $this->subTitle,
            'classPrefix' => $this->classPrefix,
            'coverCropUrl' => $this->coverCropUrl,
            'imageCropUrl' => $this->imageCropUrl,
            'imageDeleteUrl' => $this->imageDeleteUrl,
            'coverDeleteUrl' => $this->coverDeleteUrl,
            'imageUploadUrl' => $this->imageUploadUrl,
            'coverUploadUrl' => $this->coverUploadUrl,
            'imageUploadName' => $this->imageUploadName,
            'coverUploadName' => $this->coverUploadName,
            'headerControlView' => $this->headerControlView
        ]);
    }

    public function getAttributes()
    {
        return [
            'class' => 'panel panel-default panel-profile'
        ];
    }
}
