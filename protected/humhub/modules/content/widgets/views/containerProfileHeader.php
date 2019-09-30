<?php

/* @var $this \humhub\components\View */
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

use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\content\assets\ContainerHeaderAsset;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\file\widgets\Upload;
use yii\helpers\Html;

ContainerHeaderAsset::register($this);

// if the default banner image is displaying change padding to the lower image height
$bannerProgressBarPadding = $container->getProfileBannerImage()->hasImage() ? '90px 350px' : '50px 350px';
$bannerUpload = Upload::withName($coverUploadName, ['url' => $coverUploadUrl]);

$profileImageUpload = Upload::withName($imageUploadName, ['url' => $imageUploadUrl]);

$image = ($container instanceof Space)
    ? SpaceImage::widget(['space' => $container, 'width' => 140, 'htmlOptions' => ['class' => 'img-profile-header-background']])
    : UserImage::widget(['user' => $container, 'width' => 140,  'imageOptions' => ['class' => 'img-profile-header-background']]);
?>

<?= Html::beginTag('div', $options) ?>

<div class="panel-profile-header">

    <div class="image-upload-container profile-banner-image-container">
        <!-- profile image output-->
        <?= Html::img($container->getProfileBannerImage()->getUrl(), [
            'class' => 'img-profile-header-background',
            'style' => 'width:100%'
        ])?>

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

    <div class="image-upload-container profile-user-photo-container" style="width: 140px; height: 140px;">

        <?php if ($container->getProfileImage()->hasImage()) : ?>
            <a data-ui-gallery="spaceHeader" href="<?= $container->profileImage->getUrl('_org'); ?>">
                <?= $image ?>
            </a>
        <?php else : ?>
            <?= $image ?>
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
