<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * Note: Inline styles have been retained for legacy theme compatibility (prior to v1.4)
 */

use humhub\libs\Html;
use humhub\modules\file\assets\AttachedImageAsset;
use humhub\modules\file\models\AttachedImageOwnerInterface;
use humhub\modules\file\widgets\AttachedImageWidget;
use humhub\modules\file\widgets\Upload;
use humhub\modules\ui\view\components\View;

/**
 * @var $this                      View
 * @var $widget                    AttachedImageWidget
 * @var $htmlOptions               array
 * @var $title                     string
 * @var $subTitle                  string
 * @var $classPrefix               string
 * @var $canEdit                   boolean
 * @var $imageCropUrl              string
 * @var $imageDeleteUrl            string
 * @var $imageUpload               Upload|null
 * @var $record                    AttachedImageOwnerInterface
 * @var $recordImage               \humhub\modules\file\models\AttachedImage|array
 * @var $imageVariant              string
 * @var $imageClass                string
 * @var $imageIdentifier           string|null
 * @var $imageUploadContainerClass string|null
 * @var $imageLink                 string|null
 * @var $imageMenuView             string
 * @var $deleteConfirmationMessage string|null
 * @var $deleteConfirmationTitle   string|null
 * @var $deleteButtonCaption       string|null
 * @var $cancelButtonCaption       string|null
 * @var $progressBarPadding        string|null
 * @var $renderBefore              array|null
 * @var $renderAfter               array|null
 * @var $hasImage                  bool|null
 */

AttachedImageAsset::register($this);

// if the default banner image is displaying change padding to the lower image height
$progressBarPadding ??= '50px 350px';

$imageWidth  = $recordImage->width - 10;
$imageHeight = $recordImage->height - 10;

$imageUploadContainerClass ??= 'image-container-' . $imageIdentifier;

?>

<?= Html::beginTag('div', $htmlOptions) ?>

<?= $widget->renderSection($renderBefore, $this); ?>

<div class="attached-image-upload-container image-upload-container <?= $imageUploadContainerClass ?>"
     style="width: <?= $imageWidth ?>px; height: <?= $imageHeight ?>px;">

    <?php
    if ($hasImage) { ?>
        <a <?= $widget->uiGallery ? printf('data-ui-gallery="%s"', $widget->uiGallery === true ? $imageIdentifier : $widget->uiGallery) : '' ?> href="<?= $recordImage->getUrl($imageVariant) ?>">
            <?= $recordImage->render(
                $imageWidth,
                ['class' => "$imageClass attached-image attached-image-loaded", 'link' => false]
            ) ?>
        </a>
        <?php
    } else {
        echo $recordImage->render(
            $imageHeight,
            ['class' => "$imageClass attached-image attached-image-default"]
        );
    }

    if ($canEdit): ?>
        <div class="attached-image-upload-loader image-upload-loader" style="padding-top: 60px;">
            <?= $imageUpload->progress() ?>
        </div>

        <?= $this->render($imageMenuView ?? 'attachedImageMenu', [
            'upload'      => $imageUpload,
            'hasImage'    => $hasImage,
            'deleteUrl'   => $imageDeleteUrl,
            'cropUrl'     => $imageCropUrl,
            'dropZone'    => '.' . $imageUploadContainerClass,
            'confirmBody' => $deleteConfirmationMessage,
        ]) ?>
        <?php
    endif; ?>

</div>

<?= $widget->renderSection($renderBefore, $this); ?>

<?= Html::endTag('div') ?>
