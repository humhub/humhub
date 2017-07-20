<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\user\controllers\ImageController;

if ($allowModifyProfileBanner || $allowModifyProfileImage) {
    $this->registerJsFile('@web-static/resources/user/profileHeaderImageUpload.js');
    $this->registerJs("var profileImageUploaderUserGuid='" . $user->guid . "';", \yii\web\View::POS_BEGIN);
    $this->registerJs("var profileImageUploaderCurrentUserGuid='" . Yii::$app->user->getIdentity()->guid . "';", \yii\web\View::POS_BEGIN);
    $this->registerJs("var profileImageUploaderUrl='" . Url::to(['/user/image/upload', 'userGuid' => $user->guid, 'type' => ImageController::TYPE_PROFILE_IMAGE]) . "';", \yii\web\View::POS_BEGIN);
    $this->registerJs("var profileHeaderUploaderUrl='" . Url::to(['/user/image/upload', 'userGuid' => $user->guid, 'type' => ImageController::TYPE_PROFILE_BANNER_IMAGE]) . "';", \yii\web\View::POS_BEGIN);
}
?>
<div class="panel panel-default panel-profile">

    <div class="panel-profile-header">

        <div class="image-upload-container" style="width: 100%; height: 100%; overflow:hidden;">
            <!-- profile image output-->
            <img class="img-profile-header-background" id="user-banner-image" alt="<?= Yii::t('base', 'Profile image of {displayName}', ['displayName' => Html::encode($user->displayName)]); ?>"
                 src="<?= $user->getProfileBannerImage()->getUrl(); ?>"
                 width="100%" style="width: 100%; max-height: 192px;">

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($allowModifyProfileBanner) : ?>
                <form class="fileupload" id="bannerfileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; width: 100%; height: 100%;">
                    <input type="file" name="images[]" aria-hidden="true">
                </form>

                <?php
                // set standard padding for banner progressbar
                $padding = '90px 350px';

                // if the default banner image is displaying
                if (!$user->getProfileBannerImage()->hasImage()) {
                    // change padding to the lower image height
                    $padding = '50px 350px';
                }
                ?>

                <div class="image-upload-loader" id="banner-image-upload-loader"
                     style="padding: <?php echo $padding ?>;">
                    <div class="progress image-upload-progess-bar" id="banner-image-upload-bar">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="00"
                             aria-valuemin="0"
                             aria-valuemax="100" style="width: 0%;">
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <!-- show user name and title -->
            <div class="img-profile-data">
                <h1><?= Html::encode($user->displayName); ?></h1>

                <h2><?= Html::encode($user->profile->title); ?></h2>
            </div>

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($allowModifyProfileBanner): ?>
                <div class="image-upload-buttons" id="banner-image-upload-buttons">
                    <a href="#" onclick="javascript:$('#bannerfileupload input').click();" class="btn btn-info btn-sm" aria-label="<?= Yii::t('UserModule.base', 'Upload profile banner'); ?>">
                        <i class="fa fa-cloud-upload"></i>
                    </a>
                    <a id="banner-image-upload-edit-button"
                       style="<?= (!$user->getProfileBannerImage()->hasImage()) ? 'display: none;' : '' ?>"
                       href="<?= Url::to(['/user/image/crop', 'userGuid' => $user->guid, 'type' => ImageController::TYPE_PROFILE_BANNER_IMAGE]); ?>"
                       class="btn btn-info btn-sm" data-target="#globalModal" data-backdrop="static" aria-label="<?= Yii::t('UserModule.base', 'Crop profile background'); ?>">
                        <i class="fa fa-edit"></i>
                    </a>
                    <?php
                    echo \humhub\widgets\ModalConfirm::widget([
                        'uniqueID' => 'modal_bannerimagedelete',
                        'linkOutput' => 'a',
                        'ariaLabel' => Yii::t('UserModule.widgets_views_deleteBanner', 'Delete profile banner'),
                        'title' => Yii::t('UserModule.widgets_views_deleteBanner', '<strong>Confirm</strong> image deleting'),
                        'message' => Yii::t('UserModule.widgets_views_deleteBanner', 'Do you really want to delete your title image?'),
                        'buttonTrue' => Yii::t('UserModule.widgets_views_deleteBanner', 'Delete'),
                        'buttonFalse' => Yii::t('UserModule.widgets_views_deleteBanner', 'Cancel'),
                        'linkContent' => '<i class="fa fa-times"></i>',
                        'cssClass' => 'btn btn-danger btn-sm',
                        'style' => $user->getProfileBannerImage()->hasImage() ? '' : 'display: none;',
                        'linkHref' => Url::to(['/user/image/delete', 'userGuid' => $user->guid, 'type' => ImageController::TYPE_PROFILE_BANNER_IMAGE]),
                        'confirmJS' => 'function(jsonResp) { resetProfileImage(jsonResp); }'
                    ]);
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="image-upload-container profile-user-photo-container" style="width: 140px; height: 140px;">

            <?php if ($user->profileImage->hasImage()) : ?>
                <a data-ui-gallery="profileHeader"  href="<?= $user->profileImage->getUrl('_org'); ?>">
                    <img class="img-rounded profile-user-photo" id="user-profile-image"
                         src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                         data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>
                </a>
            <?php else : ?>
                <img class="img-rounded profile-user-photo" id="user-profile-image"
                     src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                     data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>
                 <?php endif; ?>

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($allowModifyProfileImage) : ?>
                <form class="fileupload" id="profilefileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; height: 140px; width: 140px;">
                    <input type="file" aria-hidden="true" name="images[]">
                </form>

                <div class="image-upload-loader" id="profile-image-upload-loader" style="padding-top: 60px;">
                    <div class="progress image-upload-progess-bar" id="profile-image-upload-bar">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="00"
                             aria-valuemin="0"
                             aria-valuemax="100" style="width: 0%;">
                        </div>
                    </div>
                </div>

                <div class="image-upload-buttons" id="profile-image-upload-buttons">
                    <a href="#" onclick="javascript:$('#profilefileupload input').click();" class="btn btn-info btn-sm" aria-label="<?= Yii::t('UserModule.base', 'Upload profile image'); ?>">
                        <i class="fa fa-cloud-upload"></i>
                    </a>
                    <a id="profile-image-upload-edit-button"
                       style="<?php
                       if (!$user->getProfileImage()->hasImage()) {
                           echo 'display: none;';
                       }
                       ?>"
                       href="<?php echo Url::to(['/user/image/crop', 'userGuid' => $user->guid, 'type' => ImageController::TYPE_PROFILE_IMAGE]); ?>"
                       class="btn btn-info btn-sm" data-target="#globalModal" data-backdrop="static" aria-label="<?= Yii::t('UserModule.base', 'Crop profile image'); ?>">
                        <i class="fa fa-edit"></i></a>
                    <?php
                    echo \humhub\widgets\ModalConfirm::widget(array(
                        'uniqueID' => 'modal_profileimagedelete',
                        'linkOutput' => 'a',
                        'ariaLabel' => Yii::t('UserModule.base', 'Delete profile image'),
                        'title' => Yii::t('UserModule.widgets_views_deleteImage', '<strong>Confirm</strong> image deleting'),
                        'message' => Yii::t('UserModule.widgets_views_deleteImage', 'Do you really want to delete your profile image?'),
                        'buttonTrue' => Yii::t('UserModule.widgets_views_deleteImage', 'Delete'),
                        'buttonFalse' => Yii::t('UserModule.widgets_views_deleteImage', 'Cancel'),
                        'linkContent' => '<i class="fa fa-times"></i>',
                        'cssClass' => 'btn btn-danger btn-sm',
                        'style' => $user->getProfileImage()->hasImage() ? '' : 'display: none;',
                        'linkHref' => Url::to(["/user/image/delete", 'type' => ImageController::TYPE_PROFILE_IMAGE, 'userGuid' => $user->guid]),
                        'confirmJS' => 'function(jsonResp) { resetProfileImage(jsonResp); }'
                    ));
                    ?>
                </div>
            <?php endif; ?>

        </div>


    </div>

    <div class="panel-body">

        <div class="panel-profile-controls">
            <!-- start: User statistics -->
            <div class="row">
                <div class="col-md-12">
                    <div class="statistics pull-left">

                        <?php if ($friendshipsEnabled): ?>
                            <a href="<?= Url::to(['/friendship/list/popup', 'userId' => $user->id]); ?>" data-target="#globalModal">
                                <div class="pull-left entry">
                                    <span class="count"><?= $countFriends; ?></span>
                                    <br>
                                    <span class="title"><?= Yii::t('UserModule.widgets_views_profileHeader', 'Friends'); ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                        <?php if ($followingEnabled): ?>
                            <a href="<?= $user->createUrl('/user/profile/follower-list'); ?>" data-target="#globalModal">
                                <div class="pull-left entry">
                                    <span class="count"><?= $countFollowers; ?></span>
                                    <br>
                                    <span class="title"><?= Yii::t('UserModule.widgets_views_profileHeader', 'Followers'); ?></span>
                                </div>
                            </a>
                            <a href="<?= $user->createUrl('/user/profile/followed-users-list'); ?>" data-target="#globalModal">
                                <div class="pull-left entry">
                                    <span class="count"><?= $countFollowing; ?></span>
                                    <br>
                                    <span class="title"><?= Yii::t('UserModule.widgets_views_profileHeader', 'Following'); ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                        <a href="<?= $user->createUrl('/user/profile/space-membership-list'); ?>" data-target="#globalModal">
                            <div class="pull-left entry">
                                <span class="count"><?= $countSpaces; ?></span><br>
                                <span class="title"><?= Yii::t('UserModule.widgets_views_profileHeader', 'Spaces'); ?></span>
                            </div>
                        </a>
                    </div>
                    <!-- end: User statistics -->

                    <div class="controls controls-header pull-right">
                        <?=
                        humhub\modules\user\widgets\ProfileHeaderControls::widget([
                            'user' => $user,
                            'widgets' => [
                                [\humhub\modules\user\widgets\ProfileEditButton::className(), ['user' => $user], []],
                                [\humhub\modules\user\widgets\UserFollowButton::className(), ['user' => $user], []],
                                [\humhub\modules\friendship\widgets\FriendshipButton::className(), ['user' => $user], []],
                            ]
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- start: Error modal -->
<div class="modal" id="uploadErrorModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-extra-small animated pulse">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"
                    id="myModalLabel"><?php echo Yii::t('UserModule.widgets_views_profileHeader', '<strong>Something</strong> went wrong'); ?></h4>
            </div>
            <div class="modal-body text-center">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                        data-dismiss="modal"><?php echo Yii::t('UserModule.widgets_views_profileHeader', 'Ok'); ?></button>
            </div>
        </div>
    </div>
</div>
