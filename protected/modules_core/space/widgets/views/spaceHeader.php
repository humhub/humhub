<?php
/** @var $space Space */
?>
<div class="panel panel-default panel-profile">

    <div class="panel-profile-header">

        <div class="image-upload-container" style="width: 100%; height: 100%; overflow:hidden;">
            <!-- profile image output-->
            <img class="img-profile-header-background" id="space-banner-image"
                 src="<?php echo $space->getProfileBannerImage()->getUrl(); ?>"
                 width="100%" style="width: 100%;">

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($space->isAdmin()) { ?>
                <form class="fileupload" id="bannerfileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; width: 100%; height: 100%;">
                    <input type="file" name="bannerfiles[]">
                </form>

                <?php
                // set standard padding for banner progressbar
                $padding = '90px 350px';

                // if the default banner image is displaying
                if (!$space->getProfileBannerImage()->hasImage()) {
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
                <h1 class="space"><?php echo CHtml::encode($space->name); ?></h1>

                <h2 class="space"><?php echo CHtml::encode($space->description); ?></h2>
            </div>

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($space->isAdmin()) { ?>
                <div class="image-upload-buttons" id="banner-image-upload-buttons">
                    <a href="#" onclick="javascript:$('#bannerfileupload input').click();"
                       class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>
                    <a id="banner-image-upload-edit-button"
                       style="<?php
                       if (!$space->getProfileBannerImage()->hasImage()) {
                           echo 'display: none;';
                       }
                       ?>"
                       href="<?php echo Yii::app()->createUrl('//space/admin/cropBannerImage', array('guid' => $space->guid)); ?>"
                       class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                            class="fa fa-edit"></i></a>
                        <?php
                        $this->widget('application.widgets.ModalConfirmWidget', array(
                            'uniqueID' => 'modal_bannerimagedelete',
                            'linkOutput' => 'a',
                            'title' => Yii::t('SpaceModule.widgets_views_deleteBanner', '<strong>Confirm</strong> image deleting'),
                            'message' => Yii::t('SpaceModule.widgets_views_deleteBanner', 'Do you really want to delete your title image?'),
                            'buttonTrue' => Yii::t('SpaceModule.widgets_views_deleteBanner', 'Delete'),
                            'buttonFalse' => Yii::t('SpaceModule.widgets_views_deleteBanner', 'Cancel'),
                            'linkContent' => '<i class="fa fa-times"></i>',
                            'class' => 'btn btn-danger btn-sm',
                            'style' => $space->getProfileBannerImage()->hasImage() ? '' : 'display: none;',
                            'linkHref' => $this->createUrl("//space/admin/deleteProfileImage", array('guid' => $space->guid, 'type' => 'banner')),
                            'confirmJS' => 'function(jsonResp) { resetProfileImage(jsonResp); }'
                        ));
                        ?>
                </div>

            <?php } ?>


        </div>

        <div class="image-upload-container profile-user-photo-container" style="width: 140px; height: 140px;">

            <?php
            /* Get original profile image URL */

            $profileImageExt = pathinfo($space->getProfileImage()->getUrl(), PATHINFO_EXTENSION);

            $profileImageOrig = preg_replace('/.[^.]*$/', '', $space->getProfileImage()->getUrl());
            $defaultImage = (basename($space->getProfileImage()->getUrl()) == 'default_user.jpg' || basename($space->getProfileImage()->getUrl()) == 'default_user.jpg?cacheId=0') ? true : false;
            $profileImageOrig = $profileImageOrig . '_org.' . $profileImageExt;

            if (!$defaultImage) {
                ?>

                <!-- profile image output-->
                <a data-toggle="lightbox" data-gallery="" href="<?php echo $profileImageOrig; ?>#.jpeg"
                   data-footer='<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo Yii::t('FileModule.widgets_views_showFiles', 'Close'); ?></button>'>
                    <img class="img-rounded profile-user-photo" id="space-profile-image"
                         src="<?php echo $space->getProfileImage()->getUrl(); ?>"
                         data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>
                </a>

            <?php } else { ?>

                <img class="img-rounded profile-user-photo" id="space-profile-image"
                     src="<?php echo $space->getProfileImage()->getUrl(); ?>"
                     data-src="holder.js/140x140" alt="140x140" style="width: 140px; height: 140px;"/>

            <?php } ?>

            <!-- check if the current user is the profile owner and can change the images -->
            <?php if ($space->isAdmin()) { ?>
                <form class="fileupload" id="profilefileupload" action="" method="POST" enctype="multipart/form-data"
                      style="position: absolute; top: 0; left: 0; opacity: 0; height: 140px; width: 140px;">
                    <input type="file" name="spacefiles[]">
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
                       if (!$space->getProfileImage()->hasImage()) {
                           echo 'display: none;';
                       }
                       ?>"
                       href="<?php echo Yii::app()->createUrl('//space/admin/cropImage', array('guid' => $space->guid)); ?>"
                       class="btn btn-info btn-sm" data-toggle="modal" data-target="#globalModal"><i
                            class="fa fa-edit"></i></a>
                        <?php
                        $this->widget('application.widgets.ModalConfirmWidget', array(
                            'uniqueID' => 'modal_profileimagedelete',
                            'linkOutput' => 'a',
                            'title' => Yii::t('SpaceModule.widgets_views_deleteImage', '<strong>Confirm</strong> image deleting'),
                            'message' => Yii::t('SpaceModule.widgets_views_deleteImage', 'Do you really want to delete your profile image?'),
                            'buttonTrue' => Yii::t('SpaceModule.widgets_views_deleteImage', 'Delete'),
                            'buttonFalse' => Yii::t('SpaceModule.widgets_views_deleteImage', 'Cancel'),
                            'linkContent' => '<i class="fa fa-times"></i>',
                            'class' => 'btn btn-danger btn-sm',
                            'style' => $space->getProfileImage()->hasImage() ? '' : 'display: none;',
                            'linkHref' => $this->createUrl("//space/admin/deleteProfileImage", array('guid' => $space->guid, 'type' => 'profile')),
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
                        <span class="count"><?php echo $space->countPosts(); ?></span></a>
                        <br>
                        <span class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Posts'); ?></span>
                    </div>

                    <div class="pull-left entry">
                        <span class="count"><?php echo count($space->memberships); ?></span>
                        <br>
                        <span class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Members'); ?></span>
                    </div>

                    <div class="pull-left entry">
                        <span class="count"><?php echo $space->getFollowerCount(); ?></span><br>
                        <span class="title"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Followers'); ?></span>
                    </div>

                </div>
                <!-- end: User statistics -->

                <div class="controls controls-header text-right col-sm-12 col-md-6">
                    <?php
                    $this->widget('application.modules_core.space.widgets.SpaceHeaderControlsWidget', array(
                        'space' => $space,
                        'widgets' => array(
                            array('application.modules_core.space.widgets.SpaceInviteButtonWidget', array('space' => $space), array()),
                            array('application.modules_core.space.widgets.SpaceMembershipButtonWidget', array('space' => $space), array()),
                            array('application.modules_core.space.widgets.SpaceFollowButtonWidget', array('space' => $space), array()),
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
                    id="myModalLabel"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', '<strong>Something</strong> went wrong'); ?></h4>
            </div>
            <div class="modal-body text-center">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                        data-dismiss="modal"><?php echo Yii::t('SpaceModule.widgets_views_profileHeader', 'Ok'); ?></button>
            </div>
        </div>
    </div>
</div>
