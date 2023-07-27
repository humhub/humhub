<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\widgets;

use humhub\modules\file\models\AttachedImage;
use humhub\modules\file\models\AttachedImageOwnerInterface;
use humhub\widgets\JsWidget;
use Yii;
use yii\base\Widget;
use yii\web\View;

/**
 *
 * @property-read string[] $attributes
 */
class AttachedImageWidget extends JsWidget
{
    //  public properties

    /**
     * @var $imageClass string
     */
    public string $imageClass = 'attached-image img-profile-header-background profile-user-photo';

    /**
     * @var $imageUploadContainerClass string|null
     */
    public ?string $imageUploadContainerClass = null;

    /**
     * @var $imageLink string|null
     */
    public ?string $imageLink = null;

    /**
     * @var $imageMenuView string|null
     */
    public ?string $imageMenuView = 'attachedImageMenu';

    /**
     * @var $deleteConfirmationMessage  string|null
     */
    public ?string $deleteConfirmationMessage = null;

    /**
     * @var $deleteConfirmationTitle    string|null
     */
    public ?string $deleteConfirmationTitle = null;

    /**
     * @var $deleteButtonCaption        string|null
     */
    public ?string $deleteButtonCaption = null;

    /**
     * @var $cancelButtonCaption        string|null
     */
    public ?string $cancelButtonCaption = null;

    /**
     * @var $progressBarPadding  string|null
     */
    public ?string $progressBarPadding = null;

    /**
     * @var $renderBefore  array|null
     */
    public ?array $renderBefore = null;

    /**
     * @var $renderAfter  array|null
     */
    public ?array $renderAfter = null;

    /**
     * @var $record AttachedImageOwnerInterface
     */
    public AttachedImageOwnerInterface $record;

    /**
     * @var $recordImage AttachedImage|array
     */
    public $recordImage;

    /**
     * @var bool|null $hasImage
     */
    public ?bool $hasImage = null;

    /**
     * @var $imageVariant string|null
     */
    public string $imageVariant = '_original';

    /**
     * @var $canEdit boolean
     */
    public bool $canEdit = false;

    /**
     * @var $imageIdentifier string|null
     */
    public ?string $imageIdentifier = null;

    /**
     * @var $imageUploadName string|null
     */
    public ?string $imageUploadName = null;

    /**
     * @var $imageUploadUrl string|null
     */
    public ?string $imageUploadUrl = null;

    /**
     * @var $imageCropUrl string|null
     */
    public ?string $imageCropUrl = null;

    /**
     * @var $imageDeleteUrl string|null
     */
    public ?string $imageDeleteUrl = null;

    /**
     * @var $title string
     */
    public string $title = '';

    /**
     * @var $subTitle string
     */
    public string $subTitle = '';

    /**
     * @var $classPrefix string
     */
    public string $classPrefix = '';

    /**
     * @var string|bool|null
     */
    public $uiGallery = true;

    public $jsWidget = 'file.AttachedImageWidget';

    public $init = true;

    public function init()
    {
        parent::init();

        if (!$this->recordImage instanceof AttachedImage) {
            $this->recordImage = $this->record->getAttachedImage($this->recordImage);
        }

        //        $this->title    = $this->container->getDisplayName();
        //        $this->subTitle = $this->container->getDisplayNameSub();

        $this->imageIdentifier ??= $this->recordImage->guid;

        $this->hasImage ??= $this->recordImage->hasImage();
        $this->deleteConfirmationMessage ??= Yii::t('FileModule.image', 'Do you really want to delete this image?');
        $this->deleteConfirmationTitle ??= Yii::t('FileModule.image', '<strong>Confirm</strong> image deletion');
        $this->deleteButtonCaption ??= Yii::t('FileModule.image', 'Delete');
        $this->cancelButtonCaption ??= Yii::t('FileModule.image', 'Cancel');
    }

    public function run()
    {
        $imageUpload = $this->canEdit
            ? Upload::withName($this->imageUploadName, ['url' => $this->imageUploadUrl])
            : null;

        $this->init = [
            'try' => 'something',
            'foo' => 'bar',
        ];

        /** @noinspection ProperNullCoalescingOperatorUsageInspection */
        return $this->render('attachedImage', [
            'cancelButtonCaption' => $this->cancelButtonCaption,
            'canEdit' => $this->canEdit,
            'classPrefix' => $this->classPrefix,
            'deleteButtonCaption' => $this->deleteButtonCaption,
            'deleteConfirmationMessage' => $this->deleteConfirmationMessage,
            'deleteConfirmationTitle' => $this->deleteConfirmationTitle,
            'hasImage' => $this->hasImage,
            'imageClass' => $this->imageClass,
            'imageCropUrl' => $this->imageCropUrl,
            'imageDeleteUrl' => $this->imageDeleteUrl,
            'imageIdentifier' => $this->imageIdentifier,
            'imageLink' => $this->imageLink ?? false,
            'imageMenuView' => $this->imageMenuView,
            'imageUploadContainerClass' => $this->imageUploadContainerClass,
            'imageUpload' => $imageUpload,
            'imageVariant' => $this->imageVariant,
            'progressBarPadding' => $this->progressBarPadding,
            'record' => $this->record,
            'recordImage' => $this->recordImage,
            'renderAfter' => $this->renderAfter,
            'renderBefore' => $this->renderBefore,
            'subTitle' => $this->subTitle,
            'title' => $this->title,
            'widget' => $this,
            'htmlOptions' => $this->getOptions(),
        ]);
    }

    public function getAttributes(): array
    {
        return [
            'class' => 'panel panel-default panel-profile',
            'style' => 'width: 150px; height: 150px; padding: 5px; float:left;'
        ];
    }

    protected function getData(): array
    {
        return [
            'hello' => 'world',
        ];
    }

    /**
     * @param array|null $toRender
     * @param View $view
     *
     * @return void
     */
    public function renderSection(
        ?array $toRender,
        View $view
    ): string {
        if ($toRender === null) {
            return '';
        }
        if ($toRender[0] instanceof Widget) {
            return $toRender[0]->render($toRender[1], $toRender[2] ?? []);
        }

        return $view->render($toRender[0], $toRender[1] ?? []);
    }
}
