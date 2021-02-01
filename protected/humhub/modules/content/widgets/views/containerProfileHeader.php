<?php

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $options array */
/* @var $title string */
/* @var $subTitle string */
/* @var $classPrefix string */
/* @var $canEdit boolean */
/* @var $coverCropUrl string */
/* @var $imageCropUrl string */
/* @var $coverDeleteUrl string */
/* @var $imageDeleteUrl string */
/* @var $imageUploadUrl string */
/* @var $coverUploadUrl string */
/* @var $headerControlView string */
/* @var $coverUploadName string */
/* @var $imageUploadName string */

/* @var $container \humhub\modules\content\components\ContentContainerActiveRecord */

/**
 * Note: Inline styles have been retained for legacy theme compatibility (prior to v1.4)
 */

use humhub\modules\content\assets\ContainerHeaderAsset;
use humhub\modules\file\widgets\Upload;
use yii\helpers\Html;

ContainerHeaderAsset::register($this);

// if the default banner image is displaying change padding to the lower image height
$bannerProgressBarPadding = $container->getProfileBannerImage()->hasImage() ? '90px 350px' : '50px 350px';
$bannerUpload = Upload::withName($coverUploadName, ['url' => $coverUploadUrl]);

$profileImageUpload = Upload::withName($imageUploadName, ['url' => $imageUploadUrl]);

$profileImageWidth = $container->getProfileImage()->width() - 10;
$profileImageHeight = $container->getProfileImage()->height() - 10;
?>

<?= Html::beginTag('div', $options) ?>

<div class="panel-profile-header">

    <div class="image-upload-container profile-banner-image-container">
        <!-- profile image output-->
        <?= $container->getProfileBannerImage()->render('width:100%', ['class' => 'img-profile-header-background']) ?>

        <!-- show user name and title -->
        <div class="img-profile-data">
            <h1 class="<?= $classPrefix ?>"><?= Html::encode($title) ?></h1>
            <h2 class="<?= $classPrefix ?>"><?= Html::encode($subTitle) ?></h2>
        </div>

        <?php if ($canEdit) : ?>
            <div class="image-upload-loader" style="padding:<?= $bannerProgressBarPadding ?>">
                <?= $bannerUpload->progress() ?>
            </div>
        <?php endif; ?>

        <?php if ($canEdit) : ?>
            <?= $this->render('containerProfileImageMenu', [
                'upload' => $bannerUpload,
                'hasImage' => $container->getProfileBannerImage()->hasImage(),
                'cropUrl' => $coverCropUrl,
                'deleteUrl' => $coverDeleteUrl,
                'dropZone' => '.profile-banner-image-container',
                'confirmBody' =>  Yii::t('SpaceModule.base', 'Do you really want to delete your title image?')
            ])?>
        <?php endif; ?>
    </div>

    <div class="image-upload-container profile-user-photo-container" style="width: <?= $profileImageWidth ?>px; height: <?= $profileImageHeight ?>px;">

        <?php if ($container->getProfileImage()->hasImage()) : ?>
            <a data-ui-gallery="spaceHeader" href="<?= $container->profileImage->getUrl('_org') ?>">
                <?= $container->getProfileImage()->render($profileImageWidth, ['class' => 'img-profile-header-background profile-user-photo', 'link' => false]) ?>
            </a>
        <?php else : ?>
            <?= $container->getProfileImage()->render($profileImageHeight, ['class' => 'img-profile-header-background profile-user-photo']) ?>
        <?php endif; ?>

        <?php if ($canEdit) : ?>
            <div class="image-upload-loader" style="padding-top: 60px;">
                <?= $profileImageUpload->progress() ?>
            </div>

            <?= $this->render('containerProfileImageMenu', [
                'upload' => $profileImageUpload,
                'hasImage' => $container->getProfileImage()->hasImage(),
                'deleteUrl' => $imageDeleteUrl,
                'cropUrl' => $imageCropUrl,
                'dropZone' => '.profile-user-photo-container',
                'confirmBody' =>   Yii::t('SpaceModule.base', 'Do you really want to delete your profile image?')
            ])?>
        <?php endif; ?>

    </div>
</div>

<?= $this->render($headerControlView, [
    'container' => $container
]) ?>

<?= Html::endTag('div') ?>
