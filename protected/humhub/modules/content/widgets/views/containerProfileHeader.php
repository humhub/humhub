<?php

/* @var $this \humhub\components\View */
/* @var $options array */
/* @var $title string */
/* @var $subTitle string */
/* @var $classPrefix string */
/* @var $canEdit bool */
/* @var $coverCropUrl string */
/* @var $imageCropUrl string */
/* @var $coverDeleteUrl string */
/* @var $imageDeleteUrl string */
/* @var $imageUploadUrl string */
/* @var $coverUploadUrl string */
/* @var $headerControlView string */
/* @var $coverUploadName string */
/* @var $imageUploadName string */

/* @var $container ContentContainerActiveRecord */

/**
 * Note: Inline styles have been retained for legacy theme compatibility (prior to v1.4)
 */

use humhub\helpers\Html;
use humhub\modules\content\assets\ContainerHeaderAsset;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\file\widgets\Upload;
use humhub\widgets\bootstrap\Link;

ContainerHeaderAsset::register($this);

// if the default banner image is displaying change padding to the lower image height
$bannerProgressBarPadding = $container->bannerImage->exists() ? '90px 350px' : '50px 350px';
$bannerUpload = Upload::withName($coverUploadName, ['url' => $coverUploadUrl]);

$profileImageUpload = Upload::withName($imageUploadName, ['url' => $imageUploadUrl]);

$profileImageWidth = $container->image->defaultOptions['width'];
$profileImageHeight = $container->image->defaultOptions['height'];
?>

<?= Html::beginTag('div', $options) ?>

<div class="panel-profile-header">

    <div class="image-upload-container profile-banner-image-container">
        <!-- profile image output-->
        <?= Html::img($container->bannerImage, ['width => 100%', 'class' => 'img-profile-header-background']) ?>

        <!-- show user name and title -->
        <div class="img-profile-data">
            <h1 class="<?= $classPrefix ?>"><?= Link::to($title)->link($container->getUrl()) ?></h1>
            <h2 class="<?= $classPrefix ?>"><?= $subTitle ?></h2>
        </div>


        <?php if ($canEdit) : ?>
            <div class="image-upload-loader d-none" style="padding:<?= $bannerProgressBarPadding ?>">
                <?= $bannerUpload->progress() ?>
            </div>
        <?php endif; ?>

        <?php if ($canEdit) : ?>
            <?= $this->render('containerProfileImageMenu', [
                'upload' => $bannerUpload,
                'hasImage' => $container->bannerImage->exists(),
                'cropUrl' => $coverCropUrl,
                'deleteUrl' => $coverDeleteUrl,
                'dropZone' => '.profile-banner-image-container',
                'confirmBody' => Yii::t('SpaceModule.base', 'Do you really want to delete your title image?'),
            ]) ?>
        <?php endif; ?>
    </div>

    <div class="image-upload-container profile-user-photo-container"
         style="width: <?= $profileImageWidth ?>px; height: <?= $profileImageHeight ?>px;">

        <?php if ($container->image->exists()) : ?>
            <a data-ui-gallery="spaceHeader" href="<?= $container->image->getUrl([]) ?>">
                <?= $container->getProfileImage()->render($profileImageWidth - 10, ['class' => 'img-profile-header-background profile-user-photo', 'link' => false, 'showSelfOnlineStatus' => true]) ?>
            </a>
        <?php else : ?>
            <?= $container->getProfileImage()->render($profileImageHeight - 10, ['class' => 'img-profile-header-background profile-user-photo']) ?>
        <?php endif; ?>

        <?php if ($canEdit) : ?>
            <div class="image-upload-loader d-none" style="padding-top: 60px;">
                <?= $profileImageUpload->progress() ?>
            </div>

            <?= $this->render('containerProfileImageMenu', [
                'upload' => $profileImageUpload,
                'hasImage' => $container->image->exists(),
                'deleteUrl' => $imageDeleteUrl,
                'cropUrl' => $imageCropUrl,
                'dropZone' => '.profile-user-photo-container',
                'confirmBody' => Yii::t('SpaceModule.base', 'Do you really want to delete your profile image?'),
            ]) ?>
        <?php endif; ?>

    </div>
</div>

<?= $this->render($headerControlView, [
    'container' => $container,
]) ?>

<?= Html::endTag('div') ?>
