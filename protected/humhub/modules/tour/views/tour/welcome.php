<?php

use humhub\helpers\Html;
use humhub\modules\content\assets\ContainerHeaderAsset;
use humhub\modules\content\controllers\ContainerImageController;
use humhub\modules\content\widgets\ContainerTagPicker;
use humhub\modules\file\widgets\Upload;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\User;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $user User */

ContainerHeaderAsset::register($this);

$imageUploadUrl = $user->createUrl('/user/image/upload');
$imageDeleteUrl = $user->createUrl('/user/image/delete', ['type' => ContainerImageController::TYPE_PROFILE_IMAGE]);
$imageCropUrl = $user->createUrl('/user/image/crop');

$profileImageUpload = Upload::withName('images', ['url' => $imageUploadUrl]);
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('TourModule.base', 'Howdy <strong>%firstname%</strong>, thank you for using %community%.', ['%firstname%' => $user->profile->firstname, '%community%' => Html::encode(Yii::$app->name)]),
    'footer' => ModalButton::save(Yii::t('TourModule.base', 'Save and close'))->submit(['/tour/tour/welcome']),
    'size' => Modal::SIZE_LARGE,
    'closeButton' => false,
    'closable' => false,
]) ?>

    <div class="text-center">
        <?= Yii::t('TourModule.base', 'You are the first user here... Yehaaa! Be a shining example and complete your profile,<br>so that future users know who is the top dog here and to whom they can turn to if they have questions.'); ?>
        <br><br><br>
    </div>

    <div class="row">
        <div class="col-lg-3" data-ui-widget="humhub.content.container.Header" data-ui-init>
            <div class="image-upload-container profile-user-photo-container"
                 style="width: 140px; height: 140px;">
                <?= Html::img($user->getProfileImage()->getUrl(), [
                    'id' => 'welcome-modal-profile-image',
                    'class' => 'img-profile-header-background',
                    'style' => 'width:100%',
                ]) ?>

                <div class="image-upload-loader d-none" style="padding-top: 60px;">
                    <?= $profileImageUpload->progress() ?>
                </div>

                <div style="position:absolute;right:5px;bottom:5px">
                    <?= $profileImageUpload->button([
                        'cssButtonClass' => 'btn btn-info btn-sm profile-image-upload',
                        'tooltip' => false,
                        'dropZone' => '#welcome-modal-profile-image',
                        'options' => ['class' => 'profile-upload-input']]) ?>
                </div>
            </div>

            <p class="text-body-secondary text-center">
                <?= Icon::get('arrow-up') ?>
                <br>
                <?= Yii::t('TourModule.base', 'Drag a photo here or click to browse your files'); ?>
            </p>
        </div>

        <div class="col-lg-9">
            <div class="row">
                <div class="col-lg-6">
                    <?php echo $form->field($user->profile, 'firstname')->textInput(['placeholder' => Yii::t('TourModule.base', 'Your firstname')]); ?>
                </div>
                <div class="col-lg-6">
                    <?php echo $form->field($user->profile, 'lastname')->textInput(['placeholder' => Yii::t('TourModule.base', 'Your lastname')]); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <?php echo $form->field($user->profile, 'title')->textInput(['placeholder' => Yii::t('TourModule.base', 'Your title or position')]); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <?php echo $form->field($user, 'tagsField')->widget(ContainerTagPicker::class, ['placeholderMore' => Yii::t('TourModule.base', 'Your skills, knowledge and experience')]); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?php echo $form->field($user->profile, 'birthday')->widget(yii\jui\DatePicker::class, ['dateFormat' => 'medium', 'clientOptions' => [], 'options' => ['class' => 'form-control']]); ?>
                </div>
                <div class="col-lg-6">
                    <br><br>
                    <?php echo $form->field($user->profile, 'birthday_hide_year')->checkbox(['label' => Yii::t('TourModule.base', 'Hide my year of birth')]); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <hr>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?php echo $form->field($user->profile, 'phone_work')->textInput(['placeholder' => Yii::t('TourModule.base', 'Your phone number at work')]); ?>
                </div>
                <div class="col-lg-6">
                    <?php echo $form->field($user->profile, 'mobile')->textInput(['placeholder' => Yii::t('TourModule.base', 'Your mobile phone number')]); ?>
                </div>
            </div>
        </div>
    </div>

<?php Modal::endFormDialog(); ?>
