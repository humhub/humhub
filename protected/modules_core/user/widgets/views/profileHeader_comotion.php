<div class="comotion-profile comotion-profile-header">

    <!-- TODO: port inline style to css -->
    <div class="image-upload-container profile-user-photo-container" style="width: 320px; height: 320px">

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
                     data-src="holder.js/320x320" alt="320x320" style="width: 320px; height: 320px;"/>
            </a>

        <?php } else { ?>

            <img class="profile-user-photo" id="user-profile-image"
                 src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                 data-src="holder.js/320x320" alt="320x320" style="width: 320px; height: 320px;"/>

        <?php } ?>

        <!-- check if the current user is the profile owner and can change the images -->
        <?php if ($isProfileOwner) { ?>
            <form class="fileupload" id="profilefileupload" action="" method="POST" enctype="multipart/form-data"
                  style="position: absolute; top: 0; left: 0; opacity: 0; height: 300px; width: 300px;">
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
        <?php } else { ?>
            <div>XX%</div>
        <?php } ?>
        </div>
        <div class="comotion-profile-data">
            <h1><?php echo CHtml::encode($user->displayName); ?></h1>

            <h2><?php echo CHtml::encode($user->profile->title); ?></h2>
            Headline: <?php echo CHtml::encode($user->profile->headline); ?><br/>
            Role: <?php echo CHtml::encode($user->profile->role); ?><br/>
        </div>

    </div>
</div>
