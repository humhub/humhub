<div class="panel panel-default panel-profile">

    <div class="panel-profile-header">

        <div class="image-upload-container" style="width: 100%; height: 100%;">
            <!-- profile image output-->
            <img class="img-profile-header-background" id="user-banner-image"
                 src="<?php echo $user->getProfileBannerImage()->getUrl(); ?>"
                 width="100%" style="width: 100%;">

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($isProfileOwner) { ?>
                <form class="fileupload" id="bannerfileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; width: 100%; height: 100%;">
                    <input type="file" name="bannerfiles[]">
                </form>

                <?php
                // set standard padding for banner progressbar
                $padding = '140px 350px';

                // if the default banner image is displaying
                if (!$user->getProfileBannerImage()->hasImage()) {
                    // change padding to the lower image height
                    $padding = '70px 350px';
                }
                ?>

                <div class="image-upload-loader" id="banner-image-upload-loader" style="padding: <?php echo $padding ?>;">
                    <div class="progress image-upload-progess-bar" id="banner-image-upload-bar">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="00"
                             aria-valuemin="0"
                             aria-valuemax="100" style="width: 0%;">
                        </div>
                    </div>
                </div>

                <div class="img-profile-data">
                    <h1><?php echo $user->displayName; ?></h1>

                    <h2><?php echo $user->profile->title; ?></h2>
                </div>

                <div class="image-upload-buttons" id="banner-image-upload-buttons">
                    <a href="javascript:$('#bannerfileupload input').click();" class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>
                    <a id="banner-image-upload-edit-button"
                       style="<?php
                       if (!$user->getProfileBannerImage()->hasImage()) {
                           echo 'display: none;';
                       }
                       ?>"
                       href="<?php echo Yii::app()->createAbsoluteUrl('//user/profile/cropBannerImage'); ?>"
                       class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                            class="fa fa-edit"></i></a>
                </div>
            <?php } ?>

        </div>

        <div class="image-upload-container profile-user-photo-container" style="width: 140px; height: 140px;">

            <!-- profile image output-->
            <img class="img-rounded profile-user-photo" id="user-profile-image"
                 src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                 data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>

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
                    <a href="javascript:$('#profilefileupload input').click();" class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>
                    <a id="profile-image-upload-edit-button"
                       style="<?php
                       if (!$user->getProfileImage()->hasImage()) {
                           echo 'display: none;';
                       }
                       ?>"
                       href="<?php echo Yii::app()->createAbsoluteUrl('//user/profile/cropProfileImage'); ?>"
                       class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                            class="fa fa-edit"></i></a>
                </div>
            <?php } ?>

        </div>


    </div>

    <div class="panel-body">

        <div class="panel-profile-controls">
            <!-- start: User statistics -->
            <div class="statistics pull-left">

                <div class="pull-left entry">
                    <span class="count"><?php echo count($user->followerUser); ?></span></a>
                    <br>
                    <span class="title"><?php echo Yii::t('UserModule.profile', 'Followers'); ?></span>
                </div>

                <div class="pull-left entry">
                    <span class="count"><?php echo count($user->followsUser); ?></span>
                    <br>
                    <span class="title"><?php echo Yii::t('UserModule.profile', 'Following'); ?></span>
                </div>

                <div class="pull-left entry">
                    <span class="count"><?php echo count($user->spaces); ?></span><br>
                    <span class="title"><?php echo Yii::t('base', 'Spaces'); ?></span>
                </div>
            </div>
            <!-- end: User statistics -->


            <div class="controls pull-right">
                <!-- start: User following -->
                <?php
                if (!$isProfileOwner) {
                    if ($user->isFollowedBy(Yii::app()->user->id)) {
                        print CHtml::link("Unfollow", $this->createUrl('profile/unfollow', array('guid' => $user->guid)), array('class' => 'btn btn-primary'));
                    } else {
                        print CHtml::link("Follow", $this->createUrl('profile/follow', array('guid' => $user->guid)), array('class' => 'btn btn-success'));
                    }
                }
                ?>
                <!-- end: User following -->

                <!-- start: Edit profile -->
                <?php if ($isProfileOwner) { ?>
                    <!-- Edit user account (if this is your profile) -->
                    <a href="<?php echo $this->createUrl('//user/account/edit', array('guid' => $user->guid)); ?>"
                       id="edit_profile" class="btn btn-primary">Edit account</a>
                   <?php } ?>
                <!-- end: Edit profile -->
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
                    id="myModalLabel"><?php echo Yii::t('UserModule.account', '<strong>Something</strong> went wrong'); ?></h4>
            </div>
            <div class="modal-body text-center">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                        data-dismiss="modal"><?php echo Yii::t('UserModule.account', 'Ok'); ?></button>
            </div>
        </div>
    </div>
</div>