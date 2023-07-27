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

        $this->container->initializeHeaderWidget($this);
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
