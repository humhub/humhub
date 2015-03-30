<div class="panel panel-default panel-profile">

    <div class="panel-profile-header">

        <div class="image-upload-container" style="width: 100%; height: 100%; overflow:hidden;">
            <!-- profile image output-->
            <img class="img-profile-header-background" id="user-banner-image"
                 src="<?php echo $user->getProfileBannerImage()->getUrl(); ?>"
                 width="100%" style="width: 100%; max-height: 192px;">

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($isProfileOwner) { ?>
                <form class="fileupload" id="bannerfileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; width: 100%; height: 100%;">
                    <input type="file" name="bannerfiles[]">
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

            <?php } ?>

            <!-- show user name and title -->
            <div class="img-profile-data">
                <h1><?php echo CHtml::encode($user->displayName); ?></h1>
                <h2><?php echo CHtml::encode($user->profile->title); ?></h2>
            </div>

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($isProfileOwner) { ?>
                <div class="image-upload-buttons" id="banner-image-upload-buttons">
                    <a href="#" onclick="javascript:$('#bannerfileupload input').click();" class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>
                    <a id="banner-image-upload-edit-button"
                       style="<?php
                       if (!$user->getProfileBannerImage()->hasImage()) {
                           echo 'display: none;';
                       }
                       ?>"
                       href="<?php echo Yii::app()->createUrl('//user/account/cropBannerImage'); ?>"
                       class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                            class="fa fa-edit"></i></a>
                        <?php
                        $this->widget('application.widgets.ModalConfirmWidget', array(
                            'uniqueID' => 'modal_bannerimagedelete',
                            'linkOutput' => 'a',
                            'title' => Yii::t('UserModule.widgets_views_deleteBanner', '<strong>Confirm</strong> image deleting'),
                            'message' => Yii::t('UserModule.widgets_views_deleteBanner', 'Do you really want to delete your title image?'),
                            'buttonTrue' => Yii::t('UserModule.widgets_views_deleteBanner', 'Delete'),
                            'buttonFalse' => Yii::t('UserModule.widgets_views_deleteBanner', 'Cancel'),
                            'linkContent' => '<i class="fa fa-times"></i>',
                            'class' => 'btn btn-danger btn-sm',
                            'style' => $user->getProfileBannerImage()->hasImage() ? '' : 'display: none;',
                            'linkHref' => $this->createUrl("//user/account/deleteProfileImage", array('type' => 'banner')),
                            'confirmJS' => 'function(jsonResp) { resetProfileImage(jsonResp); }'
                        ));
                        ?>
                </div>

            <?php } ?>


        </div>

        <div class="image-upload-container profile-user-photo-container" style="width: 140px; height: 140px;">

            <?php
            /* Get original profile image URL */

            $profileImageExt = pathinfo($user->getProfileImage()->getUrl(), PATHINFO_EXTENSION);

            $profileImageOrig = preg_replace('/.[^.]*$/', '', $user->getProfileImage()->getUrl());
            $defaultImage = (basename($user->getProfileImage()->getUrl()) == 'default_user.jpg' || basename($user->getProfileImage()->getUrl()) == 'default_user.jpg?cacheId=0') ? true : false;
            $profileImageOrig = $profileImageOrig . '_org.' . $profileImageExt;

            if (!$defaultImage) {
                ?>

                <!-- profile image output-->
                <a data-toggle="lightbox" data-gallery="" href="<?php echo $profileImageOrig; ?>#.jpeg"  data-footer='<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo Yii::t('FileModule.widgets_views_showFiles', 'Close'); ?></button>'>
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
            <?php if ($isProfileOwner) { ?>
                <form class="fileupload" id="profilefileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; height: 140px; width: 140px;">
                    <input type="file" name="profilefiles[]">
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
                    <a href="#" onclick="javascript:$('#profilefileupload input').click();" class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>
                    <a id="profile-image-upload-edit-button"
                       style="<?php
                       if (!$user->getProfileImage()->hasImage()) {
                           echo 'display: none;';
                       }
                       ?>"
                       href="<?php echo Yii::app()->createUrl('//user/account/cropProfileImage'); ?>"
                       class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                            class="fa fa-edit"></i></a>
                        <?php
                        $this->widget('application.widgets.ModalConfirmWidget', array(
                            'uniqueID' => 'modal_profileimagedelete',
                            'linkOutput' => 'a',
                            'title' => Yii::t('UserModule.widgets_views_deleteImage', '<strong>Confirm</strong> image deleting'),
                            'message' => Yii::t('UserModule.widgets_views_deleteImage', 'Do you really want to delete your profile image?'),
                            'buttonTrue' => Yii::t('UserModule.widgets_views_deleteImage', 'Delete'),
                            'buttonFalse' => Yii::t('UserModule.widgets_views_deleteImage', 'Cancel'),
                            'linkContent' => '<i class="fa fa-times"></i>',
                            'class' => 'btn btn-danger btn-sm',
                            'style' => $user->getProfileImage()->hasImage() ? '' : 'display: none;',
                            'linkHref' => $this->createUrl("//user/account/deleteProfileImage", array('type' => 'profile')),
                            'confirmJS' => 'function(jsonResp) { resetProfileImage(jsonResp); }'
                        ));
                        ?>
                </div>
            <?php } ?>

        </div>


    </div>

    <div class="panel-body">

        <div class="panel-profile-controls">
            <!-- start: User statistics -->
            <div class="row">
                <div class="statistics col-sm-12 col-md-6">

                    <div class="pull-left entry">
                        <span class="count"><?php echo $user->getFollowerCount(); ?></span></a>
                        <br>
                        <span class="title"><?php echo Yii::t('UserModule.widgets_views_profileHeader', 'Followers'); ?></span>
                    </div>

                    <div class="pull-left entry">
                        <span class="count"><?php echo $user->getFollowingCount('User') + $user->getFollowingCount('Space'); ?></span>
                        <br>
                        <span class="title"><?php echo Yii::t('UserModule.widgets_views_profileHeader', 'Following'); ?></span>
                    </div>

                    <div class="pull-left entry">
                        <span class="count"><?php echo count($user->spaces); ?></span><br>
                        <span class="title"><?php echo Yii::t('UserModule.widgets_views_profileHeader', 'Spaces'); ?></span>
                    </div>

                </div>
                <!-- end: User statistics -->

                <div class="controls controls-header text-right col-sm-12 col-md-6">
                    <?php
                    $this->widget('application.modules_core.user.widgets.ProfileHeaderControlsWidget', array(
                        'user' => $user,
                        'widgets' => array(
                            array('application.modules_core.user.widgets.ProfileEditButtonWidget', array('user' => $user), array()),
                            array('application.modules_core.user.widgets.UserFollowButtonWidget', array('user' => $user), array()),
                        )
                    ));
                    ?>
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
