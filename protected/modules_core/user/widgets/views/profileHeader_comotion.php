<div class="comotion-profile comotion-profile-header">

    <!-- TODO: port inline style to css -->
    <div class="image-upload-container profile-user-photo-container">

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
                <img class="profile-user-photo" id="user-profile-image"
                     src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                     data-src="holder.js/320x320" alt="320x320"/>
            </a>

        <?php } else { ?>

            <img class="profile-user-photo" id="user-profile-image"
                 src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                 data-src="holder.js/320x320" alt="320x320"/>

        <?php } ?>

        <!-- check if the current user is the profile owner and can change the images -->
        <?php if ($isProfileOwner) { ?>
            <form class="fileupload" id="profilefileupload" action="" method="POST" enctype="multipart/form-data"
                  style="position: absolute; top: 0; left: 0; opacity: 0; height: 350px; width: 350px;">
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
                <a href="#" onclick="javascript:$('#profilefileupload input').click();" class="btn btn-info btn-sm">
                    <i class="fa fa-cloud-upload"></i>
                </a>
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
        <?php if (!$isProfileOwner) { ?>
          <div id="compat_box" class="compatibility-container"
            data-base-url="<?php echo Yii::app()->baseUrl; ?>"
            data-in-userid="<?php echo Yii::app()->user->guid ?>"
            data-out-userid="<?php echo $user->guid ?>">
          </div>
          <script type="text/jsx" >
            var container = $('#compat_box');
            $(document).ready(function() {
              React.render(
                <UserCompatibility base_url={container.attr('data-base-url')}
                  in_userid={container.attr('data-in-userid')}
                  out_userid={container.attr('data-out-userid')} />,
                container[0]
              );
            });
          </script>
        <?php } ?>
        </div>

        <!-- TODO: Create a widget for profile data (this is not a header) -->
        <div class="comotion-content comotion-profile-data">
            <h1><?php echo CHtml::encode($user->displayName); ?></h1>

            <h2><?php echo CHtml::encode($user->profile->title); ?></h2>

            <h3><?php echo CHtml::encode($user->profile->headline); ?></h3>
            
            <h2>I am a <em><?php echo CHtml::encode($user->profile->role); ?></em>
            seeking a <em><span class="role"><?php echo CHtml::encode($user->profile->seeking); ?></em></h2>

            <!-- FOLLOW BUTTON -->
            <?php $this->widget('application.modules_core.user.widgets.UserFollowButtonWidget', array('user' => $user)) ?>

            <!-- TODO: CONNECT and MESSAGE buttons -->

            <hr/>
            <?php if ($user->profile->about != "") { ?>
                <div class="comotion-profile-about">
                    <h3>About</h3>
                    <p><?php echo CHtml::encode($user->profile->about); ?></p>
                </div>
            <?php } ?>

            <?php if ($user->profile->url_twitter != "") { ?>
                <p><?php echo CHtml::encode($user->profile->url_twitter); ?></p>
            <?php } ?>
        </div>

</div>
