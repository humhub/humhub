<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->registerJsFile('@web/resources/user/profileHeaderImageUpload.js');
$this->registerJs("var userGuid='" . $user->guid . "';", \yii\web\View::POS_BEGIN);
$this->registerJs("var profileImageUploaderUrl='" . Url::toRoute('/user/account/profile-image-upload') . "';", \yii\web\View::POS_BEGIN);
?>

<div class="modal-dialog modal-dialog-medium animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"
                id="myModalLabel">
                    <?php echo Yii::t('TourModule.views_tour_welcome', 'Howdy <strong>%firstname%</strong>, thank you for using HumHub.', ['%firstname%' => $user->profile->firstname]); ?>
            </h4>
        </div>
        <div class="modal-body">
            <div class="text-center">
                <?php echo Yii::t('TourModule.views_tour_welcome', 'You are the first user here... Yehaaa! Be a shining example and complete your profile,<br>so that future users know who is the top dog here and to whom they can turn to if they have questions.'); ?>
                <br><br>
                <br>
            </div>

            <div class="row">

                <div class="col-md-3">

                    <div class="image-upload-container profile-user-photo-container"
                         style="width: 140px; height: 140px;">

                        <?php
                        /* Get original profile image URL */

                        $profileImageExt = pathinfo($user->getProfileImage()->getUrl(), PATHINFO_EXTENSION);

                        $profileImageOrig = preg_replace('/.[^.]*$/', '', $user->getProfileImage()->getUrl());
                        $defaultImage = (basename($user->getProfileImage()->getUrl()) == 'default_user.jpg' || basename($user->getProfileImage()->getUrl()) == 'default_user.jpg?cacheId=0') ? true : false;
                        $profileImageOrig = $profileImageOrig . '_org.' . $profileImageExt;

                        if (!$defaultImage) {
                            ?>

                            <!-- profile image output-->
                            <a data-toggle="lightbox" data-gallery="" href="<?php echo $profileImageOrig; ?>#.jpeg"
                               data-footer='<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo Yii::t('FileModule.widgets_views_showFiles', 'Close'); ?></button>'>
                                <img class="img-rounded profile-user-photo" id="user-profile-image"
                                     src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                                     data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>
                            </a>

                        <?php } else { ?>

                            <img class="img-rounded profile-user-photo" id="user-profile-image"
                                 src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                                 data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>


                        <?php } ?>

                        <!-- check if the current user is the profile owner and can change the images -->

                        <form class="fileupload" id="profilefileupload" action="" method="POST"
                              enctype="multipart/form-data"
                              style="position: absolute; top: 0; left: 0; opacity: 0; height: 140px; width: 140px;">
                            <input type="file" name="profilefiles[]">
                        </form>

                        <div class="image-upload-loader" id="profile-image-upload-loader"
                             style="padding-top: 60px;">
                            <div class="progress image-upload-progess-bar" id="profile-image-upload-bar">
                                <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="00"
                                     aria-valuemin="0"
                                     aria-valuemax="100" style="width: 0%;">
                                </div>
                            </div>
                        </div>

                        <div class="image-upload-buttons" id="profile-image-upload-buttons">
                            <a href="#" onclick="javascript:$('#profilefileupload input').click();"
                               class="btn btn-info btn-sm"><i
                                    class="fa fa-cloud-upload"></i></a>
                            <!--                            <a id="profile-image-upload-edit-button"
                               style="<?php /*                               if (!$user->getProfileImage()->hasImage()) {
                          echo 'display: none;';
                          }
                         */ ?>"
                               href="<?php /* echo Url::toRoute('/user/account/crop-profile-image'); */ ?>"
                               class="btn btn-info btn-sm" data-target="#globalModal"><i
                                    class="fa fa-edit"></i></a>-->
                            <?php
                            echo \humhub\widgets\ModalConfirm::widget(array(
                                'uniqueID' => 'modal_profileimagedelete',
                                'linkOutput' => 'a',
                                'title' => Yii::t('UserModule.widgets_views_deleteImage', '<strong>Confirm</strong> image deleting'),
                                'message' => Yii::t('UserModule.widgets_views_deleteImage', 'Do you really want to delete your profile image?'),
                                'buttonTrue' => Yii::t('UserModule.widgets_views_deleteImage', 'Delete'),
                                'buttonFalse' => Yii::t('UserModule.widgets_views_deleteImage', 'Cancel'),
                                'linkContent' => '<i class="fa fa-times"></i>',
                                'cssClass' => 'btn btn-danger btn-sm',
                                'style' => $user->getProfileImage()->hasImage() ? '' : 'display: none;',
                                'linkHref' => Url::toRoute(["/user/account/delete-profile-image", 'type' => 'profile']),
                                'confirmJS' => 'function(jsonResp) { resetProfileImage(jsonResp); }'
                            ));
                            ?>
                        </div>


                    </div>
                    <p class="help-block text-center"><i
                            class="fa fa-arrow-up"></i><br><?php echo Yii::t('TourModule.views_tour_welcome', 'Drag a photo here or click to browse your files'); ?>
                    </p>

                </div>
                <div class="col-md-9">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $form->field($user->profile, 'firstname')->textInput(['placeholder' => Yii::t('TourModule.views_tour_welcome', 'Your firstname')]); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $form->field($user->profile, 'lastname')->textInput(['placeholder' => Yii::t('TourModule.views_tour_welcome', 'Your lastname')]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $form->field($user->profile, 'title')->textInput(['placeholder' => Yii::t('TourModule.views_tour_welcome', 'Your title or position')]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $form->field($user, 'tags')->textInput(['placeholder' => Yii::t('TourModule.views_tour_welcome', 'Your skills, knowledge and experience (comma seperated)')]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $form->field($user->profile, 'birthday')->widget(yii\jui\DatePicker::className(), ['dateFormat' => Yii::$app->formatter->dateInputFormat, 'clientOptions' => [], 'options' => ['class' => 'form-control']]); ?>
                        </div>
                        <div class="col-md-6">
                            <br><br>
                            <?php echo $form->field($user->profile, 'birthday_hide_year')->checkbox(['label' => Yii::t('TourModule.views_tour_welcome', 'Hide my year of birth')]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $form->field($user->profile, 'phone_work')->textInput(['placeholder' => Yii::t('TourModule.views_tour_welcome', 'Your phone number at work')]); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $form->field($user->profile, 'mobile')->textInput(['placeholder' => Yii::t('TourModule.views_tour_welcome', 'Your mobild phone number')]); ?>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12 text-right">
                            <hr>
                            <br>

                            <?php
                            echo \humhub\widgets\AjaxButton::widget([
                                'label' => Yii::t('TourModule.views_tour_welcome', 'Save and close'),
                                'ajaxOptions' => [
                                    'type' => 'POST',
                                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                                    'url' => Url::to(['/tour/tour/welcome']),
                                ],
                                'htmlOptions' => [
                                    'class' => 'btn btn-primary',
                                ]
                            ]);
                            ?>

                            <?php echo \humhub\widgets\LoaderWidget::widget(['id' => 'invite-loader', 'cssClass' => 'loader-modal hidden']); ?>
                        </div>
                    </div>


                    <?php ActiveForm::end(); ?>
                </div>


            </div>
        </div>


    </div>

</div>
